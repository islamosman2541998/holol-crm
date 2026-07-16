<?php

namespace App\Exports;

use App\Models\Lead;
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

class LeadReportExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting,
    WithCustomValueBinder
{
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
            'اسم Lead',
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
            'نوع آخر متابعة',
            'عدد المهام',
            'تم التحويل؟',
            'العميل المحول إليه',
            'تاريخ التحويل',
            'محذوف؟',
            'تاريخ الإضافة',
            'ملاحظات',
        ];
    }

    public function map($lead): array
    {
        return [
            $lead->name,
            $lead->company,
            $lead->status_label,
            $lead->assignedMember?->name ?? $lead->assignedUser?->name,
            $lead->assignedMember?->team?->name,
            $lead->source,
            $lead->city,
            $lead->email,
            $this->normalizePhone($lead->mobile),
            $this->normalizePhone($lead->phone),
            $lead->followups_count,
            $lead->latestFollowup?->next_followup_at?->format('Y-m-d'),
            $lead->latestFollowup?->status,
            $lead->latestFollowup?->type,
            $lead->tasks_count,
            $lead->converted_client_id ? 'نعم' : 'لا',
            $lead->convertedClient?->name,
            $lead->converted_at?->format('Y-m-d'),
            $lead->trashed() ? 'نعم' : 'لا',
            $lead->created_at?->format('Y-m-d'),
            $lead->notes,
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
        if (in_array($cell->getColumn(), ['I', 'J'])) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    private function query()
    {
        $query = Lead::query();

        $status = $this->filters['status'] ?? null;
        $conversionState = $this->filters['conversion_state'] ?? null;
        $trashedState = $this->filters['trashed_state'] ?? 'without';

        if (
            $trashedState === 'with' ||
            $status === 'converted' ||
            $conversionState === 'converted'
        ) {
            $query->withTrashed();
        }

        if ($trashedState === 'only') {
            $query->onlyTrashed();
        }

        return $query
            ->with([
                'assignedUser',
                'assignedMember.team',
                'convertedClient',
                'latestFollowup',
                'followups',
                'tasks',
            ])
            ->withCount([
                'followups',
                'tasks',
            ])
            ->when($this->filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('company', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('mobile', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
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
            ->when($this->filters['conversion_state'] ?? null, function ($query, $conversionState) {
                match ($conversionState) {
                    'converted' => $query->whereNotNull('converted_client_id'),
                    'not_converted' => $query->whereNull('converted_client_id'),
                    default => null,
                };
            })
            ->when($this->filters['task_state'] ?? null, function ($query, $taskState) {
                match ($taskState) {
                    'with' => $query->whereHas('tasks'),
                    'without' => $query->doesntHave('tasks'),
                    default => null,
                };
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