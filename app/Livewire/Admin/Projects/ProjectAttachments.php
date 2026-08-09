<?php

namespace App\Livewire\Admin\Projects;

use App\Models\Project;
use App\Models\ProjectAttachment;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProjectAttachments extends Component
{
    use WithFileUploads;

    public Project $project;

    public $file;
    public string $notes = '';

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('projects.edit'), 403);

        $this->authorizeProjectAccess();

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

        $path = $uploadedFile->store('projects/attachments', 'local');

        $attachment = ProjectAttachment::query()->create([
            'project_id' => $this->project->id,
            'user_id' => auth()->id(),
            'member_id' => auth()->user()?->member?->id,
            'file_name' => $uploadedFile->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $uploadedFile->getClientMimeType(),
            'file_size' => $uploadedFile->getSize(),
            'notes' => $data['notes'] ?? null,
        ]);

        $this->project->logActivity(
            event: 'attachment_created',
            title: 'تم رفع مرفق',
            description: 'تم رفع مرفق جديد على المشروع: ' . $attachment->file_name,
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
        abort_unless(auth()->user()->can('projects.edit'), 403);

        $this->authorizeProjectAccess();

        $attachment = ProjectAttachment::query()
            ->where('project_id', $this->project->id)
            ->findOrFail($attachmentId);

        $this->project->logActivity(
            event: 'attachment_deleted',
            title: 'تم حذف مرفق',
            description: 'تم حذف مرفق من المشروع: ' . $attachment->file_name,
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
        $attachments = $this->project
            ->attachments()
            ->with(['user', 'member'])
            ->latest()
            ->get();

        return view('livewire.admin.projects.project-attachments', [
            'attachments' => $attachments,
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