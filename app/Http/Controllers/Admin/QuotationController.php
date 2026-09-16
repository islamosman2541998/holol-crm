<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\Service;
use App\Traits\AuthorizesOwnedRecords;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class QuotationController extends Controller
{
    use AuthorizesOwnedRecords;

    public function index()
    {
        return view('admin.quotations.index');
    }

    public function create()
    {
        $clientQuery = Client::query();
        $this->applyOwnedRecordScope($clientQuery, 'clients.view_all', 'assigned_to');
        $clients = $clientQuery->orderBy('name')->get();

        $leadQuery = Lead::query();
        $this->applyOwnedRecordScope($leadQuery, 'leads.view_all', 'assigned_to');
        $leads = $leadQuery
            ->where('status', '!=', 'converted')
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $selectedClientId = request('client_id');
        $selectedLeadId = request('lead_id');

        return view('admin.quotations.create', compact(
            'clients',
            'leads',
            'services',
            'selectedClientId',
            'selectedLeadId'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateQuotation($request);

        $maxAttempts = 5;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                DB::transaction(function () use ($data) {
                    $totals = $this->calculateTotals($data);

                    $quotation = Quotation::query()->create([
                        'client_id' => $data['client_id'] ?? null,
                        'lead_id' => $data['lead_id'] ?? null,
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
                        description: 'تم إنشاء عرض السعر رقم '.$quotation->quotation_number,
                        newValues: $quotation->only([
                            'client_id',
                            'lead_id',
                            'quotation_number',
                            'subtotal',
                            'vat',
                            'total',
                            'status',
                        ])
                    );
                });

                break;
            } catch (QueryException $e) {
                $isDuplicateNumber = (int) $e->getCode() === 23000
                    && str_contains($e->getMessage(), 'quotation_number');

                if (! $isDuplicateNumber || $attempt === $maxAttempts) {
                    throw $e;
                }
            }
        }

        return redirect()
            ->route('admin.quotations.index')
            ->with('success', 'تم إنشاء عرض السعر بنجاح');
    }

    public function show(Quotation $quotation)
    {
        $this->authorizeOwnedRecordAccess('quotations.view_all', $quotation->user_id);

        $quotation->load([
            'client',
            'lead',
            'user',
            'items.service',
            'sale.payments',
        ]);

        return view('admin.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        $this->authorizeOwnedRecordAccess('quotations.view_all', $quotation->user_id);

        abort_if(in_array($quotation->status, ['closed', 'cancelled']), 403);

        $quotation->load('items');

        $clientQuery = Client::query();
        $this->applyOwnedRecordScopeIncluding($clientQuery, 'clients.view_all', 'assigned_to', $quotation->client_id);
        $clients = $clientQuery
            ->orderBy('name')
            ->get();

        $leadQuery = Lead::query();
        $this->applyOwnedRecordScopeIncluding($leadQuery, 'leads.view_all', 'assigned_to', $quotation->lead_id);
        $leads = $leadQuery
            ->where(function ($query) use ($quotation) {
                $query->where('status', '!=', 'converted');

                if ($quotation->lead_id) {
                    $query->orWhere('id', $quotation->lead_id);
                }
            })
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('admin.quotations.edit', compact(
            'quotation',
            'clients',
            'leads',
            'services'
        ));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $this->authorizeOwnedRecordAccess('quotations.view_all', $quotation->user_id);

        abort_if(in_array($quotation->status, ['closed', 'cancelled']), 403);

        $data = $this->validateQuotation($request, $quotation);

        DB::transaction(function () use ($quotation, $data) {
            $oldValues = $quotation->only([
                'client_id',
                'lead_id',
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
                'client_id' => $data['client_id'] ?? null,
                'lead_id' => $data['lead_id'] ?? null,
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
                description: 'تم تعديل بيانات عرض السعر رقم '.$quotation->quotation_number,
                oldValues: $oldValues,
                newValues: $quotation->only([
                    'client_id',
                    'lead_id',
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
        $this->authorizeOwnedRecordAccess('quotations.view_all', $quotation->user_id);

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
            description: 'تم تغيير الحالة من '.$oldStatus.' إلى '.$quotation->status,
            oldValues: [
                'status' => $oldStatus,
            ],
            newValues: [
                'status' => $quotation->status,
            ]
        );

        return back()->with('success', 'تم تحديث حالة عرض السعر بنجاح');
    }

    public function createSale(Quotation $quotation)
    {
        $this->authorizeOwnedRecordAccess('quotations.view_all', $quotation->user_id);

        $quotation->loadMissing(['items', 'saleWithTrashed']);

        if ($quotation->status !== 'open') {
            return back()->with('error', 'يجب أن يكون عرض السعر مفتوحًا قبل تحويله إلى بيع.');
        }

        if (! $quotation->client_id) {
            return back()->with('error', 'يجب تحويل الـ Lead إلى عميل قبل إنشاء عملية البيع.');
        }

        if ($quotation->saleWithTrashed) {
            if ($quotation->saleWithTrashed->trashed()) {
                return back()->with('error', 'عرض السعر مرتبط بعملية بيع مؤرشفة ولا يمكن إنشاء بيع مكرر له.');
            }

            return redirect()
                ->route('admin.sales.show', $quotation->saleWithTrashed)
                ->with('error', 'عرض السعر مرتبط بعملية بيع بالفعل.');
        }

        if ($quotation->items->isEmpty()) {
            return back()->with('error', 'لا يمكن تحويل عرض سعر لا يحتوي على خدمات.');
        }

        return redirect()->route('admin.sales.create', [
            'quotation_id' => $quotation->id,
        ]);
    }

    public function destroy(Quotation $quotation)
    {
        $this->authorizeOwnedRecordAccess('quotations.view_all', $quotation->user_id);

        if ($quotation->saleWithTrashed()->exists()) {
            return back()->with('error', 'لا يمكن حذف عرض سعر مرتبط بعملية بيع');
        }

        $quotation->delete();

        return redirect()
            ->route('admin.quotations.index')
            ->with('success', 'تم حذف عرض السعر بنجاح');
    }

    private function validateQuotation(Request $request, ?Quotation $quotation = null): array
    {
        $data = $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
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
            'service_id.required' => 'يجب إضافة خدمة واحدة على الأقل',
            'service_id.*.required' => 'يجب اختيار الخدمة',
            'quantity.*.required' => 'الكمية مطلوبة',
            'unit_price.*.required' => 'سعر الخدمة مطلوب',
            'valid_until.after_or_equal' => 'تاريخ انتهاء العرض يجب أن يكون بعد أو يساوي تاريخ العرض',
        ]);

        $hasClient = ! empty($data['client_id']);
        $hasLead = ! empty($data['lead_id']);

        if (! $hasClient && ! $hasLead) {
            throw ValidationException::withMessages([
                'client_id' => 'يجب اختيار عميل أو Lead لعرض السعر',
            ]);
        }

        if ($hasClient && $hasLead) {
            throw ValidationException::withMessages([
                'client_id' => 'لا يمكن ربط عرض السعر بعميل و Lead في نفس الوقت',
                'lead_id' => 'لا يمكن ربط عرض السعر بعميل و Lead في نفس الوقت',
            ]);
        }

        if ($hasClient && (! $quotation || (int) $data['client_id'] !== (int) $quotation->client_id)) {
            $client = Client::query()->findOrFail($data['client_id']);
            $this->authorizeOwnedRecordAccess('clients.view_all', $client->assigned_to);
        }

        if ($hasLead && (! $quotation || (int) $data['lead_id'] !== (int) $quotation->lead_id)) {
            $lead = Lead::query()->findOrFail($data['lead_id']);
            $this->authorizeOwnedRecordAccess('leads.view_all', $lead->assigned_to);
        }

        $itemCount = count($data['service_id'] ?? []);

        if (
            count($data['quantity'] ?? []) !== $itemCount
            || count($data['unit_price'] ?? []) !== $itemCount
        ) {
            throw ValidationException::withMessages([
                'service_id' => 'بيانات الخدمات والكميات والأسعار غير متطابقة. أعد تحميل الصفحة وحاول مرة أخرى.',
            ]);
        }

        return $data;
    }

    public function pdf(Quotation $quotation)
    {
        $this->authorizeOwnedRecordAccess('quotations.view_all', $quotation->user_id);

        $quotation->load([
            'client',
            'lead',
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

        $mpdf->SetTitle('عرض سعر '.$quotation->quotation_number);
        $mpdf->WriteHTML($html);

        $filename = $quotation->quotation_number.'.pdf';

        return response($mpdf->Output($filename, Destination::STRING_RETURN), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
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
        $prefix = 'QTN-'.now()->format('Ymd').'-';

        $lastQuotationNumber = Quotation::withTrashed()
            ->where('quotation_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->max('quotation_number');

        $lastNumber = $lastQuotationNumber
            ? ((int) Str::afterLast($lastQuotationNumber, '-')) + 1
            : 1;

        return $prefix.str_pad((string) $lastNumber, 4, '0', STR_PAD_LEFT);
    }
}
