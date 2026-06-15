@extends('layouts.admin')

@section('title', 'الرئيسية')
@section('page_title', 'الرئيسية')

@section('content')

<div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="stat-card card border-0 shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-primary-subtle text-primary">
                    <i class="bi bi-people"></i>
                </div>
                <div class="stat-title">إجمالي العملاء</div>
                <div class="stat-value">{{ $clientsCount }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="stat-card card border-0 shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-info-subtle text-info">
                    <i class="bi bi-person-plus"></i>
                </div>
                <div class="stat-title">عملاء جدد</div>
                <div class="stat-value">{{ $newClientsCount }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="stat-card card border-0 shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-warning-subtle text-warning">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="stat-title">متابعات اليوم</div>
                <div class="stat-value">{{ $todayFollowupsCount }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="stat-card card border-0 shadow-sm">
            <div class="card-body">
                <div class="stat-icon bg-danger-subtle text-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-title">متابعات متأخرة</div>
                <div class="stat-value">{{ $overdueFollowupsCount }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="small-stat-card card border-0 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">عملاء نشطين</div>
                    <div class="fw-bold fs-4">{{ $activeClientsCount }}</div>
                </div>
                <i class="bi bi-check-circle text-success fs-3"></i>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="small-stat-card card border-0 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">متابعات قيد التنفيذ</div>
                    <div class="fw-bold fs-4">{{ $pendingFollowupsCount }}</div>
                </div>
                <i class="bi bi-hourglass-split text-warning fs-3"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">آخر العملاء</h5>

                @can('clients.view')
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-sm btn-light">
                        عرض الكل
                    </a>
                @endcan
            </div>

            <div class="card-body">
                @forelse ($latestClients as $client)
                    <div class="dashboard-list-item">
                        <div>
                            <a href="{{ route('admin.clients.show', $client) }}"
                               class="fw-semibold text-decoration-none text-dark">
                                {{ $client->name }}
                            </a>

                            <div class="small text-muted">
                                {{ $client->company ?? 'بدون شركة' }}
                                -
                                {{ $client->assignedUser?->name ?? 'بدون مسؤول' }}
                            </div>
                        </div>

                        <span class="badge {{ $client->status_badge_class }}">
                            {{ $client->status_label }}
                        </span>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        لا يوجد عملاء حتى الآن
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">آخر المتابعات</h5>

                @can('followups.view')
                    <a href="{{ route('admin.followups.index') }}" class="btn btn-sm btn-light">
                        عرض الكل
                    </a>
                @endcan
            </div>

            <div class="card-body">
                @forelse ($latestFollowups as $followup)
                    <div class="dashboard-list-item">
                        <div>
                            <div class="fw-semibold">
                                {{ $followup->client?->name ?? 'عميل محذوف' }}
                            </div>

                            <div class="small text-muted">
                                {{ str($followup->note)->limit(60) }}
                            </div>

                            <div class="small text-muted mt-1">
                                بواسطة: {{ $followup->user?->name ?? '-' }}

                                @if ($followup->next_followup_at)
                                    -
                                    {{ $followup->next_followup_at->format('Y-m-d H:i') }}
                                @endif
                            </div>
                        </div>

                        <span class="badge {{ $followup->status_badge_class }}">
                            {{ $followup->status_label }}
                        </span>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        لا توجد متابعات حتى الآن
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection