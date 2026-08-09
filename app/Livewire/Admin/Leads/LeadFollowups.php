<?php

namespace App\Livewire\Admin\Leads;

use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Traits\AuthorizesOwnedRecords;
use Livewire\Component;

class LeadFollowups extends Component
{
    use AuthorizesOwnedRecords;

    public Lead $lead;

    public string $type = 'note';
    public string $note = '';
    public ?string $next_followup_at = null;
    public string $status = 'pending';

    public function mount(Lead $lead): void
    {
        $this->lead = $lead;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('leads.edit'), 403);

        $this->authorizeOwnedRecordAccess('leads.view_all', $this->lead->assigned_to);

        $data = $this->validate([
            'type' => ['required', 'in:call,whatsapp,meeting,note,email'],
            'note' => ['required', 'string', 'min:3'],
            'next_followup_at' => ['nullable', 'date'],
            'status' => ['required', 'in:pending,done,cancelled'],
        ], [
            'type.required' => 'نوع المتابعة مطلوب',
            'note.required' => 'ملاحظة المتابعة مطلوبة',
            'note.min' => 'ملاحظة المتابعة قصيرة جدًا',
            'next_followup_at.date' => 'تاريخ المتابعة غير صحيح',
        ]);

        $followup = LeadFollowup::query()->create([
            'lead_id' => $this->lead->id,
            'user_id' => auth()->id(),
            'type' => $data['type'],
            'note' => $data['note'],
            'next_followup_at' => $data['next_followup_at'],
            'status' => $data['status'],
        ]);

        $this->lead->logActivity(
            event: 'followup_created',
            title: 'تم إضافة متابعة',
            description: 'تم إضافة متابعة للـ Lead من نوع: ' . $followup->type_label . '.',
            newValues: [
                'followup_id' => $followup->id,
                'type' => $followup->type,
                'type_label' => $followup->type_label,
                'note' => $followup->note,
                'next_followup_at' => $followup->next_followup_at?->toDateTimeString(),
                'status' => $followup->status,
            ]
        );

        $this->reset([
            'note',
            'next_followup_at',
        ]);

        $this->type = 'note';
        $this->status = 'pending';

        $this->dispatch('activity-log-updated');
        $this->dispatch('toast', type: 'success', message: 'تم إضافة المتابعة بنجاح');
    }

    public function markAsDone(int $followupId): void
    {
        abort_unless(auth()->user()->can('leads.edit'), 403);

        $this->authorizeOwnedRecordAccess('leads.view_all', $this->lead->assigned_to);

        $followup = LeadFollowup::query()
            ->where('lead_id', $this->lead->id)
            ->findOrFail($followupId);

        $oldStatus = $followup->status;

        $followup->update([
            'status' => 'done',
        ]);

        $this->lead->logActivity(
            event: 'followup_done',
            title: 'تم إنهاء متابعة',
            description: 'تم تغيير حالة متابعة الـ Lead إلى تمت.',
            oldValues: [
                'followup_id' => $followup->id,
                'status' => $oldStatus,
            ],
            newValues: [
                'followup_id' => $followup->id,
                'status' => 'done',
            ]
        );

        $this->dispatch('activity-log-updated');
        $this->dispatch('toast', type: 'success', message: 'تم تحديث المتابعة');
    }

    public function delete(int $followupId): void
    {
        abort_unless(auth()->user()->can('leads.edit'), 403);

        $this->authorizeOwnedRecordAccess('leads.view_all', $this->lead->assigned_to);

        $followup = LeadFollowup::query()
            ->where('lead_id', $this->lead->id)
            ->findOrFail($followupId);

        $this->lead->logActivity(
            event: 'followup_deleted',
            title: 'تم حذف متابعة',
            description: 'تم حذف متابعة من سجل الـ Lead.',
            oldValues: [
                'followup_id' => $followup->id,
                'type' => $followup->type,
                'type_label' => $followup->type_label,
                'note' => $followup->note,
                'next_followup_at' => $followup->next_followup_at?->toDateTimeString(),
                'status' => $followup->status,
            ]
        );

        $followup->delete();

        $this->dispatch('activity-log-updated');
        $this->dispatch('toast', type: 'success', message: 'تم حذف المتابعة');
    }

    public function render()
    {
        $followups = $this->lead
            ->followups()
            ->with('user')
            ->latest()
            ->get();

        return view('livewire.admin.leads.lead-followups', [
            'followups' => $followups,
        ]);
    }
}