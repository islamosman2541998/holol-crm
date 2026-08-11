@extends('layouts.admin')

@section('title', 'تفاصيل عرض السعر')
@section('page_title', 'تفاصيل عرض السعر')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                عرض سعر رقم: {{ $quotation->quotation_number }}
            </h4>

            <div class="text-muted">
                @if ($quotation->client)
                    العميل:
                    <a href="{{ route('admin.clients.show', $quotation->client) }}">
                        {{ $quotation->client->name }}
                    </a>
                @elseif ($quotation->lead)
                    Lead:
                    <a href="{{ route('admin.leads.show', $quotation->lead) }}">
                        {{ $quotation->lead->name }}
                    </a>
                @else
                    -
                @endif
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.quotations.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-right"></i>
                رجوع
            </a>

            @can('quotations.edit')
                @if (!in_array($quotation->status, ['closed', 'cancelled']))
                    <a href="{{ route('admin.quotations.edit', $quotation) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i>
                        تعديل
                    </a>
                @endif
            @endcan
            <a href="{{ route('admin.quotations.pdf', $quotation) }}" class="btn btn-outline-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i>
                PDF
            </a>
            @if ($quotation->status === 'open' && !$quotation->sale && $quotation->client)
                @can('sales.create')
                    <a href="{{ route('admin.sales.create', ['quotation_id' => $quotation->id]) }}" class="btn btn-success">
                        <i class="bi bi-cash-coin"></i>
                        إنشاء بيع من العرض
                    </a>
                @endcan
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">بيانات عرض السعر</h5>
                </div>

                <div class="card-body">
                    <div class="client-info-item">
                        <span>رقم العرض</span>
                        <strong>{{ $quotation->quotation_number }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>الحالة</span>
                        <strong>
                            <span class="badge {{ $quotation->status_badge_class }}">
                                {{ $quotation->status_label }}
                            </span>
                        </strong>
                    </div>

                    <div class="client-info-item">
                        <span>{{ $quotation->client ? 'العميل' : 'Lead' }}</span>
                        <strong>
                            @if ($quotation->client)
                                <a href="{{ route('admin.clients.show', $quotation->client) }}">
                                    {{ $quotation->client->name }}
                                </a>

                                <div class="small text-muted mt-1">
                                    {{ $quotation->client->company ?? 'بدون شركة' }}
                                </div>
                            @elseif ($quotation->lead)
                                <a href="{{ route('admin.leads.show', $quotation->lead) }}">
                                    {{ $quotation->lead->name }}
                                </a>

                                <div class="small text-muted mt-1">
                                    {{ $quotation->lead->company ?? 'بدون شركة' }}
                                </div>
                            @else
                                -
                            @endif
                        </strong>
                    </div>

                    <div class="client-info-item">
                        <span>أنشئ بواسطة</span>
                        <strong>{{ $quotation->user?->name ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>تاريخ العرض</span>
                        <strong>{{ $quotation->quotation_date?->format('Y-m-d') ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>صالح حتى</span>
                        <strong>{{ $quotation->valid_until?->format('Y-m-d') ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>تاريخ الفتح</span>
                        <strong>{{ $quotation->opened_at?->format('Y-m-d H:i') ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>تاريخ الإغلاق</span>
                        <strong>{{ $quotation->closed_at?->format('Y-m-d H:i') ?? '-' }}</strong>
                    </div>
                </div>
            </div>

            @can('quotations.change_status')
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">تغيير حالة العرض</h5>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.quotations.change-status', $quotation) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label class="form-label">الحالة الجديدة</label>
                                <select name="status" class="form-select">
                                    <option value="pending" @selected($quotation->status === 'pending')>معلق</option>
                                    <option value="open" @selected($quotation->status === 'open')>مفتوح</option>
                                    <option value="closed" @selected($quotation->status === 'closed')>مغلق</option>
                                    <option value="cancelled" @selected($quotation->status === 'cancelled')>ملغي</option>
                                </select>

                                <div class="form-text">
                                    العرض المفتوح فقط هو الذي يظهر في بروفايل العميل ويمكن استخدامه في البيع.
                                </div>
                            </div>

                            <button class="btn btn-primary w-100">
                                <i class="bi bi-arrow-repeat"></i>
                                تحديث الحالة
                            </button>
                        </form>
                    </div>
                </div>
            @endcan

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">الإجماليات</h5>
                </div>

                <div class="card-body">
                    <div class="client-info-item">
                        <span>Subtotal</span>
                        <strong>{{ number_format($quotation->subtotal, 2) }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>VAT</span>
                        <strong>{{ number_format($quotation->vat, 2) }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>Total</span>
                        <strong class="fs-5">{{ number_format($quotation->total, 2) }}</strong>
                    </div>
                </div>
            </div>

            @if ($quotation->sale)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">عملية البيع المرتبطة</h5>
                    </div>

                    <div class="card-body">
                        <div class="client-info-item">
                            <span>حالة البيع</span>
                            <strong>
                                <span class="badge {{ $quotation->sale->status_badge_class }}">
                                    {{ $quotation->sale->status_label }}
                                </span>
                            </strong>
                        </div>

                        <div class="client-info-item">
                            <span>إجمالي البيع</span>
                            <strong>{{ number_format($quotation->sale->total, 2) }}</strong>
                        </div>

                        <div class="client-info-item">
                            <span>المدفوع</span>
                            <strong>{{ number_format($quotation->sale->paid_amount, 2) }}</strong>
                        </div>

                        <div class="client-info-item">
                            <span>المتبقي</span>
                            <strong>{{ number_format($quotation->sale->remaining_amount, 2) }}</strong>
                        </div>

                        <a href="{{ route('admin.sales.show', $quotation->sale) }}"
                            class="btn btn-outline-dark w-100 mt-2">
                            <i class="bi bi-eye"></i>
                            عرض عملية البيع
                        </a>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">ملاحظات</h5>
                </div>

                <div class="card-body">
                    <p class="text-muted mb-0">
                        {{ $quotation->notes ?: 'لا توجد ملاحظات' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">بنود عرض السعر</h5>
                        <div class="small text-muted mt-1">
                            الخدمات والأسعار التي سيتم نسخها تلقائيًا عند إنشاء عملية بيع من العرض.
                        </div>
                    </div>
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
                                @forelse ($quotation->items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->service?->name ?? '-' }}</strong>
                                            <div class="small text-muted">
                                                {{ $item->service?->code ?? '' }}
                                            </div>
                                        </td>

                                        <td>{{ $item->quantity }}</td>

                                        <td>{{ number_format($item->unit_price, 2) }}</td>

                                        <td>{{ number_format($item->total, 2) }}</td>

                                        <td>{{ $item->notes ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            لا توجد خدمات داخل عرض السعر
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            <tfoot>
                                <tr>
                                    <th colspan="3">Subtotal</th>
                                    <th colspan="2">{{ number_format($quotation->subtotal, 2) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="3">VAT</th>
                                    <th colspan="2">{{ number_format($quotation->vat, 2) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="3">Total</th>
                                    <th colspan="2">{{ number_format($quotation->total, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <livewire:admin.shared.activity-timeline :subject-type="get_class($quotation)" :subject-id="$quotation->id" />
        </div>
    </div>

@endsection
