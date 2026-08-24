<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Task $task,
        public readonly ?string $assignedByName = null,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('تم تسنيد مهمة لك: ' . $this->task->title)
            ->view('emails.task-assigned');
    }
}
