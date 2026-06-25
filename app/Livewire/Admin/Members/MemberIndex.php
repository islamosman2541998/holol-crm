<?php

namespace App\Livewire\Admin\Members;

use App\Models\Member;
use App\Models\Team;
use Livewire\Component;
use Livewire\WithPagination;

class MemberIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $teamId = '';
    public string $isManager = '';

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingTeamId(): void
    {
        $this->resetPage();
    }

    public function updatingIsManager(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'status',
            'teamId',
            'isManager',
        ]);

        $this->resetPage();
    }

    public function render()
    {
        $members = Member::query()
            ->with(['user', 'team', 'directManager'])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('job_title', 'like', '%' . $this->search . '%')
                        ->orWhere('department', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('mobile', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($query) {
                            $query->where('email', 'like', '%' . $this->search . '%')
                                ->orWhere('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->teamId, function ($query) {
                $query->where('team_id', $this->teamId);
            })
            ->when($this->isManager !== '', function ($query) {
                $query->where('is_manager', $this->isManager);
            })
            ->latest()
            ->paginate(10);

        $teams = Team::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.members.member-index', [
            'members' => $members,
            'teams' => $teams,
        ]);
    }
}