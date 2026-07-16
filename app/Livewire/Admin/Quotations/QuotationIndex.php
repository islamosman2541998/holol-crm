<?php

namespace App\Livewire\Admin\Quotations;

use App\Models\Quotation;
use Livewire\Component;
use Livewire\WithPagination;

class QuotationIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'status']);
        $this->resetPage();
    }

    public function render()
    {
        $quotations = Quotation::query()
            ->with(['client', 'user', 'sale'])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('quotation_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('client', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('company', 'like', '%' . $this->search . '%')
                                ->orWhere('mobile', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.quotations.quotation-index', [
            'quotations' => $quotations,
        ]);
    }
}