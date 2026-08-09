<?php

namespace App\Livewire\Admin\Clients;

use App\Models\Client;
use App\Models\User;
use App\Traits\AuthorizesOwnedRecords;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Member;

class ClientIndex extends Component
{
    use WithPagination, AuthorizesOwnedRecords;

    public string $search = '';
    public string $status = '';
    public string $assignedTo = '';

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingAssignedTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'status',
            'assignedTo',
        ]);

        $this->resetPage();
    }

    public function render()
    {
        $clients = Client::query()
            ->with(['assignedUser', 'assignedMember.team', 'latestFollowup.user']);

        $this->applyOwnedRecordScope($clients, 'clients.view_all', 'assigned_to');

        $clients = $clients
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('company', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('mobile', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->assignedTo, function ($query) {
                $query->where('assigned_to', $this->assignedTo);
            })
            ->latest()
            ->paginate(10);

        $members = Member::query()
            ->assignable()
            ->get();
        return view('livewire.admin.clients.client-index', [
            'clients' => $clients,
            'members' => $members,
        ]);
    }
}
