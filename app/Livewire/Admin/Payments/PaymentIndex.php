<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use App\Services\PaymentService;
use App\Traits\AuthorizesOwnedRecords;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentIndex extends Component
{
    use AuthorizesOwnedRecords, WithPagination;

    public string $search = '';

    public string $paymentMethod = '';

    public string $userId = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentMethod(): void
    {
        $this->resetPage();
    }

    public function updatingUserId(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'paymentMethod',
            'userId',
            'dateFrom',
            'dateTo',
        ]);

        $this->resetPage();
    }

    public function reverse(int $paymentId, string $reason, PaymentService $paymentService): void
    {
        abort_unless(auth()->user()->can('payments.delete'), 403);

        $payment = Payment::query()
            ->with('sale')
            ->findOrFail($paymentId);

        $sale = $payment->sale;

        abort_unless($sale, 404);

        $saleQuery = Sale::query()->whereKey($sale->id);
        $this->applyOwnedRecordScope($saleQuery, 'sales.view_all', 'user_id');
        abort_unless($saleQuery->exists(), 403);

        $validated = Validator::make([
            'reason' => $reason,
        ], [
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'reason.required' => 'سبب عكس الدفعة مطلوب.',
            'reason.min' => 'اكتب سببًا واضحًا لا يقل عن 5 أحرف.',
        ])->validate();

        $paymentService->reverse($payment, $validated['reason'], auth()->id());

        $this->dispatch('toast', type: 'success', message: 'تم عكس الدفعة محاسبيًا بنجاح');
    }

    public function render()
    {
        $query = Payment::query()
            ->with(['sale.client', 'user'])
            ->whereHas('sale', function ($query) {
                $this->applyOwnedRecordScope($query, 'sales.view_all', 'user_id');
            })
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('notes', 'like', '%'.$this->search.'%')
                        ->orWhereHas('sale', function ($query) {
                            $query->where('id', $this->search);
                        })
                        ->orWhereHas('sale.client', function ($query) {
                            $query->where('name', 'like', '%'.$this->search.'%')
                                ->orWhere('company', 'like', '%'.$this->search.'%')
                                ->orWhere('mobile', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->paymentMethod, function ($query) {
                $query->where('payment_method', $this->paymentMethod);
            })
            ->when($this->userId, function ($query) {
                $query->where('user_id', $this->userId);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('paid_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('paid_at', '<=', $this->dateTo);
            });

        $totalAmount = (clone $query)->sum('amount');

        $payments = $query
            ->latest('paid_at')
            ->latest()
            ->paginate(10);

        $users = User::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.payments.payment-index', [
            'payments' => $payments,
            'users' => $users,
            'totalAmount' => $totalAmount,
        ]);
    }
}
