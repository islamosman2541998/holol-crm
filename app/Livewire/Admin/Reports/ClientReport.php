<?php

namespace App\Livewire\Admin\Reports;

use App\Exports\ClientReportExport;
use App\Models\Client;
use App\Models\Member;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Url;

class ClientReport extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $source = '';

    #[Url(except: '')]
    public string $city = '';

    #[Url(except: '')]
    public string $assignedTo = '';

    #[Url(except: '')]
    public string $dateFrom = '';

    #[Url(except: '')]
    public string $dateTo = '';

    #[Url(except: '')]
    public string $followupState = '';

    #[Url(except: '')]
    public string $quotationState = '';

    #[Url(except: '')]
    public string $salesState = '';

    #[Url(except: 10)]
    public int $perPage = 10;
    protected string $paginationTheme = 'bootstrap';

    public function updated($property): void
    {
        if (in_array($property, [
            'search',
            'status',
            'source',
            'city',
            'assignedTo',
            'dateFrom',
            'dateTo',
            'followupState',
            'quotationState',
            'salesState',
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
            'source',
            'city',
            'assignedTo',
            'dateFrom',
            'dateTo',
            'followupState',
            'quotationState',
            'salesState',
            'perPage',
        ]);

        $this->perPage = 10;
        $this->resetPage();
    }

    public function exportExcel()
    {
        $filename = 'clients-report-' . now()->format('Y-m-d-H-i') . '.xlsx';

        return Excel::download(
            new ClientReportExport($this->filters()),
            $filename
        );
    }

    private function filters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->status,
            'source' => $this->source,
            'city' => $this->city,
            'assigned_to' => $this->assignedTo,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'followup_state' => $this->followupState,
            'quotation_state' => $this->quotationState,
            'sales_state' => $this->salesState,
        ];
    }

    private function clientsQuery()
    {
        return Client::query()
            ->with([
                'assignedUser',
                'assignedMember.team',
                'latestFollowup',
                'followups',
                'quotations.items.service',
                'sales.payments',
                'projects',
                'tasks',
            ])
            ->withCount([
                'followups',
                'quotations',
                'sales',
                'projects',
                'tasks',
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('company', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('mobile', 'like', '%' . $this->search . '%')
                        ->orWhere('source', 'like', '%' . $this->search . '%')
                        ->orWhere('city', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, fn($query) => $query->where('status', $this->status))
            ->when($this->source, fn($query) => $query->where('source', $this->source))
            ->when($this->city, fn($query) => $query->where('city', $this->city))
            ->when($this->assignedTo, fn($query) => $query->where('assigned_to', $this->assignedTo))
            ->when($this->dateFrom, function ($query) {
                $query->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay());
            })
            ->when($this->dateTo, function ($query) {
                $query->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay());
            })
            ->when($this->followupState, function ($query) {
                match ($this->followupState) {
                    'with' => $query->whereHas('followups'),
                    'without' => $query->doesntHave('followups'),

                    'pending' => $query->whereHas('followups', function ($query) {
                        $query->where('status', 'pending');
                    }),

                    'today' => $query->whereHas('followups', function ($query) {
                        $query->whereDate('next_followup_at', today());
                    }),

                    'overdue' => $query->whereHas('followups', function ($query) {
                        $query->whereDate('next_followup_at', '<', today())
                            ->whereNotIn('status', ['done', 'completed', 'cancelled']);
                    }),

                    'upcoming' => $query->whereHas('followups', function ($query) {
                        $query->whereDate('next_followup_at', '>', today())
                            ->whereNotIn('status', ['done', 'completed', 'cancelled']);
                    }),

                    default => null,
                };
            })
            ->when($this->quotationState, function ($query) {
                if ($this->quotationState === 'without') {
                    $query->doesntHave('quotations');
                } else {
                    $query->whereHas('quotations', function ($query) {
                        $query->where('status', $this->quotationState);
                    });
                }
            })
            ->when($this->salesState, function ($query) {
                if ($this->salesState === 'with') {
                    $query->whereHas('sales');
                } elseif ($this->salesState === 'without') {
                    $query->doesntHave('sales');
                } else {
                    $query->whereHas('sales', function ($query) {
                        $query->where('status', $this->salesState);
                    });
                }
            });
    }

    private function buildStats($query): array
    {
        $clients = (clone $query)->get();

        $salesTotal = 0;
        $paidTotal = 0;

        foreach ($clients as $client) {
            foreach ($client->sales as $sale) {
                $salesTotal += (float) $sale->total;
                $paidTotal += (float) $sale->payments->sum('amount');
            }
        }

        return [
            'clients_count' => $clients->count(),
            'active_count' => $clients->where('status', 'active')->count(),
            'new_count' => $clients->where('status', 'new')->count(),
            'lost_count' => $clients->where('status', 'lost')->count(),
            'without_followups' => $clients->filter(fn($client) => $client->followups->isEmpty())->count(),
            'overdue_followups' => $clients->filter(function ($client) {
                return $client->followups->contains(function ($followup) {
                    return $followup->next_followup_at
                        && $followup->next_followup_at->lt(today())
                        && ! in_array($followup->status, ['done', 'completed', 'cancelled']);
                });
            })->count(),
            'sales_total' => $salesTotal,
            'paid_total' => $paidTotal,
            'remaining_total' => max($salesTotal - $paidTotal, 0),
        ];
    }

    public function formatPhone(?string $phone): string
    {
        if (! $phone) {
            return '-';
        }

        $phone = trim($phone);
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($digits, '20') && strlen($digits) === 12) {
            return '0' . substr($digits, 2);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            return '0' . $digits;
        }

        return $digits ?: $phone;
    }

    public function render()
    {
        $query = $this->clientsQuery();

        $stats = $this->buildStats(clone $query);

        $clients = $query
            ->latest()
            ->paginate($this->perPage);

        $sources = Client::query()
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');

        $cities = Client::query()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        $members = Member::query()
            ->with(['user', 'team'])
            ->whereNotNull('user_id')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.reports.client-report', [
            'clients' => $clients,
            'stats' => $stats,
            'sources' => $sources,
            'cities' => $cities,
            'members' => $members,
        ]);
    }
}
