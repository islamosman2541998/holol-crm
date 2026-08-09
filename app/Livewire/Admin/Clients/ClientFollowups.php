<?php

namespace App\Livewire\Admin\Clients;

use App\Models\Client;
use App\Models\ClientFollowup;
use App\Traits\AuthorizesOwnedRecords;
use Livewire\Component;

class ClientFollowups extends Component
{
    use AuthorizesOwnedRecords;

    public Client $client;

    public string $note = '';
    public ?string $next_followup_at = null;
    public string $status = 'pending';

    public function mount(Client $client): void
    {
        $this->client = $client;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('followups.create'), 403);

        $this->authorizeOwnedRecordAccess('clients.view_all', $this->client->assigned_to);

        $data = $this->validate([
            'note' => ['required', 'string', 'min:3'],
            'next_followup_at' => ['nullable', 'date'],
            'status' => ['required', 'in:pending,done,cancelled'],
        ], [
            'note.required' => 'ملاحظة المتابعة مطلوبة',
            'note.min' => 'ملاحظة المتابعة قصيرة جدًا',
            'next_followup_at.date' => 'تاريخ المتابعة غير صحيح',
            'status.required' => 'حالة المتابعة مطلوبة',
        ]);

        $followup = ClientFollowup::query()->create([
            'client_id' => $this->client->id,
            'user_id' => auth()->id(),
            'note' => $data['note'],
            'next_followup_at' => $data['next_followup_at'],
            'status' => $data['status'],
        ]);

        $this->client->logActivity(
            event: 'followup_created',
            title: 'تم إضافة متابعة',
            description: 'تم إضافة متابعة للعميل.',
            newValues: [
                'followup_id' => $followup->id,
                'note' => $followup->note,
                'next_followup_at' => $followup->next_followup_at?->toDateTimeString(),
                'status' => $followup->status,
            ]
        );

        $this->reset([
            'note',
            'next_followup_at',
        ]);

        $this->status = 'pending';

        $this->dispatch('activity-log-updated');
        $this->dispatch('toast', type: 'success', message: 'تم إضافة المتابعة بنجاح');
    }

    public function markAsDone(int $followupId): void
    {
        abort_unless(auth()->user()->can('followups.edit'), 403);

        $this->authorizeOwnedRecordAccess('clients.view_all', $this->client->assigned_to);

        $followup = ClientFollowup::query()
            ->where('client_id', $this->client->id)
            ->findOrFail($followupId);

        $oldStatus = $followup->status;

        $followup->update([
            'status' => 'done',
        ]);

        $this->client->logActivity(
            event: 'followup_done',
            title: 'تم إنهاء متابعة',
            description: 'تم تغيير حالة متابعة العميل إلى تمت.',
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
        $this->dispatch('toast', type: 'success', message: 'تم تحديث حالة المتابعة');
    }

    public function delete(int $followupId): void
    {
        abort_unless(auth()->user()->can('followups.delete'), 403);

        $this->authorizeOwnedRecordAccess('clients.view_all', $this->client->assigned_to);

        $followup = ClientFollowup::query()
            ->where('client_id', $this->client->id)
            ->findOrFail($followupId);

        $this->client->logActivity(
            event: 'followup_deleted',
            title: 'تم حذف متابعة',
            description: 'تم حذف متابعة من سجل العميل.',
            oldValues: [
                'followup_id' => $followup->id,
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
        $followups = $this->client
            ->followups()
            ->with('user')
            ->latest()
            ->get();

        return view('livewire.admin.clients.client-followups', [
            'followups' => $followups,
        ]);
    }
}