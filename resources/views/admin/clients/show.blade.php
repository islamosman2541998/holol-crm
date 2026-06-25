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
        </div>
    </div>
    <livewire:admin.shared.related-projects
    type="client"
    :id="$client->id"/>
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
