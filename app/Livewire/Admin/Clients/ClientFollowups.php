<?php

namespace App\Livewire\Admin\Clients;

use App\Models\Client;
use App\Models\ClientFollowup;
use Livewire\Component;

class ClientFollowups extends Component
{
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

        ClientFollowup::query()->create([
            'client_id' => $this->client->id,
            'user_id' => auth()->id(),
            'note' => $data['note'],
            'next_followup_at' => $data['next_followup_at'],
            'status' => $data['status'],
        ]);

        $this->reset([
            'note',
            'next_followup_at',
        ]);

        $this->status = 'pending';

        $this->dispatch('toast', type: 'success', message: 'تم إضافة المتابعة بنجاح');
    }

    public function markAsDone(int $followupId): void
    {
        abort_unless(auth()->user()->can('followups.edit'), 403);

        $followup = ClientFollowup::query()
            ->where('client_id', $this->client->id)
            ->findOrFail($followupId);

        $followup->update([
            'status' => 'done',
        ]);

        $this->dispatch('toast', type: 'success', message: 'تم تحديث حالة المتابعة');
    }

    public function delete(int $followupId): void
    {
        abort_unless(auth()->user()->can('followups.delete'), 403);

        $followup = ClientFollowup::query()
            ->where('client_id', $this->client->id)
            ->findOrFail($followupId);

        $followup->delete();

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