<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    public function __construct(
        private readonly Task $task,
        private readonly ?string $assignedByName = null,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'due_at' => $this->task->due_at?->toIso8601String(),
            'assigned_by' => $this->assignedByName,
        ];
    }
}
