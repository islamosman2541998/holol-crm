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
            <div class="stat-card card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div class="stat-title">إجمالي المبيعات</div>
                    <div class="stat-value fs-4">{{ number_format($salesTotal, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="stat-title">إجمالي المدفوع</div>
                    <div class="stat-value fs-4">{{ number_format($paymentsTotal, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-hourglass-bottom"></i>
                    </div>
                    <div class="stat-title">إجمالي المتبقي</div>
                    <div class="stat-value fs-4">{{ number_format($remainingTotal, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                    <div class="stat-title">مبيعات الشهر</div>
                    <div class="stat-value fs-4">{{ number_format($monthlySalesTotal, 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    {{-- حطي صف الـ Leads هنا --}}
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-person-lines-fill"></i>
                    </div>
                    <div class="stat-title">إجمالي Leads</div>
                    <div class="stat-value">{{ $leadsCount }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="stat-title">Leads جديدة</div>
                    <div class="stat-value">{{ $newLeadsCount }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div class="stat-title">Leads مؤهلة</div>
                    <div class="stat-value">{{ $qualifiedLeadsCount }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="stat-card card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div class="stat-title">تم تحويلها</div>
                    <div class="stat-value">{{ $convertedLeadsCount }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl-4">
            <div class="stat-card card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-chat-left-dots"></i>
                    </div>
                    <div class="stat-title">متابعات Leads اليوم</div>
                    <div class="stat-value">{{ $todayLeadFollowupsCount }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="stat-card card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="stat-title">متابعات Leads متأخرة</div>
                    <div class="stat-value">{{ $overdueLeadFollowupsCount }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <div class="stat-card card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="stat-title">متابعات Leads قيد التنفيذ</div>
                    <div class="stat-value">{{ $pendingLeadFollowupsCount }}</div>
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
        <div class="col-lg-4">
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

        <div class="col-lg-4">
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
                                    {{ str($followup->note)->limit(50) }}
                                </div>

                                <div class="small text-muted mt-1">
                                    {{ $followup->next_followup_at?->format('Y-m-d H:i') ?? '-' }}
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

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">آخر المبيعات</h5>

                    @can('sales.view')
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-sm btn-light">
                            عرض الكل
                        </a>
                    @endcan
                </div>

                <div class="card-body">
                    @forelse ($latestSales as $sale)
                        <div class="dashboard-list-item">
                            <div>
                                <a href="{{ route('admin.sales.show', $sale) }}"
                                    class="fw-semibold text-decoration-none text-dark">
                                    بيع #{{ $sale->id }}
                                </a>

                                <div class="small text-muted">
                                    {{ $sale->client?->name ?? '-' }}
                                </div>

                                <div class="small text-muted mt-1">
                                    إجمالي:
                                    <strong>{{ number_format($sale->total, 2) }}</strong>
                                    -
                                    مدفوع:
                                    <strong>{{ number_format($sale->paid_amount, 2) }}</strong>
                                </div>
                            </div>

                            <span class="badge {{ $sale->status_badge_class }}">
                                {{ $sale->status_label }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            لا توجد مبيعات حتى الآن
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">آخر العملاء المحتملين</h5>

                    @can('leads.view')
                        <a href="{{ route('admin.leads.index') }}" class="btn btn-sm btn-light">
                            عرض الكل
                        </a>
                    @endcan
                </div>

                <div class="card-body">
                    @forelse ($latestLeads as $lead)
                        <div class="dashboard-list-item">
                            <div>
                                <div class="fw-semibold">
                                    {{ $lead->name }}
                                </div>

                                <div class="small text-muted">
                                    {{ $lead->company ?? 'بدون شركة' }}
                                    -
                                    {{ $lead->source ?? 'بدون مصدر' }}
                                </div>

                                <div class="small text-muted mt-1">
                                    المسؤول:
                                    {{ $lead->assignedUser?->name ?? '-' }}
                                </div>
                            </div>

                            <span class="badge {{ $lead->status_badge_class }}">
                                {{ $lead->status_label }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            لا يوجد Leads حتى الآن
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
          <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">آخر متابعات Leads</h5>

                    @can('leads.view')
                        <a href="{{ route('admin.lead-followups.index') }}" class="btn btn-sm btn-light">
                            عرض الكل
                        </a>
                    @endcan
                </div>

                <div class="card-body">
                    @forelse ($latestLeadFollowups as $followup)
                        <div class="dashboard-list-item">
                            <div>
                                <div class="fw-semibold">
                                    {{ $followup->lead?->name ?? 'Lead محذوف' }}
                                </div>

                                <div class="small text-muted">
                                    <i class="bi {{ $followup->type_icon }}"></i>
                                    {{ $followup->type_label }}
                                    -
                                    {{ str($followup->note)->limit(50) }}
                                </div>

                                <div class="small text-muted mt-1">
                                    المسؤول:
                                    {{ $followup->user?->name ?? '-' }}

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
                            لا توجد متابعات Leads حتى الآن
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
@endsection
