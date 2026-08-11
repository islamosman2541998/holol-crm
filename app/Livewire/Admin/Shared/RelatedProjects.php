<?php

namespace App\Livewire\Admin\Shared;

use App\Models\Member;
use App\Models\Project;
use Livewire\Component;

class RelatedProjects extends Component
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
        $query = Project::query()
            ->with([
                'client',
                'service',
                'team',
                'manager',
            ])
            ->withCount([
                'tasks',
                'openTasks',
            ])
            ->when($this->type === 'client', function ($query) {
                $query->where('client_id', $this->id);
            })
            ->when($this->type === 'member', function ($query) {
                $query->where(function ($query) {
                    $query->where('manager_member_id', $this->id)
                        ->orWhereHas('tasks.assignedMembers', function ($query) {
                            $query->where('members.id', $this->id);
                        });
                });
            })
            ->when($this->type === 'team', function ($query) {
                $query->where('team_id', $this->id);
            });

        $this->applyProjectVisibilityScope($query);

        $projects = $query
            ->latest()
            ->get();

        return view('livewire.admin.shared.related-projects', [
            'projects' => $projects,
        ]);
    }

    private function applyProjectVisibilityScope($query): void
    {
        $user = auth()->user();

        if (! $user) {
            $query->whereRaw('1 = 0');
            return;
        }

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