<?php

namespace App\Livewire\Admin\Projects;

use App\Models\Member;
use App\Models\Project;
use App\Models\Team;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class ProjectIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $priority = '';

    #[Url(except: '')]
    public string $teamId = '';

    #[Url(except: '')]
    public string $managerMemberId = '';

    #[Url(except: '')]
    public string $dateFilter = '';

    #[Url(except: '')]
    public string $statusGroup = '';

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }
    public function updatingStatusGroup(): void
    {
        $this->resetPage();
    }
    public function updatingPriority(): void
    {
        $this->resetPage();
    }

    public function updatingTeamId(): void
    {
        $this->resetPage();
    }

    public function updatingManagerMemberId(): void
    {
        $this->resetPage();
    }

    public function updatingDateFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'status',
            'priority',
            'teamId',
            'managerMemberId',
            'dateFilter',
            'statusGroup',
        ]);

        $this->resetPage();
    }

    public function render()
    {
        $query = Project::query()
            ->with([
                'client',
                'service',
                'team',
                'manager',
                'creator',
            ])
            ->withCount([
                'tasks',
                'openTasks',
                'completedTasks',
                'milestones',
                'completedMilestones',
            ]);

        $this->applyVisibilityScope($query);

        $projects = $query
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('code', 'like', '%' . $this->search . '%')
                        ->orWhereHas('client', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('company', 'like', '%' . $this->search . '%')
                                ->orWhere('mobile', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('service', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->statusGroup, function ($query) {
                match ($this->statusGroup) {
                    'active' => $query->whereIn('status', ['new', 'planning', 'in_progress', 'on_hold']),
                    default => null,
                };
            })
            ->when($this->priority, function ($query) {
                $query->where('priority', $this->priority);
            })
            ->when($this->teamId, function ($query) {
                $query->where('team_id', $this->teamId);
            })
            ->when($this->managerMemberId, function ($query) {
                $query->where('manager_member_id', $this->managerMemberId);
            })
            ->when($this->dateFilter, function ($query) {
                match ($this->dateFilter) {
                    'overdue' => $query
                        ->whereNotNull('due_date')
                        ->where('due_date', '<', today())
                        ->whereNotIn('status', ['completed', 'cancelled']),
                    'this_month' => $query
                        ->whereBetween('due_date', [
                            now()->startOfMonth()->toDateString(),
                            now()->endOfMonth()->toDateString(),
                        ]),
                    'completed' => $query->where('status', 'completed'),
                    default => null,
                };
            })
            ->latest()
            ->paginate(10);

        $teams = Team::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $members = Member::query()
            ->where('status', 'active')
            ->with('team')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.projects.project-index', [
            'projects' => $projects,
            'teams' => $teams,
            'members' => $members,
        ]);
    }

    private function applyVisibilityScope($query): void
    {
        $user = auth()->user();

        if ($user->can('projects.view_all')) {
            return;
        }

        $member = $user->member;

        if (! $member) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->where(function ($query) use ($member) {
            $query->where('manager_member_id', $member->id)
                ->orWhereHas('tasks.assignedMembers', function ($query) use ($member) {
                    $query->where('members.id', $member->id);
                });

            if ($member->is_manager && $member->team_id) {
                $query->orWhere('team_id', $member->team_id);
            }
        });
    }
}
