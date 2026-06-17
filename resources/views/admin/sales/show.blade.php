@extends('layouts.admin')

@section('title', 'تفاصيل عملية البيع')
@section('page_title', 'تفاصيل عملية البيع')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">عملية بيع #{{ $sale->id }}</h4>
        <div class="text-muted">
            {{ $sale->client?->name ?? '-' }}
            -
            {{ $sale->sold_at?->format('Y-m-d') ?? '-' }}
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('admin.sales.index') }}" class="btn btn-light">
            <i class="bi bi-arrow-right"></i>
            رجوع
        </a>

        @can('sales.edit')
            <a href="{{ route('admin.sales.edit', $sale) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i>
                تعديل
            </a>
        @endcan
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">بيانات البيع</h5>
            </div>

            <div class="card-body">
                <div class="client-info-item">
                    <span>العميل</span>
                    <strong>{{ $sale->client?->name ?? '-' }}</strong>
                </div>

                <div class="client-info-item">
                    <span>الشركة</span>
                    <strong>{{ $sale->client?->company ?? '-' }}</strong>
                </div>

                <div class="client-info-item">
                    <span>الموظف</span>
                    <strong>{{ $sale->user?->name ?? '-' }}</strong>
                </div>

                <div class="client-info-item">
                    <span>الحالة</span>
                    <strong>
                        <span class="badge {{ $sale->status_badge_class }}">
                            {{ $sale->status_label }}
                        </span>
                    </strong>
                </div>

                <div class="client-info-item">
                    <span>طريقة الدفع</span>
                    <strong>{{ $sale->payment_method_label }}</strong>
                </div>

                <div class="client-info-item">
                    <span>تاريخ البيع</span>
                    <strong>{{ $sale->sold_at?->format('Y-m-d') ?? '-' }}</strong>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">ملخص الحساب</h5>
            </div>

            <div class="card-body">
                <div class="client-info-item">
                    <span>Subtotal</span>
                    <strong>{{ number_format($sale->subtotal, 2) }}</strong>
                </div>

                <div class="client-info-item">
                    <span>VAT</span>
                    <strong>{{ number_format($sale->vat, 2) }}</strong>
                </div>

                <div class="client-info-item">
                    <span>Total</span>
                    <strong>{{ number_format($sale->total, 2) }}</strong>
                </div>

                <div class="client-info-item">
                    <span>Paid</span>
                    <strong class="text-success">{{ number_format($sale->paid_amount, 2) }}</strong>
                </div>

                <div class="client-info-item">
                    <span>Remaining</span>
                    <strong class="text-danger">{{ number_format($sale->remaining_amount, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">الخدمات المباعة</h5>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>الخدمة</th>
                                <th>الكمية</th>
                                <th>سعر الوحدة</th>
                                <th>الإجمالي</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($sale->items as $item)
                                <tr>
                                    <td>{{ $item->service?->name ?? 'خدمة محذوفة' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ number_format($item->unit_price, 2) }}</td>
                                    <td>{{ number_format($item->total, 2) }}</td>
                                    <td>{{ $item->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($sale->notes)
                    <hr>
                    <h6>ملاحظات البيع</h6>
                    <p class="text-muted mb-0">{{ $sale->notes }}</p>
                @endif
            </div>
        </div>

        <livewire:admin.sales.sale-payments :sale="$sale" />
    </div>
</div>

@endsection