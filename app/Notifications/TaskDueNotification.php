<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class TaskDueNotification extends Notification
{
    public function __construct(
        private readonly Collection $todayTasks,
        private readonly Collection $overdueTasks,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'today_count' => $this->todayTasks->count(),
            'overdue_count' => $this->overdueTasks->count(),
            'today_tasks' => $this->todayTasks->map(fn ($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'due_at' => $task->due_at?->toIso8601String(),
            ])->values()->all(),
            'overdue_tasks' => $this->overdueTasks->map(fn ($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'due_at' => $task->due_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }
}
