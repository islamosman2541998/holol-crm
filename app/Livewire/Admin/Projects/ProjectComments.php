<?php

namespace App\Livewire\Admin\Projects;

use App\Models\Project;
use App\Models\ProjectComment;
use Livewire\Component;

class ProjectComments extends Component
{
    public Project $project;

    public string $comment = '';

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('projects.view'), 403);

        $this->authorizeProjectAccess();

        $data = $this->validate([
            'comment' => ['required', 'string', 'min:3'],
        ], [
            'comment.required' => 'التعليق مطلوب',
            'comment.min' => 'التعليق قصير جدًا',
        ]);

        $projectComment = ProjectComment::query()->create([
            'project_id' => $this->project->id,
            'user_id' => auth()->id(),
            'member_id' => auth()->user()?->member?->id,
            'comment' => $data['comment'],
        ]);

        $this->project->logActivity(
            event: 'comment_created',
            title: 'تم إضافة تعليق',
            description: 'تم إضافة تعليق جديد على المشروع.',
            newValues: [
                'comment_id' => $projectComment->id,
                'comment' => $projectComment->comment,
            ]
        );

        $this->reset('comment');

        $this->dispatch('activity-log-updated');
        $this->dispatch('toast', type: 'success', message: 'تم إضافة التعليق بنجاح');
    }

    public function delete(int $commentId): void
    {
        abort_unless(auth()->user()->can('projects.edit'), 403);

        $this->authorizeProjectAccess();

        $comment = ProjectComment::query()
            ->where('project_id', $this->project->id)
            ->findOrFail($commentId);

        $this->project->logActivity(
            event: 'comment_deleted',
            title: 'تم حذف تعليق',
            description: 'تم حذف تعليق من المشروع.',
            oldValues: [
                'comment_id' => $comment->id,
                'comment' => $comment->comment,
            ]
        );

        $comment->delete();

        $this->dispatch('activity-log-updated');
        $this->dispatch('toast', type: 'success', message: 'تم حذف التعليق');
    }

    public function render()
    {
        $comments = $this->project
            ->comments()
            ->with(['user', 'member'])
            ->latest()
            ->get();

        return view('livewire.admin.projects.project-comments', [
            'comments' => $comments,
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
            ->whereHas('assignedMembers', function ($query) use ($member) {
                $query->where('members.id', $member->id);
            })
            ->exists();

        abort_unless($hasTaskInsideProject, 403);
    }
}