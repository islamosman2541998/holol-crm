@extends('layouts.admin')

@section('title', 'تفاصيل العميل')
@section('page_title', 'تفاصيل العميل')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $client->name }}</h4>
            <div class="text-muted">{{ $client->company ?? 'بدون شركة' }}</div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.clients.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-right"></i>
                رجوع
            </a>

            @can('clients.edit')
                <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-primary">
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
                    <h5 class="mb-0">بيانات العميل</h5>
                </div>

                <div class="card-body">
                    <div class="client-info-item">
                        <span>الحالة</span>
                        <strong>
                            <span class="badge {{ $client->status_badge_class }}">
                                {{ $client->status_label }}
                            </span>
                        </strong>
                    </div>

                    <div class="client-info-item">
                        <span>الموظف المسؤول</span>
                        <strong>{{ $client->assignedUser?->name ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>الموبايل</span>
                        <strong>{{ $client->mobile ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>الهاتف</span>
                        <strong>{{ $client->phone ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>البريد الإلكتروني</span>
                        <strong>{{ $client->email ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>المدينة</span>
                        <strong>{{ $client->city ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>مصدر العميل</span>
                        <strong>{{ $client->source ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>تاريخ الإضافة</span>
                        <strong>{{ $client->created_at->format('Y-m-d') }}</strong>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">ملاحظات</h5>
                </div>

                <div class="card-body">
                    <p class="mb-0 text-muted">
                        {{ $client->notes ?: 'لا توجد ملاحظات' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <livewire:admin.clients.client-followups :client="$client" />

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">عروض الأسعار المفتوحة</h5>
                        <div class="small text-muted mt-1">
                            العروض المفتوحة فقط تظهر هنا ويمكن استخدامها في إنشاء عملية بيع.
                        </div>
                    </div>

                    @can('quotations.create')
                        <a href="{{ route('admin.quotations.create', ['client_id' => $client->id]) }}"
                            class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i>
                            عرض سعر جديد
                        </a>
                    @endcan
                </div>

                <div class="card-body">
                    @forelse ($client->openQuotations as $quotation)
                        <div class="related-project-item">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h6 class="mb-1">
                                        <a href="{{ route('admin.quotations.show', $quotation) }}">
                                            {{ $quotation->quotation_number }}
                                        </a>
                                    </h6>

                                    <div class="small text-muted">
                                        تاريخ العرض:
                                        {{ $quotation->quotation_date?->format('Y-m-d') ?? '-' }}

                                        <span class="mx-2">|</span>

                                        صالح حتى:
                                        {{ $quotation->valid_until?->format('Y-m-d') ?? '-' }}
                                    </div>

                                    <div class="small text-muted mt-1">
                                        الخدمات:
                                        {{ $quotation->items->count() }}

                                        <span class="mx-2">|</span>

                                        الإجمالي:
                                        <strong>{{ number_format($quotation->total, 2) }}</strong>
                                    </div>

                                    @if ($quotation->sale)
                                        <div class="small text-muted mt-1">
                                            مرتبط بعملية بيع:
                                            <a href="{{ route('admin.sales.show', $quotation->sale) }}">
                                                عرض البيع
                                            </a>

                                            <span class="mx-2">|</span>

                                            حالة البيع:
                                            <span class="badge {{ $quotation->sale->status_badge_class }}">
                                                {{ $quotation->sale->status_label }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    <span class="badge {{ $quotation->status_badge_class }}">
                                        {{ $quotation->status_label }}
                                    </span>

                                    <a href="{{ route('admin.quotations.show', $quotation) }}"
                                        class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('admin.quotations.pdf', $quotation) }}"
                                        class="btn btn-sm btn-outline-danger" target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                        PDF
                                    </a>

                                    @if (!$quotation->sale)
                                        @can('sales.create')
                                            <a href="{{ route('admin.sales.create', ['quotation_id' => $quotation->id]) }}"
                                                class="btn btn-sm btn-outline-success">
                                                <i class="bi bi-cash-coin"></i>
                                                بيع
                                            </a>
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            لا توجد عروض أسعار مفتوحة لهذا العميل حاليًا
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <livewire:admin.shared.related-projects type="client" :id="$client->id" />
    <div class="row g-4 mt-1">
        <div class="col-12">
            <livewire:admin.shared.related-tasks type="client" :id="$client->id" />
        </div>
    </div>
    <div class="row g-4 mt-1">
        <div class="col-12">
            <livewire:admin.shared.activity-timeline :subject-type="get_class($client)" :subject-id="$client->id" />

        </div>

    </div>

@endsection
