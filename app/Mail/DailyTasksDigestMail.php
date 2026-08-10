<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DailyTasksDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Collection $todayTasks,
        public readonly Collection $overdueTasks,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('مهامك اليوم: ' . $this->todayTasks->count() . ' اليوم، ' . $this->overdueTasks->count() . ' متأخرة')
            ->view('emails.daily-tasks-digest');
    }
}
