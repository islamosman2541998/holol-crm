<?php

namespace App\Livewire\Admin\Followups;

use App\Models\ClientFollowup;
use App\Models\User;
use App\Traits\AuthorizesOwnedRecords;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class FollowupIndex extends Component
{
    use WithPagination, AuthorizesOwnedRecords;

    public string $search = '';
    public string $status = '';
    public string $userId = '';
    public string $dateFilter = '';

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingUserId(): void
    {
        $this->resetPage();
    }

    public function updatingDateFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'status',
            'userId',
            'dateFilter',
        ]);

        $this->resetPage();
    }

    public function markAsDone(int $followupId): void
    {
        abort_unless(auth()->user()->can('followups.edit'), 403);

        $followup = ClientFollowup::query()->with('client')->findOrFail($followupId);

        $this->authorizeFollowupAccess($followup);

        $followup->update([
            'status' => 'done',
        ]);

        $this->dispatch('toast', type: 'success', message: 'تم تحديث المتابعة إلى تمت');
    }

    public function delete(int $followupId): void
    {
        abort_unless(auth()->user()->can('followups.delete'), 403);

        $followup = ClientFollowup::query()->with('client')->findOrFail($followupId);

        $this->authorizeFollowupAccess($followup);

        $followup->delete();

        $this->dispatch('toast', type: 'success', message: 'تم حذف المتابعة بنجاح');
    }

    private function authorizeFollowupAccess(ClientFollowup $followup): void
    {
        $this->authorizeOwnedRecordAccess('clients.view_all', $followup->client?->assigned_to);
    }

    public function render()
    {
        $followups = ClientFollowup::query()
            ->with(['client', 'user'])
            ->whereHas('client', function ($query) {
                $this->applyOwnedRecordScope($query, 'clients.view_all', 'assigned_to');
            })
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('note', 'like', '%' . $this->search . '%')
                        ->orWhereHas('client', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('company', 'like', '%' . $this->search . '%')
                                ->orWhere('mobile', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('user', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->userId, function ($query) {
                $query->where('user_id', $this->userId);
            })
            ->when($this->dateFilter === 'today', function ($query) {
                $query->whereDate('next_followup_at', today());
            })
            ->when($this->dateFilter === 'overdue', function ($query) {
                $query->whereNotNull('next_followup_at')
                    ->where('next_followup_at', '<', now())
                    ->where('status', 'pending');
            })
            ->when($this->dateFilter === 'upcoming', function ($query) {
                $query->whereNotNull('next_followup_at')
                    ->where('next_followup_at', '>=', now())
                    ->where('status', 'pending');
            })
            ->latest('next_followup_at')
            ->latest()
            ->paginate(10);

        $users = User::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.followups.followup-index', [
            'followups' => $followups,
            'users' => $users,
        ]);
    }
}