<?php

namespace App\Livewire\Admin\Tasks;

use App\Models\Task;
use App\Models\TaskComment;
use Livewire\Component;

class TaskComments extends Component
{
    public Task $task;

    public string $comment = '';

    public function mount(Task $task): void
    {
        $this->task = $task;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('tasks.view'), 403);

        $this->authorizeTaskAccess();

        $data = $this->validate([
            'comment' => ['required', 'string', 'min:3'],
        ], [
            'comment.required' => 'التعليق مطلوب',
            'comment.min' => 'التعليق قصير جدًا',
        ]);

        $taskComment = TaskComment::query()->create([
            'task_id' => $this->task->id,
            'user_id' => auth()->id(),
            'member_id' => auth()->user()?->member?->id,
            'comment' => $data['comment'],
        ]);

        $this->task->logActivity(
            event: 'comment_created',
            title: 'تم إضافة تعليق',
            description: 'تم إضافة تعليق جديد على المهمة.',
            newValues: [
                'comment_id' => $taskComment->id,
                'comment' => $taskComment->comment,
            ]
        );

        $this->reset('comment');

        $this->dispatch('activity-log-updated');
        $this->dispatch('toast', type: 'success', message: 'تم إضافة التعليق بنجاح');
    }

    public function delete(int $commentId): void
    {
        abort_unless(auth()->user()->can('tasks.edit'), 403);

        $this->authorizeTaskAccess();

        $comment = TaskComment::query()
            ->where('task_id', $this->task->id)
            ->findOrFail($commentId);

        $this->task->logActivity(
            event: 'comment_deleted',
            title: 'تم حذف تعليق',
            description: 'تم حذف تعليق من المهمة.',
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
        $comments = $this->task
            ->comments()
            ->with(['user', 'member'])
            ->latest()
            ->get();

        return view('livewire.admin.tasks.task-comments', [
            'comments' => $comments,
        ]);
    }

    private function authorizeTaskAccess(): void
    {
        $user = auth()->user();

        if ($user->can('tasks.view_all')) {
            return;
        }

        $member = $user->member;

        abort_unless($member, 403);

        if ($member->is_manager && $member->team_id) {
            $isTeamTask = $this->task->assignedMembers()
                ->where('team_id', $member->team_id)
                ->exists();

            abort_unless($isTeamTask, 403);

            return;
        }

        abort_unless(
            $this->task->assignedMembers()->where('members.id', $member->id)->exists(),
            403
        );
    }
}