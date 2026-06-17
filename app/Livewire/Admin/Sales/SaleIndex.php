<?php

namespace App\Livewire\Admin\Sales;

use App\Models\Sale;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class SaleIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $userId = '';

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

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'status',
            'userId',
        ]);

        $this->resetPage();
    }

    public function render()
    {
        $sales = Sale::query()
            ->with(['client', 'user', 'items.service'])
            ->when($this->search, function ($query) {
                $query->whereHas('client', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('company', 'like', '%' . $this->search . '%')
                        ->orWhere('mobile', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->userId, function ($query) {
                $query->where('user_id', $this->userId);
            })
            ->latest()
            ->paginate(10);

        $users = User::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.sales.sale-index', [
            'sales' => $sales,
            'users' => $users,
        ]);
    }
}