<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Sale;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        return view('admin.sales.index');
    }
    public function show(Sale $sale)
    {
        $sale->load([
            'client',
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

        return view('admin.sales.create', compact('clients', 'services'));
    }

    public function store(Request $request)
    {
        $data = $this->validateSale($request);

        DB::transaction(function () use ($data) {
            $totals = $this->calculateTotals($data);

            $sale = Sale::query()->create([
                'client_id' => $data['client_id'],
                'user_id' => auth()->id(),
                'subtotal' => $totals['subtotal'],
                'vat' => $data['vat'] ?? 0,
                'total' => $totals['total'],
                'payment_method' => $data['payment_method'],
                'status' => $data['status'],
                'sold_at' => $data['sold_at'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncItems($sale, $data);
        });

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'تم تسجيل عملية البيع بنجاح');
    }

    public function edit(Sale $sale)
    {
        $sale->load('items');

        $clients = Client::query()
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('admin.sales.edit', compact('sale', 'clients', 'services'));
    }

    public function update(Request $request, Sale $sale)
    {
        $data = $this->validateSale($request);

        DB::transaction(function () use ($sale, $data) {
            $totals = $this->calculateTotals($data);

            $sale->update([
                'client_id' => $data['client_id'],
                'subtotal' => $totals['subtotal'],
                'vat' => $data['vat'] ?? 0,
                'total' => $totals['total'],
                'payment_method' => $data['payment_method'],
                'status' => $data['status'],
                'sold_at' => $data['sold_at'] ?? now()->toDateString(),
                'notes' => $data['notes'] ?? null,
            ]);

            $sale->items()->delete();

            $this->syncItems($sale, $data);
        });

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'تم تحديث عملية البيع بنجاح');
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();

        return redirect()
            ->route('admin.sales.index')
            ->with('success', 'تم حذف عملية البيع بنجاح');
    }

    private function validateSale(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'payment_method' => ['required', 'in:cash,bank_transfer,instapay,vodafone_cash,other'],
            'status' => ['required', 'in:pending,partial,paid,cancelled'],
            'sold_at' => ['nullable', 'date'],
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
            'total' => $subtotal + $vat,
        ];
    }

    private function syncItems(Sale $sale, array $data): void
    {
        foreach ($data['service_id'] as $index => $serviceId) {
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
}
