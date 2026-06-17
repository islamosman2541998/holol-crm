<?php

namespace App\Livewire\Admin\Sales;

use App\Models\Payment;
use App\Models\Sale;
use Livewire\Component;

class SalePayments extends Component
{
    public Sale $sale;

    public string $amount = '';
    public string $payment_method = 'cash';
    public ?string $paid_at = null;
    public ?string $notes = null;

    public function mount(Sale $sale): void
    {
        $this->sale = $sale;
        $this->paid_at = now()->format('Y-m-d');
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('sales.edit'), 403);

        $data = $this->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:cash,bank_transfer,instapay,vodafone_cash,other'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ], [
            'amount.required' => 'قيمة الدفعة مطلوبة',
            'amount.numeric' => 'قيمة الدفعة يجب أن تكون رقم',
            'amount.min' => 'قيمة الدفعة يجب أن تكون أكبر من صفر',
            'paid_at.required' => 'تاريخ الدفع مطلوب',
        ]);

        Payment::query()->create([
            'sale_id' => $this->sale->id,
            'user_id' => auth()->id(),
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'paid_at' => $data['paid_at'],
            'notes' => $data['notes'] ?? null,
        ]);

        $this->refreshSaleStatus();

        $this->reset([
            'amount',
            'notes',
        ]);

        $this->payment_method = 'cash';
        $this->paid_at = now()->format('Y-m-d');

        $this->dispatch('toast', type: 'success', message: 'تم إضافة الدفعة بنجاح');
    }

    public function delete(int $paymentId): void
    {
        abort_unless(auth()->user()->can('sales.edit'), 403);

        $payment = Payment::query()
            ->where('sale_id', $this->sale->id)
            ->findOrFail($paymentId);

        $payment->delete();

        $this->refreshSaleStatus();

        $this->dispatch('toast', type: 'success', message: 'تم حذف الدفعة بنجاح');
    }

    private function refreshSaleStatus(): void
    {
        $this->sale->refresh();

        $paid = (float) $this->sale->payments()->sum('amount');
        $total = (float) $this->sale->total;

        if ($paid <= 0) {
            $status = 'pending';
        } elseif ($paid < $total) {
            $status = 'partial';
        } else {
            $status = 'paid';
        }

        if ($this->sale->status !== 'cancelled') {
            $this->sale->update([
                'status' => $status,
            ]);
        }

        $this->sale->refresh();
        $this->sale->load(['payments.user']);
    }

    public function render()
    {
        $this->sale->load(['payments.user']);

        return view('livewire.admin.sales.sale-payments', [
            'payments' => $this->sale->payments()->with('user')->latest()->get(),
        ]);
    }
}