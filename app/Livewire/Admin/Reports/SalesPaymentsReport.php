<?php

namespace App\Livewire\Admin\Reports;

use App\Exports\SalesPaymentsReportExport;
use App\Models\Client;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Service;
use App\Traits\AuthorizesOwnedRecords;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Url;

class SalesPaymentsReport extends Component
{
    use WithPagination, AuthorizesOwnedRecords;

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
        $query = Sale::query();

        $this->applyOwnedRecordScope($query, 'sales.view_all', 'user_id');

        return $query
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
        $statusCounts = (clone $query)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $revenueQuery = (clone $query)->where('status', '!=', 'cancelled');
        $salesTotal = (float) (clone $revenueQuery)->sum('total');

        $saleIds = $revenueQuery->pluck('id');

        $paymentsQuery = Payment::query()
            ->whereIn('sale_id', $saleIds)
            ->when($this->paymentMethod, fn($q) => $q->where('payment_method', $this->paymentMethod))
            ->when($this->paidFrom, fn($q) => $q->where('paid_at', '>=', Carbon::parse($this->paidFrom)->startOfDay()))
            ->when($this->paidTo, fn($q) => $q->where('paid_at', '<=', Carbon::parse($this->paidTo)->endOfDay()));

        $paidTotal = (float) (clone $paymentsQuery)->sum('amount');

        $methodTotals = (clone $paymentsQuery)
            ->selectRaw('payment_method, sum(amount) as aggregate')
            ->groupBy('payment_method')
            ->pluck('aggregate', 'payment_method');

        return [
            'sales_count' => (int) $statusCounts->sum(),
            'pending_count' => (int) ($statusCounts['pending'] ?? 0),
            'partial_count' => (int) ($statusCounts['partial'] ?? 0),
            'paid_count' => (int) ($statusCounts['paid'] ?? 0),
            'cancelled_count' => (int) ($statusCounts['cancelled'] ?? 0),
            'sales_total' => $salesTotal,
            'paid_total' => $paidTotal,
            'remaining_total' => max($salesTotal - $paidTotal, 0),
            'cash_total' => (float) ($methodTotals['cash'] ?? 0),
            'bank_transfer_total' => (float) ($methodTotals['bank_transfer'] ?? 0),
            'instapay_total' => (float) ($methodTotals['instapay'] ?? 0),
            'vodafone_cash_total' => (float) ($methodTotals['vodafone_cash'] ?? 0),
            'other_total' => (float) ($methodTotals['other'] ?? 0),
        ];
    }

    public function render()
    {
        $query = $this->salesQuery();

        $stats = $this->buildStats(clone $query);

        $sales = $query
            ->with([
                'client',
                'user',
                'quotation',
                'items.service',
                'payments.user',
            ])
            ->withCount(['items', 'payments'])
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
