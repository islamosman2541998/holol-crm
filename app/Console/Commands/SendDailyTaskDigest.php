<?php

namespace App\Console\Commands;

use App\Mail\DailyTasksDigestMail;
use App\Models\Member;
use App\Models\Task;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDailyTaskDigest extends Command
{
    protected $signature = 'tasks:send-daily-digest';

    protected $description = 'Email each member with a linked account their due-today and overdue tasks';

    public function handle(): int
    {
        $sent = 0;
        $failed = 0;

        Member::query()
            ->whereNotNull('user_id')
            ->where('status', 'active')
            ->with('user')
            ->chunkById(50, function ($members) use (&$sent, &$failed) {
                foreach ($members as $member) {
                    if (! $member->user || ! $member->user->email) {
                        continue;
                    }

                    $todayTasks = Task::query()->forMember($member->id)->dueToday()->get();
                    $overdueTasks = Task::query()->forMember($member->id)->overdue()->get();

                    if ($todayTasks->isEmpty() && $overdueTasks->isEmpty()) {
                        continue;
                    }

                    try {
                        Mail::to($member->user->email)
                            ->send(new DailyTasksDigestMail($member->user, $todayTasks, $overdueTasks));

                        $sent++;
                    } catch (\Throwable $e) {
                        $failed++;

                        Log::error('Failed to send daily task digest', [
                            'user_id' => $member->user->id,
                            'email' => $member->user->email,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

        $this->info("Daily task digest sent to {$sent} member(s), {$failed} failed.");

        return self::SUCCESS;
    }
}
