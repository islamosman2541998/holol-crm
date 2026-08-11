<?php

namespace App\Livewire\Admin\Shared;

use App\Models\Member;
use App\Models\Task;
use Livewire\Component;

class RelatedTasks extends Component
{
    public string $type;
    public int $id;

    public function mount(string $type, int $id): void
    {
        $this->type = $type;
        $this->id = $id;
    }

    public function render()
    {
        $query = Task::query()
            ->with([
                'assignedMembers.team',
                'client',
                'lead',
                'project.client',
                'creator',

            ])
            ->when($this->type === 'client', function ($query) {
                $query->where('client_id', $this->id);
            })
            ->when($this->type === 'lead', function ($query) {
                $query->where('lead_id', $this->id);
            })
            ->when($this->type === 'member', function ($query) {
                $query->whereHas('assignedMembers', function ($query) {
                    $query->where('members.id', $this->id);
                });
            })
            ->when($this->type === 'project', function ($query) {
                $query->where('project_id', $this->id);
            });

        $this->applyTaskVisibilityScope($query);

        $tasks = $query
            ->latest()
            ->get();
            

        return view('livewire.admin.shared.related-tasks', [
            'tasks' => $tasks,
        ]);
    }

    private function applyTaskVisibilityScope($query): void
    {
        $user = auth()->user();

        if (! $user) {
            $query->whereRaw('1 = 0');
            return;
        }

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

            $query->whereHas('assignedMembers', function ($query) use ($teamMemberIds) {
                $query->whereIn('members.id', $teamMemberIds);
            });

            return;
        }

        $query->whereHas('assignedMembers', function ($query) use ($member) {
            $query->where('members.id', $member->id);
        });
    }
}