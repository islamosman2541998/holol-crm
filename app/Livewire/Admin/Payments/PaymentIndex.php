<?php

namespace App\Livewire\Admin\Payments;

use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentIndex extends Component
{
    use WithPagination;

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

    public function delete(int $paymentId): void
    {
        abort_unless(auth()->user()->can('payments.delete'), 403);

        $payment = Payment::query()
            ->with('sale')
            ->findOrFail($paymentId);

        $sale = $payment->sale;

        $payment->delete();

        if ($sale) {
            $this->refreshSaleStatus($sale);
        }

        $this->dispatch('toast', type: 'success', message: 'تم حذف الدفعة بنجاح');
    }

    private function refreshSaleStatus(Sale $sale): void
    {
        $sale->refresh();

        if ($sale->status === 'cancelled') {
            return;
        }

        $paid = (float) $sale->payments()->sum('amount');
        $total = (float) $sale->total;

        if ($paid <= 0) {
            $status = 'pending';
        } elseif ($paid < $total) {
            $status = 'partial';
        } else {
            $status = 'paid';
        }

        $sale->update([
            'status' => $status,
        ]);
    }

    public function render()
    {
        $query = Payment::query()
            ->with(['sale.client', 'user'])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('notes', 'like', '%' . $this->search . '%')
                        ->orWhereHas('sale', function ($query) {
                            $query->where('id', $this->search);
                        })
                        ->orWhereHas('sale.client', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('company', 'like', '%' . $this->search . '%')
                                ->orWhere('mobile', 'like', '%' . $this->search . '%');
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