<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Quotation;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class QuotationController extends Controller
{
    public function index()
    {
        return view('admin.quotations.index');
    }

    public function create()
    {
        $clients = Client::query()
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $selectedClientId = request('client_id');

        return view('admin.quotations.create', compact(
            'clients',
            'services',
            'selectedClientId'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateQuotation($request);

        DB::transaction(function () use ($data) {
            $totals = $this->calculateTotals($data);

            $quotation = Quotation::query()->create([
                'client_id' => $data['client_id'],
                'user_id' => auth()->id(),
                'quotation_number' => $this->generateQuotationNumber(),
                'subtotal' => $totals['subtotal'],
                'vat' => $totals['vat'],
                'total' => $totals['total'],
                'status' => 'pending',
                'quotation_date' => $data['quotation_date'] ?? now()->toDateString(),
                'valid_until' => $data['valid_until'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($quotation, $data);

            $quotation->logActivity(
                event: 'created',
                title: 'تم إنشاء عرض سعر',
                description: 'تم إنشاء عرض السعر رقم ' . $quotation->quotation_number,
                newValues: $quotation->only([
                    'client_id',
                    'quotation_number',
                    'subtotal',
                    'vat',
                    'total',
                    'status',
                ])
            );
        });

        return redirect()
            ->route('admin.quotations.index')
            ->with('success', 'تم إنشاء عرض السعر بنجاح');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load([
            'client',
            'user',
            'items.service',
            'sale.payments',
        ]);

        return view('admin.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        abort_if(in_array($quotation->status, ['closed', 'cancelled']), 403);

        $quotation->load('items');

        $clients = Client::query()
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('admin.quotations.edit', compact(
            'quotation',
            'clients',
            'services'
        ));
    }

    public function update(Request $request, Quotation $quotation)
    {
        abort_if(in_array($quotation->status, ['closed', 'cancelled']), 403);

        $data = $this->validateQuotation($request);

        DB::transaction(function () use ($quotation, $data) {
            $oldValues = $quotation->only([
                'client_id',
                'subtotal',
                'vat',
                'total',
                'status',
                'quotation_date',
                'valid_until',
                'notes',
            ]);

            $totals = $this->calculateTotals($data);

            $quotation->update([
                'client_id' => $data['client_id'],
                'subtotal' => $totals['subtotal'],
                'vat' => $totals['vat'],
                'total' => $totals['total'],
                'quotation_date' => $data['quotation_date'] ?? now()->toDateString(),
                'valid_until' => $data['valid_until'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $quotation->items()->delete();

            $this->syncItems($quotation, $data);

            $quotation->logActivity(
                event: 'updated',
                title: 'تم تعديل عرض السعر',
                description: 'تم تعديل بيانات عرض السعر رقم ' . $quotation->quotation_number,
                oldValues: $oldValues,
                newValues: $quotation->only([
                    'client_id',
                    'subtotal',
                    'vat',
                    'total',
                    'status',
                    'quotation_date',
                    'valid_until',
                    'notes',
                ])
            );
        });

        return redirect()
            ->route('admin.quotations.show', $quotation)
            ->with('success', 'تم تحديث عرض السعر بنجاح');
    }

    public function changeStatus(Request $request, Quotation $quotation)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,open,closed,cancelled'],
        ]);

        if ($quotation->sale && $data['status'] === 'cancelled') {
            return back()->with('error', 'لا يمكن إلغاء عرض سعر مرتبط بعملية بيع');
        }

        $oldStatus = $quotation->status;

        $updateData = [
            'status' => $data['status'],
        ];

        if ($data['status'] === 'open' && ! $quotation->opened_at) {
            $updateData['opened_at'] = now();
        }

        if ($data['status'] === 'closed') {
            $updateData['closed_at'] = now();
        }

        if ($data['status'] !== 'closed') {
            $updateData['closed_at'] = null;
        }

        $quotation->update($updateData);

        $quotation->logActivity(
            event: 'status_changed',
            title: 'تم تغيير حالة عرض السعر',
            description: 'تم تغيير الحالة من ' . $oldStatus . ' إلى ' . $quotation->status,
            oldValues: [
                'status' => $oldStatus,
            ],
            newValues: [
                'status' => $quotation->status,
            ]
        );

        return back()->with('success', 'تم تحديث حالة عرض السعر بنجاح');
    }

    public function destroy(Quotation $quotation)
    {
        if ($quotation->sale) {
            return back()->with('error', 'لا يمكن حذف عرض سعر مرتبط بعملية بيع');
        }

        $quotation->delete();

        return redirect()
            ->route('admin.quotations.index')
            ->with('success', 'تم حذف عرض السعر بنجاح');
    }

    private function validateQuotation(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'quotation_date' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quotation_date'],
            'vat' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],

            'service_id' => ['required', 'array', 'min:1'],
            'service_id.*' => ['required', 'exists:services,id'],

            'quantity' => ['required', 'array'],
            'quantity.*' => ['required', 'integer', 'min:1'],

            'unit_price' => ['required', 'array'],
            'unit_price.*' => ['required', 'numeric', 'min:0'],

            'item_notes' => ['nullable', 'array'],
            'item_notes.*' => ['nullable', 'string'],
        ], [
            'client_id.required' => 'يجب اختيار العميل',
            'service_id.required' => 'يجب إضافة خدمة واحدة على الأقل',
            'service_id.*.required' => 'يجب اختيار الخدمة',
            'quantity.*.required' => 'الكمية مطلوبة',
            'unit_price.*.required' => 'سعر الخدمة مطلوب',
            'valid_until.after_or_equal' => 'تاريخ انتهاء العرض يجب أن يكون بعد أو يساوي تاريخ العرض',
        ]);
    }
    public function pdf(Quotation $quotation)
    {
        $quotation->load([
            'client',
            'user',
            'items.service',
            'sale.payments',
        ]);

        $html = view('admin.quotations.pdf', compact('quotation'))->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusans',
            'directionality' => 'rtl',
            'margin_top' => 12,
            'margin_right' => 10,
            'margin_bottom' => 12,
            'margin_left' => 10,
        ]);

        $mpdf->SetTitle('عرض سعر ' . $quotation->quotation_number);
        $mpdf->WriteHTML($html);

        $filename = $quotation->quotation_number . '.pdf';

        return response($mpdf->Output($filename, Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
    private function calculateTotals(array $data): array
    {
        $subtotal = 0;

        foreach ($data['service_id'] as $index => $serviceId) {
            $quantity = (int) ($data['quantity'][$index] ?? 1);
            $unitPrice = (float) ($data['unit_price'][$index] ?? 0);

            $subtotal += $quantity * $unitPrice;
        }

        $vat = (float) ($data['vat'] ?? 0);

        return [
            'subtotal' => $subtotal,
            'vat' => $vat,
            'total' => $subtotal + $vat,
        ];
    }

    private function syncItems(Quotation $quotation, array $data): void
    {
        foreach ($data['service_id'] as $index => $serviceId) {
            $quantity = (int) ($data['quantity'][$index] ?? 1);
            $unitPrice = (float) ($data['unit_price'][$index] ?? 0);

            $quotation->items()->create([
                'service_id' => $serviceId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $quantity * $unitPrice,
                'notes' => $data['item_notes'][$index] ?? null,
            ]);
        }
    }

    private function generateQuotationNumber(): string
    {
        $prefix = 'QTN-' . now()->format('Ymd') . '-';

        $lastNumber = Quotation::query()
            ->where('quotation_number', 'like', $prefix . '%')
            ->count() + 1;

        return $prefix . str_pad((string) $lastNumber, 4, '0', STR_PAD_LEFT);
    }
}
