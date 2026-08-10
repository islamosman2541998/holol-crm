<?php

namespace App\Support;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDueNotification;

class TaskReminders
{
    /**
     * Create today's database reminder notification for the user's member, once per day.
     * Returns true if a fresh notification was created (used to trigger the login popup).
     */
    public static function notifyIfDue(User $user): bool
    {
        $member = $user->member;

        if (! $member) {
            return false;
        }

        $alreadyNotifiedToday = $user->notifications()
            ->where('type', TaskDueNotification::class)
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadyNotifiedToday) {
            return false;
        }

        $todayTasks = Task::query()->forMember($member->id)->dueToday()->get();
        $overdueTasks = Task::query()->forMember($member->id)->overdue()->get();

        if ($todayTasks->isEmpty() && $overdueTasks->isEmpty()) {
            return false;
        }

        $user->notify(new TaskDueNotification($todayTasks, $overdueTasks));

        return true;
    }
}
