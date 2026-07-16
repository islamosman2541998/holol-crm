<?php

namespace App\Livewire\Admin\Reports;

use App\Exports\LeadReportExport;
use App\Models\Lead;
use App\Models\Member;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Url;

class LeadReport extends Component
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
    public string $conversionState = '';

    #[Url(except: '')]
    public string $taskState = '';

    #[Url(except: 'without')]
    public string $trashedState = 'without';

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
            'conversionState',
            'taskState',
            'trashedState',
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
            'conversionState',
            'taskState',
            'trashedState',
            'perPage',
        ]);

        $this->trashedState = 'without';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function exportExcel()
    {
        $filename = 'leads-report-' . now()->format('Y-m-d-H-i') . '.xlsx';

        return Excel::download(
            new LeadReportExport($this->filters()),
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
            'conversion_state' => $this->conversionState,
            'task_state' => $this->taskState,
            'trashed_state' => $this->trashedState,
        ];
    }

    private function leadsQuery()
    {
        $query = Lead::query();

        if (
            $this->trashedState === 'with' ||
            $this->status === 'converted' ||
            $this->conversionState === 'converted'
        ) {
            $query->withTrashed();
        }

        if ($this->trashedState === 'only') {
            $query->onlyTrashed();
        }

        return $query
            ->with([
                'assignedUser',
                'assignedMember.team',
                'convertedClient',
                'latestFollowup',
                'followups',
                'tasks',
            ])
            ->withCount([
                'followups',
                'tasks',
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('company', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('mobile', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
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
            ->when($this->conversionState, function ($query) {
                match ($this->conversionState) {
                    'converted' => $query->whereNotNull('converted_client_id'),
                    'not_converted' => $query->whereNull('converted_client_id'),
                    default => null,
                };
            })
            ->when($this->taskState, function ($query) {
                match ($this->taskState) {
                    'with' => $query->whereHas('tasks'),
                    'without' => $query->doesntHave('tasks'),
                    default => null,
                };
            });
    }

    private function buildStats($query): array
    {
        $leads = (clone $query)->get();

        $total = $leads->count();
        $converted = $leads->filter(fn($lead) => $lead->converted_client_id || $lead->status === 'converted')->count();

        return [
            'leads_count' => $total,
            'new_count' => $leads->where('status', 'new')->count(),
            'qualified_count' => $leads->where('status', 'qualified')->count(),
            'converted_count' => $converted,
            'lost_count' => $leads->where('status', 'lost')->count(),
            'without_followups' => $leads->filter(fn($lead) => $lead->followups->isEmpty())->count(),
            'overdue_followups' => $leads->filter(function ($lead) {
                return $lead->followups->contains(function ($followup) {
                    return $followup->next_followup_at
                        && $followup->next_followup_at->lt(today())
                        && ! in_array($followup->status, ['done', 'completed', 'cancelled']);
                });
            })->count(),
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0,
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
        $query = $this->leadsQuery();

        $stats = $this->buildStats(clone $query);

        $leads = $query
            ->latest()
            ->paginate($this->perPage);

        $sources = Lead::query()
            ->whereNotNull('source')
            ->where('source', '!=', '')
            ->distinct()
            ->orderBy('source')
            ->pluck('source');

        $cities = Lead::query()
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

        return view('livewire.admin.reports.lead-report', [
            'leads' => $leads,
            'stats' => $stats,
            'sources' => $sources,
            'cities' => $cities,
            'members' => $members,
        ]);
    }
}
