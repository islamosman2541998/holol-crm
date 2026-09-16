<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
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
        $clientQuery = Client::query();
        $this->applyOwnedRecordScope($clientQuery, 'clients.view_all', 'assigned_to');
        $clients = $clientQuery->orderBy('name')->get();

        $services = Service::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $quotationQuery = Quotation::query();
        $this->applyOwnedRecordScope($quotationQuery, 'quotations.view_all', 'user_id');
        $quotations = $quotationQuery
            ->with(['client', 'items.service'])
            ->whereNotNull('client_id')
            ->where('status', 'open')
            ->whereDoesntHave('saleWithTrashed')
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

            if ($paidAmount > (float) $sale->total) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'الدفعة الأولى لا يمكن أن تكون أكبر من إجمالي البيع.',
                ]);
            }

            if ($paidAmount > 0 && ($data['status'] ?? null) === 'cancelled') {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن إلغاء عملية بيع أثناء تسجيل دفعة عليها.',
                ]);
            }

            if ($paidAmount > 0) {
                $sale->payments()->create([
                    'user_id' => auth()->id(),
                    'amount' => $paidAmount,
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

        $clientQuery = Client::query();
        $this->applyOwnedRecordScopeIncluding($clientQuery, 'clients.view_all', 'assigned_to', $sale->client_id);
        $clients = $clientQuery
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $quotationQuery = Quotation::query();
        $this->applyOwnedRecordScopeIncluding($quotationQuery, 'quotations.view_all', 'user_id', $sale->quotation_id);
        $quotations = $quotationQuery
            ->with(['client', 'items.service'])
            ->where(function ($query) use ($sale) {
                $query->where(function ($query) {
                        $query->whereNotNull('client_id')
                            ->where('status', 'open')
                            ->whereDoesntHave('saleWithTrashed');
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
            $lockedSale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($sale->id);

            $quotation = $this->resolveQuotation($data, $lockedSale);

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

            $paidAmount = (float) $lockedSale->payments()->sum('amount');

            if ($paidAmount > 0 && (
                (int) $data['client_id'] !== (int) $lockedSale->client_id
                || (int) ($quotation?->id) !== (int) $lockedSale->quotation_id
            )) {
                throw ValidationException::withMessages([
                    'client_id' => 'لا يمكن تغيير العميل أو عرض السعر بعد تسجيل دفعات.',
                ]);
            }

            if ((float) $totals['total'] < $paidAmount) {
                throw ValidationException::withMessages([
                    'unit_price' => 'إجمالي البيع الجديد أقل من المبلغ المدفوع بالفعل.',
                ]);
            }

            if (($data['status'] ?? null) === 'cancelled' && $paidAmount > 0) {
                throw ValidationException::withMessages([
                    'status' => 'لا يمكن إلغاء عملية بيع لها دفعات. يجب معالجة الاسترداد أولًا.',
                ]);
            }

            $lockedSale->update([
                'client_id' => $data['client_id'],
                'quotation_id' => $quotation?->id,
                'subtotal' => $totals['subtotal'],
                'vat' => $totals['vat'],
                'total' => $totals['total'],
                'payment_method' => $data['payment_method'],
                'status' => $data['status'] ?? $lockedSale->status,
                'sold_at' => $data['sold_at'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);

            $lockedSale->items()->delete();

            if ($quotation) {
                $this->copyQuotationItemsToSale($lockedSale, $quotation);
            } else {
                $this->syncItems($lockedSale, $data);
            }

            if (($data['status'] ?? null) !== 'cancelled') {
                $lockedSale->refreshPaymentStatus();
            }
        });

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'تم تحديث عملية البيع بنجاح');
    }

    public function destroy(Sale $sale)
    {
        $this->authorizeOwnedRecordAccess('sales.view_all', $sale->user_id);

        if ($sale->payments()->exists()) {
            return back()->with('error', 'لا يمكن حذف عملية بيع لها دفعات مسجلة. احتفظ بها للسجل المالي.');
        }

        $sale->delete();

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'تم حذف عملية البيع بنجاح');
    }

    private function validateSale(Request $request, ?Sale $sale = null): array
    {
        $hasQuotation = $request->filled('quotation_id');

        $data = $request->validate([
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

        if (! $hasQuotation) {
            $itemCount = count($data['service_id'] ?? []);

            if (
                count($data['quantity'] ?? []) !== $itemCount
                || count($data['unit_price'] ?? []) !== $itemCount
            ) {
                throw ValidationException::withMessages([
                    'service_id' => 'بيانات الخدمات والكميات والأسعار غير متطابقة. أعد تحميل الصفحة وحاول مرة أخرى.',
                ]);
            }

            if (! $sale || (int) $data['client_id'] !== (int) $sale->client_id) {
                $client = Client::query()->findOrFail($data['client_id']);
                $this->authorizeOwnedRecordAccess('clients.view_all', $client->assigned_to);
            }
        }

        return $data;
    }

    private function resolveQuotation(array $data, ?Sale $sale = null): ?Quotation
    {
        if (empty($data['quotation_id'])) {
            return null;
        }

        $quotation = Quotation::query()
            ->with(['items.service', 'saleWithTrashed'])
            ->lockForUpdate()
            ->findOrFail($data['quotation_id']);

        if (! $sale || (int) $quotation->id !== (int) $sale->quotation_id) {
            $this->authorizeOwnedRecordAccess('quotations.view_all', $quotation->user_id);
        }

        if ($quotation->status !== 'open' && (int) $quotation->id !== (int) $sale?->quotation_id) {
            throw ValidationException::withMessages([
                'quotation_id' => 'عرض السعر المختار ليس مفتوحًا ولا يمكن استخدامه في البيع',
            ]);
        }

        if (! $quotation->client_id) {
            throw ValidationException::withMessages([
                'quotation_id' => 'عرض السعر ده مرتبط بـ Lead مش عميل، لازم تحول الـ Lead لعميل الأول',
            ]);
        }

        if ($quotation->saleWithTrashed && (int) $quotation->saleWithTrashed->id !== (int) $sale?->id) {
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
