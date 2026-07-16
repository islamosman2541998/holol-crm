<?php

namespace App\Exports;

use App\Models\Quotation;
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

class QuotationReportExport extends DefaultValueBinder implements
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
            'رقم عرض السعر',
            'العميل',
            'الشركة',
            'موبايل العميل',
            'هاتف العميل',
            'إيميل العميل',
            'الموظف',
            'الخدمات',
            'Subtotal',
            'VAT',
            'Total',
            'الحالة',
            'تاريخ العرض',
            'صالح حتى',
            'الصلاحية',
            'تحول لبيع؟',
            'رقم البيع',
            'حالة البيع',
            'إجمالي البيع',
            'المدفوع',
            'المتبقي',
            'تاريخ الفتح',
            'تاريخ الإغلاق',
            'ملاحظات',
        ];
    }

    public function map($quotation): array
    {
        $services = $quotation->items
            ->map(fn ($item) => ($item->service?->name ?? '-') . ' × ' . $item->quantity)
            ->implode(' | ');

        $isExpired = $quotation->valid_until
            && $quotation->valid_until->lt(today())
            && ! in_array($quotation->status, ['closed', 'cancelled']);

        $paidAmount = $quotation->sale
            ? (float) $quotation->sale->payments->sum('amount')
            : 0;

        $saleTotal = $quotation->sale
            ? (float) $quotation->sale->total
            : 0;

        $remainingAmount = max($saleTotal - $paidAmount, 0);

        return [
            $quotation->quotation_number,
            $quotation->client?->name,
            $quotation->client?->company,
            $this->normalizePhone($quotation->client?->mobile),
            $this->normalizePhone($quotation->client?->phone),
            $quotation->client?->email,
            $quotation->user?->name,
            $services,
            (float) $quotation->subtotal,
            (float) $quotation->vat,
            (float) $quotation->total,
            $quotation->status_label,
            $quotation->quotation_date?->format('Y-m-d'),
            $quotation->valid_until?->format('Y-m-d'),
            $isExpired ? 'منتهي الصلاحية' : 'ساري / غير منتهي',
            $quotation->sale ? 'نعم' : 'لا',
            $quotation->sale?->id,
            $quotation->sale?->status_label,
            $saleTotal ?: null,
            $paidAmount ?: null,
            $quotation->sale ? $remainingAmount : null,
            $quotation->opened_at?->format('Y-m-d H:i'),
            $quotation->closed_at?->format('Y-m-d H:i'),
            $quotation->notes,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function bindValue(Cell $cell, $value): bool
    {
        if (in_array($cell->getColumn(), ['D', 'E'])) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    private function query()
    {
        return Quotation::query()
            ->with([
                'client',
                'user',
                'items.service',
                'sale.payments',
            ])
            ->withCount(['items'])
            ->when($this->filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('quotation_number', 'like', '%' . $search . '%')
                        ->orWhere('notes', 'like', '%' . $search . '%')
                        ->orWhereHas('client', function ($query) use ($search) {
                            $query->where('name', 'like', '%' . $search . '%')
                                ->orWhere('company', 'like', '%' . $search . '%')
                                ->orWhere('mobile', 'like', '%' . $search . '%')
                                ->orWhere('phone', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($this->filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($this->filters['client_id'] ?? null, fn ($query, $clientId) => $query->where('client_id', $clientId))
            ->when($this->filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($this->filters['service_id'] ?? null, function ($query, $serviceId) {
                $query->whereHas('items', function ($query) use ($serviceId) {
                    $query->where('service_id', $serviceId);
                });
            })
            ->when($this->filters['quotation_from'] ?? null, function ($query, $date) {
                $query->whereDate('quotation_date', '>=', Carbon::parse($date)->toDateString());
            })
            ->when($this->filters['quotation_to'] ?? null, function ($query, $date) {
                $query->whereDate('quotation_date', '<=', Carbon::parse($date)->toDateString());
            })
            ->when($this->filters['valid_from'] ?? null, function ($query, $date) {
                $query->whereDate('valid_until', '>=', Carbon::parse($date)->toDateString());
            })
            ->when($this->filters['valid_to'] ?? null, function ($query, $date) {
                $query->whereDate('valid_until', '<=', Carbon::parse($date)->toDateString());
            })
            ->when($this->filters['validity_state'] ?? null, function ($query, $validityState) {
                match ($validityState) {
                    'expired' => $query->whereNotNull('valid_until')
                        ->whereDate('valid_until', '<', today())
                        ->whereNotIn('status', ['closed', 'cancelled']),

                    'valid' => $query->whereNotNull('valid_until')
                        ->whereDate('valid_until', '>=', today()),

                    'no_validity' => $query->whereNull('valid_until'),

                    default => null,
                };
            })
            ->when($this->filters['sale_state'] ?? null, function ($query, $saleState) {
                match ($saleState) {
                    'with_sale' => $query->whereHas('sale'),
                    'without_sale' => $query->doesntHave('sale'),
                    'open_without_sale' => $query->where('status', 'open')->doesntHave('sale'),
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