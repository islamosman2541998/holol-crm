<?php

namespace App\Livewire\Admin\Reports;

use App\Exports\QuotationReportExport;
use App\Models\Client;
use App\Models\Member;
use App\Models\Quotation;
use App\Models\Service;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class QuotationReport extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public string $clientId = '';
    public string $userId = '';
    public string $serviceId = '';
    public string $quotationFrom = '';
    public string $quotationTo = '';
    public string $validFrom = '';
    public string $validTo = '';
    public string $validityState = '';
    public string $saleState = '';
    public int $perPage = 10;

    protected string $paginationTheme = 'bootstrap';

    public function updated($property): void
    {
        if (in_array($property, [
            'search',
            'status',
            'clientId',
            'userId',
            'serviceId',
            'quotationFrom',
            'quotationTo',
            'validFrom',
            'validTo',
            'validityState',
            'saleState',
            'perPage',
        ])) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'status',
            'clientId',
            'userId',
            'serviceId',
            'quotationFrom',
            'quotationTo',
            'validFrom',
            'validTo',
            'validityState',
            'saleState',
            'perPage',
        ]);

        $this->perPage = 10;
        $this->resetPage();
    }

    public function exportExcel()
    {
        $filename = 'quotations-report-' . now()->format('Y-m-d-H-i') . '.xlsx';

        return Excel::download(
            new QuotationReportExport($this->filters()),
            $filename
        );
    }

    private function filters(): array
    {
        return [
            'search' => $this->search,
            'status' => $this->status,
            'client_id' => $this->clientId,
            'user_id' => $this->userId,
            'service_id' => $this->serviceId,
            'quotation_from' => $this->quotationFrom,
            'quotation_to' => $this->quotationTo,
            'valid_from' => $this->validFrom,
            'valid_to' => $this->validTo,
            'validity_state' => $this->validityState,
            'sale_state' => $this->saleState,
        ];
    }

    private function quotationsQuery()
    {
        return Quotation::query()
            ->with([
                'client',
                'user',
                'items.service',
                'sale.payments',
            ])
            ->withCount(['items'])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('quotation_number', 'like', '%' . $this->search . '%')
                        ->orWhere('notes', 'like', '%' . $this->search . '%')
                        ->orWhereHas('client', function ($query) {
                            $query->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('company', 'like', '%' . $this->search . '%')
                                ->orWhere('mobile', 'like', '%' . $this->search . '%')
                                ->orWhere('phone', 'like', '%' . $this->search . '%')
                                ->orWhere('email', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->clientId, fn ($query) => $query->where('client_id', $this->clientId))
            ->when($this->userId, fn ($query) => $query->where('user_id', $this->userId))
            ->when($this->serviceId, function ($query) {
                $query->whereHas('items', function ($query) {
                    $query->where('service_id', $this->serviceId);
                });
            })
            ->when($this->quotationFrom, function ($query) {
                $query->whereDate('quotation_date', '>=', Carbon::parse($this->quotationFrom)->toDateString());
            })
            ->when($this->quotationTo, function ($query) {
                $query->whereDate('quotation_date', '<=', Carbon::parse($this->quotationTo)->toDateString());
            })
            ->when($this->validFrom, function ($query) {
                $query->whereDate('valid_until', '>=', Carbon::parse($this->validFrom)->toDateString());
            })
            ->when($this->validTo, function ($query) {
                $query->whereDate('valid_until', '<=', Carbon::parse($this->validTo)->toDateString());
            })
            ->when($this->validityState, function ($query) {
                match ($this->validityState) {
                    'expired' => $query->whereNotNull('valid_until')
                        ->whereDate('valid_until', '<', today())
                        ->whereNotIn('status', ['closed', 'cancelled']),

                    'valid' => $query->whereNotNull('valid_until')
                        ->whereDate('valid_until', '>=', today()),

                    'no_validity' => $query->whereNull('valid_until'),

                    default => null,
                };
            })
            ->when($this->saleState, function ($query) {
                match ($this->saleState) {
                    'with_sale' => $query->whereHas('sale'),
                    'without_sale' => $query->doesntHave('sale'),
                    'open_without_sale' => $query->where('status', 'open')->doesntHave('sale'),
                    default => null,
                };
            });
    }

    private function buildStats($query): array
    {
        $quotations = (clone $query)->get();

        $convertedCount = $quotations->filter(fn ($quotation) => $quotation->sale !== null)->count();

        $openWithoutSale = $quotations->filter(function ($quotation) {
            return $quotation->status === 'open' && $quotation->sale === null;
        })->count();

        $expiredCount = $quotations->filter(function ($quotation) {
            return $quotation->valid_until
                && $quotation->valid_until->lt(today())
                && ! in_array($quotation->status, ['closed', 'cancelled']);
        })->count();

        return [
            'quotations_count' => $quotations->count(),
            'pending_count' => $quotations->where('status', 'pending')->count(),
            'open_count' => $quotations->where('status', 'open')->count(),
            'closed_count' => $quotations->where('status', 'closed')->count(),
            'cancelled_count' => $quotations->where('status', 'cancelled')->count(),
            'converted_count' => $convertedCount,
            'open_without_sale_count' => $openWithoutSale,
            'expired_count' => $expiredCount,
            'subtotal_total' => (float) $quotations->sum('subtotal'),
            'vat_total' => (float) $quotations->sum('vat'),
            'grand_total' => (float) $quotations->sum('total'),
        ];
    }

    public function render()
    {
        $query = $this->quotationsQuery();

        $stats = $this->buildStats(clone $query);

        $quotations = $query
            ->latest()
            ->paginate($this->perPage);

        $clients = Client::query()
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->orderBy('name')
            ->get();

        $members = Member::query()
            ->with(['user', 'team'])
            ->whereNotNull('user_id')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.reports.quotation-report', [
            'quotations' => $quotations,
            'stats' => $stats,
            'clients' => $clients,
            'services' => $services,
            'members' => $members,
        ]);
    }
}