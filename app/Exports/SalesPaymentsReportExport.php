<?php

namespace App\Exports;

use App\Models\Sale;
use App\Traits\AuthorizesOwnedRecords;
use App\Traits\SanitizesExcelFormulas;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class SalesPaymentsReportExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithCustomValueBinder
{
    use AuthorizesOwnedRecords, SanitizesExcelFormulas;

    public function __construct(
        private readonly array $filters = []
    ) {}

    public function collection(): Collection
    {
        return $this->query()
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'رقم البيع',
            'العميل',
            'الشركة',
            'عرض السعر',
            'الموظف',
            'الخدمات',
            'Subtotal',
            'VAT',
            'Total',
            'Paid',
            'Remaining',
            'حالة البيع',
            'طريقة الدفع الأساسية',
            'عدد الدفعات',
            'طرق الدفع المستخدمة',
            'تاريخ البيع',
            'آخر تاريخ دفع',
            'ملاحظات',
        ];
    }

    public function map($sale): array
    {
        $filteredPayments = $this->filteredPayments($sale);

        $paidAmount = (float) $filteredPayments->sum('amount');
        $remainingAmount = max((float) $sale->total - $paidAmount, 0);

        $services = $sale->items
            ->map(fn($item) => ($item->service?->name ?? '-') . ' × ' . $item->quantity)
            ->implode(' | ');

        $paymentMethods = $filteredPayments
            ->map(fn($payment) => $payment->payment_method_label)
            ->unique()
            ->implode(' | ');

        $lastPaymentDate = $filteredPayments
            ->sortByDesc('paid_at')
            ->first()
            ?->paid_at
            ?->format('Y-m-d');

        return [
            $sale->id,
            $sale->client?->name,
            $sale->client?->company,
            $sale->quotation?->quotation_number,
            $sale->user?->name,
            $services,
            (float) $sale->subtotal,
            (float) $sale->vat,
            (float) $sale->total,
            $paidAmount,
            $remainingAmount,
            $sale->status_label,
            $sale->payment_method_label,
            $sale->payments_count,
            $paymentMethods,
            $sale->sold_at?->format('Y-m-d'),
            $lastPaymentDate,
            $sale->notes,
        ];
    }
public function bindValue(Cell $cell, $value): bool
{
    if ($this->looksLikeFormula($value)) {
        $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
        return true;
    }

    return parent::bindValue($cell, $value);
}

private function filteredPayments($sale): Collection
{
    return $sale->payments->filter(function ($payment) {
        if (($this->filters['payment_method'] ?? null) && $payment->payment_method !== $this->filters['payment_method']) {
            return false;
        }

        if (($this->filters['paid_from'] ?? null) && $payment->paid_at?->lt(Carbon::parse($this->filters['paid_from'])->startOfDay())) {
            return false;
        }

        if (($this->filters['paid_to'] ?? null) && $payment->paid_at?->gt(Carbon::parse($this->filters['paid_to'])->endOfDay())) {
            return false;
        }

        return true;
    });
}
    private function query()
    {
        $query = Sale::query()
            ->with([
                'client',
                'user',
                'quotation',
                'items.service',
                'payments',
            ])
            ->withCount(['items', 'payments']);

        $this->applyOwnedRecordScope($query, 'sales.view_all', 'user_id');

        return $query
            ->when($this->filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('client', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%')
                            ->orWhere('company', 'like', '%' . $search . '%')
                            ->orWhere('mobile', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%');
                    })
                        ->orWhereHas('quotation', function ($query) use ($search) {
                            $query->where('quotation_number', 'like', '%' . $search . '%');
                        })
                        ->orWhere('notes', 'like', '%' . $search . '%');
                });
            })
            ->when($this->filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->when($this->filters['client_id'] ?? null, fn($query, $clientId) => $query->where('client_id', $clientId))
            ->when($this->filters['user_id'] ?? null, fn($query, $userId) => $query->where('user_id', $userId))
            ->when($this->filters['service_id'] ?? null, function ($query, $serviceId) {
                $query->whereHas('items', function ($query) use ($serviceId) {
                    $query->where('service_id', $serviceId);
                });
            })
            ->when($this->filters['payment_method'] ?? null, function ($query, $paymentMethod) {
                $query->where(function ($query) use ($paymentMethod) {
                    $query->where('payment_method', $paymentMethod)
                        ->orWhereHas('payments', function ($query) use ($paymentMethod) {
                            $query->where('payment_method', $paymentMethod);
                        });
                });
            })
            ->when($this->filters['sold_from'] ?? null, function ($query, $date) {
                $query->where('sold_at', '>=', Carbon::parse($date)->startOfDay());
            })
            ->when($this->filters['sold_to'] ?? null, function ($query, $date) {
                $query->where('sold_at', '<=', Carbon::parse($date)->endOfDay());
            })
            ->when($this->filters['paid_from'] ?? null, function ($query, $date) {
                $query->whereHas('payments', function ($query) use ($date) {
                    $query->where('paid_at', '>=', Carbon::parse($date)->startOfDay());
                });
            })
            ->when($this->filters['paid_to'] ?? null, function ($query, $date) {
                $query->whereHas('payments', function ($query) use ($date) {
                    $query->where('paid_at', '<=', Carbon::parse($date)->endOfDay());
                });
            })
            ->when($this->filters['quotation_state'] ?? null, function ($query, $quotationState) {
                match ($quotationState) {
                    'with' => $query->whereNotNull('quotation_id'),
                    'without' => $query->whereNull('quotation_id'),
                    default => null,
                };
            })
            ->when($this->filters['balance_state'] ?? null, function ($query, $balanceState) {
                match ($balanceState) {
                    'has_remaining' => $query->whereIn('status', ['pending', 'partial']),

                    'fully_paid' => $query->where('status', 'paid'),

                    'no_payments' => $query->doesntHave('payments'),

                    'partial_paid' => $query->where('status', 'partial')
                        ->whereHas('payments'),

                    default => null,
                };
            });
    }
}
