<?php

namespace App\Livewire\Admin\Reports;

use App\Exports\SalesPaymentsReportExport;
use App\Models\Client;
use App\Models\Member;
use App\Models\Sale;
use App\Models\Service;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Url;

class SalesPaymentsReport extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $paymentMethod = '';

    #[Url(except: '')]
    public string $clientId = '';

    #[Url(except: '')]
    public string $serviceId = '';

    #[Url(except: '')]
    public string $userId = '';

    #[Url(except: '')]
    public string $soldFrom = '';

    #[Url(except: '')]
    public string $soldTo = '';

    #[Url(except: '')]
    public string $paidFrom = '';

    #[Url(except: '')]
    public string $paidTo = '';

    #[Url(except: '')]
    public string $quotationState = '';

    #[Url(except: '')]
    public string $balanceState = '';

    #[Url(except: 10)]
    public int $perPage = 10;

    protected string $paginationTheme = 'bootstrap';

    public function updated($property): void
    {
        if (in_array($property, [
            'search',
            'status',
            'paymentMethod',
            'clientId',
            'serviceId',
            'userId',
            'soldFrom',
            'soldTo',
            'paidFrom',
            'paidTo',
            'quotationState',
            'balanceState',
            'perPage',
        ])) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'status',
            'paymentMethod',
            'clientId',
            'serviceId',
            'userId',
            'soldFrom',
            'soldTo',
            'paidFrom',
            'paidTo',
            'quotationState',
            'balanceState',
            'perPage',
        ]);

        $this->perPage = 10;
        $this->resetPage();
    }

    public function exportExcel()
    {
        $filename = 'sales-payments-report-' . now()->format('Y-m-d-H-i') . '.xlsx';

        return Excel::download(
            new SalesPaymentsReportExport($this->filters()),
            $filename
        );
    }

    private function filters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->status,
            'payment_method' => $this->paymentMethod,
            'client_id' => $this->clientId,
            'service_id' => $this->serviceId,
            'user_id' => $this->userId,
            'sold_from' => $this->soldFrom,
            'sold_to' => $this->soldTo,
            'paid_from' => $this->paidFrom,
            'paid_to' => $this->paidTo,
            'quotation_state' => $this->quotationState,
            'balance_state' => $this->balanceState,
        ];
    }

    private function salesQuery()
    {
        return Sale::query()
            ->with([
                'client',
                'user',
                'quotation',
                'items.service',
                'payments.user',
            ])
            ->withCount(['items', 'payments'])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->whereHas('client', function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('company', 'like', '%' . $this->search . '%')
                            ->orWhere('mobile', 'like', '%' . $this->search . '%')
                            ->orWhere('phone', 'like', '%' . $this->search . '%');
                    })
                        ->orWhereHas('quotation', function ($query) {
                            $query->where('quotation_number', 'like', '%' . $this->search . '%');
                        })
                        ->orWhere('notes', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, fn($query) => $query->where('status', $this->status))
            ->when($this->clientId, fn($query) => $query->where('client_id', $this->clientId))
            ->when($this->userId, fn($query) => $query->where('user_id', $this->userId))
            ->when($this->serviceId, function ($query) {
                $query->whereHas('items', function ($query) {
                    $query->where('service_id', $this->serviceId);
                });
            })
            ->when($this->paymentMethod, function ($query) {
                $query->where(function ($query) {
                    $query->where('payment_method', $this->paymentMethod)
                        ->orWhereHas('payments', function ($query) {
                            $query->where('payment_method', $this->paymentMethod);
                        });
                });
            })
            ->when($this->soldFrom, function ($query) {
                $query->where('sold_at', '>=', Carbon::parse($this->soldFrom)->startOfDay());
            })
            ->when($this->soldTo, function ($query) {
                $query->where('sold_at', '<=', Carbon::parse($this->soldTo)->endOfDay());
            })
            ->when($this->paidFrom, function ($query) {
                $query->whereHas('payments', function ($query) {
                    $query->where('paid_at', '>=', Carbon::parse($this->paidFrom)->startOfDay());
                });
            })
            ->when($this->paidTo, function ($query) {
                $query->whereHas('payments', function ($query) {
                    $query->where('paid_at', '<=', Carbon::parse($this->paidTo)->endOfDay());
                });
            })
            ->when($this->quotationState, function ($query) {
                match ($this->quotationState) {
                    'with' => $query->whereNotNull('quotation_id'),
                    'without' => $query->whereNull('quotation_id'),
                    default => null,
                };
            })
            ->when($this->balanceState, function ($query) {
                match ($this->balanceState) {
                    'has_remaining' => $query->whereIn('status', ['pending', 'partial']),

                    'fully_paid' => $query->where('status', 'paid'),

                    'no_payments' => $query->doesntHave('payments'),

                    'partial_paid' => $query->where('status', 'partial')
                        ->whereHas('payments'),

                    default => null,
                };
            });
    }



    private function buildStats($query): array
    {
        $sales = (clone $query)->get();

        $salesTotal = 0;
        $paidTotal = 0;

        $methods = [
            'cash' => 0,
            'bank_transfer' => 0,
            'instapay' => 0,
            'vodafone_cash' => 0,
            'other' => 0,
        ];

        foreach ($sales as $sale) {
            $salesTotal += (float) $sale->total;

            foreach ($sale->payments as $payment) {
                if ($this->paymentMethod && $payment->payment_method !== $this->paymentMethod) {
                    continue;
                }

                if ($this->paidFrom && $payment->paid_at?->lt(Carbon::parse($this->paidFrom)->startOfDay())) {
                    continue;
                }

                if ($this->paidTo && $payment->paid_at?->gt(Carbon::parse($this->paidTo)->endOfDay())) {
                    continue;
                }

                $paidTotal += (float) $payment->amount;

                if (array_key_exists($payment->payment_method, $methods)) {
                    $methods[$payment->payment_method] += (float) $payment->amount;
                }
            }
        }

        return [
            'sales_count' => $sales->count(),
            'pending_count' => $sales->where('status', 'pending')->count(),
            'partial_count' => $sales->where('status', 'partial')->count(),
            'paid_count' => $sales->where('status', 'paid')->count(),
            'cancelled_count' => $sales->where('status', 'cancelled')->count(),
            'sales_total' => $salesTotal,
            'paid_total' => $paidTotal,
            'remaining_total' => max($salesTotal - $paidTotal, 0),
            'cash_total' => $methods['cash'],
            'bank_transfer_total' => $methods['bank_transfer'],
            'instapay_total' => $methods['instapay'],
            'vodafone_cash_total' => $methods['vodafone_cash'],
            'other_total' => $methods['other'],
        ];
    }

    public function render()
    {
        $query = $this->salesQuery();

        $stats = $this->buildStats(clone $query);

        $sales = $query
            ->latest()
            ->paginate($this->perPage);

        $clients = Client::query()
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->orderBy('name')
            ->get();

        $members = Member::query()
            ->with(['user', 'team'])
            ->whereNotNull('user_id')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.reports.sales-payments-report', [
            'sales' => $sales,
            'stats' => $stats,
            'clients' => $clients,
            'services' => $services,
            'members' => $members,
        ]);
    }
}
