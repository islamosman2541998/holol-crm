<?php

namespace App\Livewire\Admin\Sales;

use App\Models\Payment;
use App\Models\Sale;
use App\Services\PaymentService;
use App\Traits\AuthorizesOwnedRecords;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class SalePayments extends Component
{
    use AuthorizesOwnedRecords;

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

    public function save(PaymentService $paymentService): void
    {
        abort_unless(auth()->user()->can('payments.create'), 403);

        $this->authorizeOwnedRecordAccess('sales.view_all', $this->sale->user_id);

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

        $paymentService->create($this->sale, $data, auth()->id());
        $this->sale->refresh();

        $this->reset([
            'amount',
            'notes',
        ]);

        $this->payment_method = 'cash';
        $this->paid_at = now()->format('Y-m-d');

        $this->dispatch('toast', type: 'success', message: 'تم إضافة الدفعة بنجاح');
    }

    public function reverse(int $paymentId, string $reason, PaymentService $paymentService): void
    {
        abort_unless(auth()->user()->can('payments.delete'), 403);

        $this->authorizeOwnedRecordAccess('sales.view_all', $this->sale->user_id);

        $payment = Payment::query()
            ->where('sale_id', $this->sale->id)
            ->findOrFail($paymentId);

        $validated = Validator::make([
            'reason' => $reason,
        ], [
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'reason.required' => 'سبب عكس الدفعة مطلوب.',
            'reason.min' => 'اكتب سببًا واضحًا لا يقل عن 5 أحرف.',
        ])->validate();

        $paymentService->reverse($payment, $validated['reason'], auth()->id());
        $this->sale->refresh();

        $this->dispatch('toast', type: 'success', message: 'تم عكس الدفعة محاسبيًا بنجاح');
    }

    public function render()
    {
        $this->sale->load(['payments.user']);

        $reversedPayments = $this->sale->payments()
            ->onlyTrashed()
            ->with(['user', 'reversal.user'])
            ->latest('deleted_at')
            ->get();

        return view('livewire.admin.sales.sale-payments', [
            'payments' => $this->sale->payments()->with('user')->latest()->get(),
            'reversedPayments' => $reversedPayments,
        ]);
    }
}
