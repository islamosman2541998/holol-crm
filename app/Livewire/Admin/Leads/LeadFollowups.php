<?php

namespace App\Livewire\Admin\Leads;

use App\Models\Lead;
use App\Models\LeadFollowup;
use Livewire\Component;

class LeadFollowups extends Component
{
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

        LeadFollowup::query()->create([
            'lead_id' => $this->lead->id,
            'user_id' => auth()->id(),
            'type' => $data['type'],
            'note' => $data['note'],
            'next_followup_at' => $data['next_followup_at'],
            'status' => $data['status'],
        ]);

        $this->reset([
            'note',
            'next_followup_at',
        ]);

        $this->type = 'note';
        $this->status = 'pending';

        $this->dispatch('toast', type: 'success', message: 'تم إضافة المتابعة بنجاح');
    }

    public function markAsDone(int $followupId): void
    {
        abort_unless(auth()->user()->can('leads.edit'), 403);

        $followup = LeadFollowup::query()
            ->where('lead_id', $this->lead->id)
            ->findOrFail($followupId);

        $followup->update([
            'status' => 'done',
        ]);

        $this->dispatch('toast', type: 'success', message: 'تم تحديث المتابعة');
    }

    public function delete(int $followupId): void
    {
        abort_unless(auth()->user()->can('leads.edit'), 403);

        $followup = LeadFollowup::query()
            ->where('lead_id', $this->lead->id)
            ->findOrFail($followupId);

        $followup->delete();

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