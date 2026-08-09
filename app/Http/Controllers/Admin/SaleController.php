<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\Service;
use App\Traits\AuthorizesOwnedRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    use AuthorizesOwnedRecords;

    public function index()
    {
        return view('admin.sales.index');
    }

    public function show(Sale $sale)
    {
        $this->authorizeOwnedRecordAccess('sales.view_all', $sale->user_id);

        $sale->load([
            'client',
            'quotation',
            'user',
            'items.service',
            'payments.user',
        ]);

        return view('admin.sales.show', compact('sale'));
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

        $quotations = Quotation::query()
            ->with(['client', 'items.service'])
            ->where('status', 'open')
            ->whereDoesntHave('sale')
            ->latest()
            ->get();

        $selectedQuotationId = request('quotation_id');

        return view('admin.sales.create', compact(
            'clients',
            'services',
            'quotations',
            'selectedQuotationId'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateSale($request);

        DB::transaction(function () use ($data) {
            $quotation = $this->resolveQuotation($data);

            if ($quotation) {
                $data['client_id'] = $quotation->client_id;

                $totals = [
                    'subtotal' => (float) $quotation->subtotal,
                    'vat' => (float) $quotation->vat,
                    'total' => (float) $quotation->total,
                ];
            } else {
                $totals = $this->calculateTotals($data);
            }

            $sale = Sale::query()->create([
                'client_id' => $data['client_id'],
                'quotation_id' => $quotation?->id,
                'user_id' => auth()->id(),
                'subtotal' => $totals['subtotal'],
                'vat' => $totals['vat'],
                'total' => $totals['total'],
                'payment_method' => $data['payment_method'],
                'status' => 'pending',
                'sold_at' => $data['sold_at'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);

            if ($quotation) {
                $this->copyQuotationItemsToSale($sale, $quotation);
            } else {
                $this->syncItems($sale, $data);
            }

            $paidAmount = (float) ($data['paid_amount'] ?? 0);

            if ($paidAmount > 0) {
                $sale->payments()->create([
                    'user_id' => auth()->id(),
                    'amount' => min($paidAmount, (float) $sale->total),
                    'payment_method' => $data['payment_method'],
                    'paid_at' => $data['sold_at'] ?? now()->toDateString(),
                    'notes' => 'دفعة أولى مسجلة مع عملية البيع',
                ]);
            }

            if (($data['status'] ?? null) === 'cancelled') {
                $sale->update(['status' => 'cancelled']);
                return;
            }

            $sale->refreshPaymentStatus();
        });

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'تم تسجيل عملية البيع بنجاح');
    }

    public function edit(Sale $sale)
    {
        $this->authorizeOwnedRecordAccess('sales.view_all', $sale->user_id);

        $sale->load([
            'items.service',
            'quotation.items.service',
            'payments',
        ]);

        $clients = Client::query()
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $quotations = Quotation::query()
            ->with(['client', 'items.service'])
            ->where(function ($query) use ($sale) {
                $query->where(function ($query) {
                    $query->where('status', 'open')
                        ->whereDoesntHave('sale');
                });

                if ($sale->quotation_id) {
                    $query->orWhere('id', $sale->quotation_id);
                }
            })
            ->latest()
            ->get();

        return view('admin.sales.edit', compact(
            'sale',
            'clients',
            'services',
            'quotations'
        ));
    }

    public function update(Request $request, Sale $sale)
    {
        $this->authorizeOwnedRecordAccess('sales.view_all', $sale->user_id);

        $data = $this->validateSale($request, $sale);

        DB::transaction(function () use ($sale, $data) {
            $quotation = $this->resolveQuotation($data, $sale);

            if ($quotation) {
                $data['client_id'] = $quotation->client_id;

                $totals = [
                    'subtotal' => (float) $quotation->subtotal,
                    'vat' => (float) $quotation->vat,
                    'total' => (float) $quotation->total,
                ];
            } else {
                $totals = $this->calculateTotals($data);
            }

            $sale->update([
                'client_id' => $data['client_id'],
                'quotation_id' => $quotation?->id,
                'subtotal' => $totals['subtotal'],
                'vat' => $totals['vat'],
                'total' => $totals['total'],
                'payment_method' => $data['payment_method'],
                'status' => $data['status'] ?? $sale->status,
                'sold_at' => $data['sold_at'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);

            $sale->items()->delete();

            if ($quotation) {
                $this->copyQuotationItemsToSale($sale, $quotation);
            } else {
                $this->syncItems($sale, $data);
            }

            if (($data['status'] ?? null) !== 'cancelled') {
                $sale->refreshPaymentStatus();
            }
        });

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'تم تحديث عملية البيع بنجاح');
    }

    public function destroy(Sale $sale)
    {
        $this->authorizeOwnedRecordAccess('sales.view_all', $sale->user_id);

        $sale->delete();

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'تم حذف عملية البيع بنجاح');
    }

    private function validateSale(Request $request, ?Sale $sale = null): array
    {
        $hasQuotation = $request->filled('quotation_id');

        return $request->validate([
            'quotation_id' => ['nullable', 'exists:quotations,id'],

            'client_id' => [$hasQuotation ? 'nullable' : 'required', 'exists:clients,id'],

            'payment_method' => ['required', 'in:cash,bank_transfer,instapay,vodafone_cash,other'],

            'status' => [$sale ? 'required' : 'nullable', 'in:pending,partial,paid,cancelled'],

            'sold_at' => ['nullable', 'date'],
            'vat' => [$hasQuotation ? 'nullable' : 'nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],

            'service_id' => [$hasQuotation ? 'nullable' : 'required', 'array', 'min:1'],
            'service_id.*' => [$hasQuotation ? 'nullable' : 'required', 'exists:services,id'],

            'quantity' => [$hasQuotation ? 'nullable' : 'required', 'array'],
            'quantity.*' => [$hasQuotation ? 'nullable' : 'required', 'integer', 'min:1'],

            'unit_price' => [$hasQuotation ? 'nullable' : 'required', 'array'],
            'unit_price.*' => [$hasQuotation ? 'nullable' : 'required', 'numeric', 'min:0'],

            'item_notes' => ['nullable', 'array'],
            'item_notes.*' => ['nullable', 'string'],
        ], [
            'client_id.required' => 'يجب اختيار العميل',
            'service_id.required' => 'يجب إضافة خدمة واحدة على الأقل',
            'service_id.*.required' => 'يجب اختيار الخدمة',
            'quantity.*.required' => 'الكمية مطلوبة',
            'unit_price.*.required' => 'سعر الخدمة مطلوب',
        ]);
    }

    private function resolveQuotation(array $data, ?Sale $sale = null): ?Quotation
    {
        if (empty($data['quotation_id'])) {
            return null;
        }

        $quotation = Quotation::query()
            ->with(['items.service', 'sale'])
            ->findOrFail($data['quotation_id']);

        if ($quotation->status !== 'open' && (int) $quotation->id !== (int) $sale?->quotation_id) {
            throw ValidationException::withMessages([
                'quotation_id' => 'عرض السعر المختار ليس مفتوحًا ولا يمكن استخدامه في البيع',
            ]);
        }

        if ($quotation->sale && (int) $quotation->sale->id !== (int) $sale?->id) {
            throw ValidationException::withMessages([
                'quotation_id' => 'عرض السعر المختار مرتبط بعملية بيع أخرى بالفعل',
            ]);
        }

        if ($quotation->items->isEmpty()) {
            throw ValidationException::withMessages([
                'quotation_id' => 'عرض السعر المختار لا يحتوي على خدمات',
            ]);
        }

        return $quotation;
    }

    private function calculateTotals(array $data): array
    {
        $subtotal = 0;

        foreach (($data['service_id'] ?? []) as $index => $serviceId) {
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

    private function syncItems(Sale $sale, array $data): void
    {
        foreach (($data['service_id'] ?? []) as $index => $serviceId) {
            $quantity = (int) ($data['quantity'][$index] ?? 1);
            $unitPrice = (float) ($data['unit_price'][$index] ?? 0);

            $sale->items()->create([
                'service_id' => $serviceId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $quantity * $unitPrice,
                'notes' => $data['item_notes'][$index] ?? null,
            ]);
        }
    }

    private function copyQuotationItemsToSale(Sale $sale, Quotation $quotation): void
    {
        foreach ($quotation->items as $item) {
            $sale->items()->create([
                'service_id' => $item->service_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
                'notes' => $item->notes,
            ]);
        }
    }
}
