<?php

namespace App\Livewire\Admin\Leads;

use App\Models\LeadFollowup;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class LeadFollowupIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $type = '';
    public string $status = '';
    public string $userId = '';
    public string $dateFilter = '';

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
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
            'type',
            'status',
            'userId',
            'dateFilter',
        ]);

        $this->resetPage();
    }

    public function markAsDone(int $followupId): void
    {
        abort_unless(auth()->user()->can('leads.edit'), 403);

        $followup = LeadFollowup::query()->findOrFail($followupId);

        $followup->update([
            'status' => 'done',
        ]);

        $this->dispatch('toast', type: 'success', message: 'تم تحديث المتابعة إلى تمت');
    }

    public function delete(int $followupId): void
    {
        abort_unless(auth()->user()->can('leads.edit'), 403);

        $followup = LeadFollowup::query()->findOrFail($followupId);

        $followup->delete();

        $this->dispatch('toast', type: 'success', message: 'تم حذف المتابعة بنجاح');
    }

    public function render()
    {
        $leadFollowups = LeadFollowup::query()
            ->with(['lead', 'user'])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('note', 'like', '%' . $this->search . '%')
                        ->orWhereHas('lead', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('company', 'like', '%' . $this->search . '%')
                                ->orWhere('mobile', 'like', '%' . $this->search . '%')
                                ->orWhere('source', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('user', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->type, function ($query) {
                $query->where('type', $this->type);
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

        $todayCount = LeadFollowup::query()
            ->whereDate('next_followup_at', today())
            ->count();

        $overdueCount = LeadFollowup::query()
            ->where('status', 'pending')
            ->whereNotNull('next_followup_at')
            ->where('next_followup_at', '<', now())
            ->count();

        $pendingCount = LeadFollowup::query()
            ->where('status', 'pending')
            ->count();

        $doneCount = LeadFollowup::query()
            ->where('status', 'done')
            ->count();

        return view('livewire.admin.leads.lead-followup-index', [
            'leadFollowups' => $leadFollowups,
            'users' => $users,
            'todayCount' => $todayCount,
            'overdueCount' => $overdueCount,
            'pendingCount' => $pendingCount,
            'doneCount' => $doneCount,
        ]);
    }
}