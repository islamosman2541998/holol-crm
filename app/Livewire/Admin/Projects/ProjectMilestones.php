<?php

namespace App\Livewire\Admin\Projects;

use App\Models\Project;
use App\Models\ProjectMilestone;
use Livewire\Component;

class ProjectMilestones extends Component
{
    public Project $project;

    public string $title = '';
    public string $description = '';
    public string $status = 'pending';
    public ?string $due_date = null;

    public ?int $editingId = null;

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('projects.edit'), 403);

        $this->authorizeProjectAccess();

        $data = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
            'due_date' => ['nullable', 'date'],
        ], [
            'title.required' => 'عنوان المرحلة مطلوب',
            'status.required' => 'حالة المرحلة مطلوبة',
        ]);

        if ($this->editingId) {
            $milestone = ProjectMilestone::query()
                ->where('project_id', $this->project->id)
                ->findOrFail($this->editingId);

            $oldValues = $milestone->toArray();

            $data['completed_date'] = $data['status'] === 'completed'
                ? now()->toDateString()
                : null;

            $milestone->update($data);

            $this->project->logActivity(
                event: 'milestone_updated',
                title: 'تم تعديل مرحلة',
                description: 'تم تعديل مرحلة في المشروع: ' . $milestone->title,
                oldValues: $oldValues,
                newValues: $milestone->toArray()
            );

            $message = 'تم تحديث المرحلة بنجاح';
        } else {
            $data['project_id'] = $this->project->id;
            $data['created_by'] = auth()->id();
            $data['sort_order'] = ProjectMilestone::query()
                ->where('project_id', $this->project->id)
                ->max('sort_order') + 1;

            $data['completed_date'] = $data['status'] === 'completed'
                ? now()->toDateString()
                : null;

            $milestone = ProjectMilestone::query()->create($data);

            $this->project->logActivity(
                event: 'milestone_created',
                title: 'تم إضافة مرحلة',
                description: 'تم إضافة مرحلة جديدة للمشروع: ' . $milestone->title,
                newValues: $milestone->toArray()
            );

            $message = 'تم إضافة المرحلة بنجاح';
        }

        $this->resetForm();

        $this->dispatch('activity-log-updated');
        $this->dispatch('toast', type: 'success', message: $message);
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()->can('projects.edit'), 403);

        $this->authorizeProjectAccess();

        $milestone = ProjectMilestone::query()
            ->where('project_id', $this->project->id)
            ->findOrFail($id);

        $this->editingId = $milestone->id;
        $this->title = $milestone->title;
        $this->description = $milestone->description ?? '';
        $this->status = $milestone->status;
        $this->due_date = $milestone->due_date?->format('Y-m-d');
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()->can('projects.edit'), 403);

        $this->authorizeProjectAccess();

        $milestone = ProjectMilestone::query()
            ->where('project_id', $this->project->id)
            ->findOrFail($id);

        $this->project->logActivity(
            event: 'milestone_deleted',
            title: 'تم حذف مرحلة',
            description: 'تم حذف مرحلة من المشروع: ' . $milestone->title,
            oldValues: $milestone->toArray()
        );

        $milestone->delete();

        $this->dispatch('activity-log-updated');
        $this->dispatch('toast', type: 'success', message: 'تم حذف المرحلة');
    }

    public function resetForm(): void
    {
        $this->reset([
            'title',
            'description',
            'due_date',
            'editingId',
        ]);

        $this->status = 'pending';
    }

    public function render()
    {
        $milestones = $this->project
            ->milestones()
            ->latest('sort_order')
            ->get();

        return view('livewire.admin.projects.project-milestones', [
            'milestones' => $milestones,
        ]);
    }

    private function authorizeProjectAccess(): void
    {
        $user = auth()->user();

        if ($user->can('projects.view_all')) {
            return;
        }

        $member = $user->member;

        abort_unless($member, 403);

        if ($this->project->manager_member_id && (int) $this->project->manager_member_id === (int) $member->id) {
            return;
        }

        if ($member->is_manager && $member->team_id && (int) $this->project->team_id === (int) $member->team_id) {
            return;
        }

        $hasTaskInsideProject = $this->project->tasks()
            ->where('assigned_member_id', $member->id)
            ->exists();

        abort_unless($hasTaskInsideProject, 403);
    }
}