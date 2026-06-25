@extends('layouts.admin')

@section('title', 'تفاصيل Lead')
@section('page_title', 'تفاصيل Lead')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $lead->name }}</h4>
            <div class="text-muted">
                {{ $lead->company ?? 'بدون شركة' }}
                -
                {{ $lead->source ?? 'بدون مصدر' }}
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.leads.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-right"></i>
                رجوع
            </a>

            @can('leads.convert')
                @if ($lead->status !== 'converted')
                    <form action="{{ route('admin.leads.convert', $lead) }}" method="POST" class="d-inline">
                        @csrf

                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-arrow-repeat"></i>
                            تحويل إلى عميل
                        </button>
                    </form>
                @endif
            @endcan

            @can('leads.edit')
                <a href="{{ route('admin.leads.edit', $lead) }}" class="btn btn-primary">
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
                    <h5 class="mb-0">بيانات الـ Lead</h5>
                </div>

                <div class="card-body">
                    <div class="client-info-item">
                        <span>الحالة</span>
                        <strong>
                            <span class="badge {{ $lead->status_badge_class }}">
                                {{ $lead->status_label }}
                            </span>
                        </strong>
                    </div>

                    <div class="client-info-item">
                        <span>الموظف المسؤول</span>
                        <strong>{{ $lead->assignedUser?->name ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>اسم الشركة</span>
                        <strong>{{ $lead->company ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>المصدر</span>
                        <strong>{{ $lead->source ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>المدينة</span>
                        <strong>{{ $lead->city ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>تاريخ الإضافة</span>
                        <strong>{{ $lead->created_at->format('Y-m-d') }}</strong>
                    </div>

                    @if ($lead->convertedClient)
                        <div class="client-info-item">
                            <span>تم تحويله إلى</span>
                            <strong>
                                <a href="{{ route('admin.clients.show', $lead->convertedClient) }}">
                                    {{ $lead->convertedClient->name }}
                                </a>
                            </strong>
                        </div>

                        <div class="client-info-item">
                            <span>تاريخ التحويل</span>
                            <strong>{{ $lead->converted_at?->format('Y-m-d H:i') ?? '-' }}</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">بيانات التواصل</h5>
                </div>

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="lead-contact-box">
                                <div class="text-muted small">الموبايل</div>
                                <div class="fw-bold">{{ $lead->mobile ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="lead-contact-box">
                                <div class="text-muted small">الهاتف</div>
                                <div class="fw-bold">{{ $lead->phone ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="lead-contact-box">
                                <div class="text-muted small">البريد الإلكتروني</div>
                                <div class="fw-bold">{{ $lead->email ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="lead-contact-box">
                                <div class="text-muted small">الحالة الحالية</div>
                                <div class="fw-bold">{{ $lead->status_label }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">ملاحظات</h5>
                </div>

                <div class="card-body">
                    <p class="mb-0 text-muted">
                        {{ $lead->notes ?: 'لا توجد ملاحظات' }}
                    </p>
                </div>
            </div>
            <div class="mt-4">
                <livewire:admin.leads.lead-followups :lead="$lead" />
            </div>
        </div>
    </div>
    <div class="row g-4 mt-1">
        <div class="col-12">
            <livewire:admin.shared.related-tasks type="lead" :id="$lead->id" />
        </div>
    </div>
    <div class="row g-4 mt-1">
        <div class="col-12">
            <livewire:admin.shared.activity-timeline :subject-type="get_class($lead)" :subject-id="$lead->id" />
        </div>
    </div>
@endsection
