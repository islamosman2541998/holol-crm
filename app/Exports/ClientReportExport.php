<?php

namespace App\Exports;

use App\Models\Client;
use App\Traits\AuthorizesOwnedRecords;
use App\Traits\SanitizesExcelFormulas;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ClientReportExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting,
    WithCustomValueBinder
{
    use AuthorizesOwnedRecords, SanitizesExcelFormulas;

    public function __construct(
        private readonly array $filters = []
    ) {
    }

    public function collection(): Collection
    {
        return $this->query()
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'اسم العميل',
            'الشركة',
            'الحالة',
            'الموظف المسؤول',
            'الفريق',
            'المصدر',
            'المدينة',
            'الإيميل',
            'الموبايل',
            'الهاتف',
            'عدد المتابعات',
            'آخر متابعة',
            'حالة آخر متابعة',
            'عدد عروض الأسعار',
            'عروض مفتوحة',
            'إجمالي عروض الأسعار',
            'عدد المبيعات',
            'إجمالي المبيعات',
            'إجمالي المدفوع',
            'إجمالي المتبقي',
            'عدد المشاريع',
            'عدد المهام',
            'تاريخ الإضافة',
        ];
    }

    public function map($client): array
    {
        $salesTotal = $client->sales->sum(fn ($sale) => (float) $sale->total);
        $paidTotal = $client->sales->sum(fn ($sale) => (float) $sale->payments->sum('amount'));
        $remainingTotal = max($salesTotal - $paidTotal, 0);

        $openQuotations = $client->quotations->where('status', 'open');
        $quotationsTotal = $client->quotations->sum(fn ($quotation) => (float) $quotation->total);

        return [
            $client->name,
            $client->company,
            $client->status_label,
            $client->assignedMember?->name ?? $client->assignedUser?->name,
            $client->assignedMember?->team?->name,
            $client->source,
            $client->city,
            $client->email,
            $this->normalizePhone($client->mobile),
            $this->normalizePhone($client->phone),
            $client->followups_count,
            $client->latestFollowup?->next_followup_at?->format('Y-m-d'),
            $client->latestFollowup?->status,
            $client->quotations_count,
            $openQuotations->count(),
            $quotationsTotal,
            $client->sales_count,
            $salesTotal,
            $paidTotal,
            $remainingTotal,
            $client->projects_count,
            $client->tasks_count,
            $client->created_at?->format('Y-m-d'),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), ['I', 'J']) || $this->looksLikeFormula($value)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    private function query()
    {
        $query = Client::query()
            ->with([
                'assignedUser',
                'assignedMember.team',
                'latestFollowup',
                'followups',
                'quotations',
                'sales.payments',
                'projects',
                'tasks',
            ])
            ->withCount([
                'followups',
                'quotations',
                'sales',
                'projects',
                'tasks',
            ]);

        $this->applyOwnedRecordScope($query, 'clients.view_all', 'assigned_to');

        return $query
            ->when($this->filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('company', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('mobile', 'like', '%' . $search . '%')
                        ->orWhere('source', 'like', '%' . $search . '%')
                        ->orWhere('city', 'like', '%' . $search . '%');
                });
            })
            ->when($this->filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($this->filters['source'] ?? null, fn ($query, $source) => $query->where('source', $source))
            ->when($this->filters['city'] ?? null, fn ($query, $city) => $query->where('city', $city))
            ->when($this->filters['assigned_to'] ?? null, fn ($query, $assignedTo) => $query->where('assigned_to', $assignedTo))
            ->when($this->filters['date_from'] ?? null, function ($query, $dateFrom) {
                $query->where('created_at', '>=', Carbon::parse($dateFrom)->startOfDay());
            })
            ->when($this->filters['date_to'] ?? null, function ($query, $dateTo) {
                $query->where('created_at', '<=', Carbon::parse($dateTo)->endOfDay());
            })
            ->when($this->filters['followup_state'] ?? null, function ($query, $followupState) {
                match ($followupState) {
                    'with' => $query->whereHas('followups'),
                    'without' => $query->doesntHave('followups'),
                    'today' => $query->whereHas('followups', function ($query) {
                        $query->whereDate('next_followup_at', today());
                    }),
                    'overdue' => $query->whereHas('followups', function ($query) {
                        $query->whereDate('next_followup_at', '<', today())
                            ->whereNotIn('status', ['done', 'completed', 'cancelled']);
                    }),
                    'upcoming' => $query->whereHas('followups', function ($query) {
                        $query->whereDate('next_followup_at', '>', today())
                            ->whereNotIn('status', ['done', 'completed', 'cancelled']);
                    }),
                    default => null,
                };
            })
            ->when($this->filters['quotation_state'] ?? null, function ($query, $quotationState) {
                if ($quotationState === 'without') {
                    $query->doesntHave('quotations');
                } else {
                    $query->whereHas('quotations', function ($query) use ($quotationState) {
                        $query->where('status', $quotationState);
                    });
                }
            })
            ->when($this->filters['sales_state'] ?? null, function ($query, $salesState) {
                if ($salesState === 'with') {
                    $query->whereHas('sales');
                } elseif ($salesState === 'without') {
                    $query->doesntHave('sales');
                } else {
                    $query->whereHas('sales', function ($query) use ($salesState) {
                        $query->where('status', $salesState);
                    });
                }
            });
    }

    private function normalizePhone(?string $phone): string
    {
        if (! $phone) {
            return '';
        }

        $phone = trim($phone);
        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($digits, '20') && strlen($digits) === 12) {
            return '0' . substr($digits, 2);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            return '0' . $digits;
        }

        return $digits ?: $phone;
    }
}