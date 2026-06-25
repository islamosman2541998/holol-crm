<?php

namespace App\Livewire\Admin\Tasks;

use App\Models\Member;
use App\Models\Task;
use App\Models\Team;
use Livewire\Component;
use Livewire\WithPagination;

class TaskIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $priority = '';
    public string $assignedMemberId = '';
    public string $teamId = '';
    public string $dateFilter = '';

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPriority(): void
    {
        $this->resetPage();
    }

    public function updatingAssignedMemberId(): void
    {
        $this->resetPage();
    }

    public function updatingTeamId(): void
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
            'assignedMemberId',
            'teamId',
            'dateFilter',
        ]);

        $this->resetPage();
    }

    public function render()
    {
        $query = Task::query()
            ->with([
                'assignedMember.team',
                'creator',
                'client',
                'lead',
                'project.client',
            ]);

        $this->applyVisibilityScope($query);

        $tasks = $query
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('title', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%')
                        ->orWhereHas('client', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('company', 'like', '%' . $this->search . '%')
                                ->orWhere('mobile', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('lead', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('company', 'like', '%' . $this->search . '%')
                                ->orWhere('mobile', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->priority, function ($query) {
                $query->where('priority', $this->priority);
            })
            ->when($this->assignedMemberId, function ($query) {
                $query->where('assigned_member_id', $this->assignedMemberId);
            })
            ->when($this->teamId, function ($query) {
                $query->whereHas('assignedMember', function ($query) {
                    $query->where('team_id', $this->teamId);
                });
            })
            ->when($this->dateFilter, function ($query) {
                match ($this->dateFilter) {
                    'today' => $query->whereDate('due_at', today()),
                    'overdue' => $query
                        ->whereNotNull('due_at')
                        ->where('due_at', '<', now())
                        ->whereNotIn('status', ['completed', 'cancelled']),
                    'upcoming' => $query
                        ->whereNotNull('due_at')
                        ->where('due_at', '>', now())
                        ->whereNotIn('status', ['completed', 'cancelled']),
                    default => null,
                };
            })
            ->latest()
            ->paginate(10);

        $members = Member::query()
            ->where('status', 'active')
            ->with('team')
            ->orderBy('name')
            ->get();

        $teams = Team::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.tasks.task-index', [
            'tasks' => $tasks,
            'members' => $members,
            'teams' => $teams,
        ]);
    }

    private function applyVisibilityScope($query): void
    {
        $user = auth()->user();

        if ($user->can('tasks.view_all')) {
            return;
        }

        $member = $user->member;

        if (! $member) {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($member->is_manager && $member->team_id) {
            $teamMemberIds = Member::query()
                ->where('team_id', $member->team_id)
                ->pluck('id');

            $query->whereIn('assigned_member_id', $teamMemberIds);

            return;
        }

        $query->where('assigned_member_id', $member->id);
    }
}
