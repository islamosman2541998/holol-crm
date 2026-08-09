<?php

namespace App\Livewire\Admin\Tasks;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class TaskAttachments extends Component
{
    use WithFileUploads;

    public Task $task;

    public $file;
    public string $notes = '';

    public function mount(Task $task): void
    {
        $this->task = $task;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('tasks.edit'), 403);

        $this->authorizeTaskAccess();

        $data = $this->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,csv,txt,jpg,jpeg,png,gif,webp,zip,rar'],
            'notes' => ['nullable', 'string'],
        ], [
            'file.required' => 'الملف مطلوب',
            'file.file' => 'يجب اختيار ملف صحيح',
            'file.max' => 'حجم الملف لا يزيد عن 5MB',
            'file.mimes' => 'نوع الملف غير مسموح به',
        ]);

        $uploadedFile = $data['file'];

        $path = $uploadedFile->store('tasks/attachments', 'local');

        $attachment = TaskAttachment::query()->create([
            'task_id' => $this->task->id,
            'user_id' => auth()->id(),
            'member_id' => auth()->user()?->member?->id,
            'file_name' => $uploadedFile->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $uploadedFile->getClientMimeType(),
            'file_size' => $uploadedFile->getSize(),
            'notes' => $data['notes'] ?? null,
        ]);

        $this->task->logActivity(
            event: 'attachment_created',
            title: 'تم رفع مرفق',
            description: 'تم رفع مرفق جديد على المهمة: ' . $attachment->file_name,
            newValues: [
                'attachment_id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'file_path' => $attachment->file_path,
            ]
        );

        $this->reset(['file', 'notes']);

        $this->dispatch('activity-log-updated');
        $this->dispatch('toast', type: 'success', message: 'تم رفع المرفق بنجاح');
    }

    public function delete(int $attachmentId): void
    {
        abort_unless(auth()->user()->can('tasks.edit'), 403);

        $this->authorizeTaskAccess();

        $attachment = TaskAttachment::query()
            ->where('task_id', $this->task->id)
            ->findOrFail($attachmentId);

        $this->task->logActivity(
            event: 'attachment_deleted',
            title: 'تم حذف مرفق',
            description: 'تم حذف مرفق من المهمة: ' . $attachment->file_name,
            oldValues: [
                'attachment_id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'file_path' => $attachment->file_path,
            ]
        );

        Storage::disk('local')->delete($attachment->file_path);

        $attachment->delete();

        $this->dispatch('activity-log-updated');
        $this->dispatch('toast', type: 'success', message: 'تم حذف المرفق');
    }

    public function render()
    {
        $attachments = $this->task
            ->attachments()
            ->with(['user', 'member'])
            ->latest()
            ->get();

        return view('livewire.admin.tasks.task-attachments', [
            'attachments' => $attachments,
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
            $isTeamTask = $this->task->assignedMember()
                ->where('team_id', $member->team_id)
                ->exists();

            abort_unless($isTeamTask, 403);

            return;
        }

        abort_unless((int) $this->task->assigned_member_id === (int) $member->id, 403);
    }
}