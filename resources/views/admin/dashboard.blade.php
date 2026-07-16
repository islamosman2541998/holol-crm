@extends('layouts.admin')

@section('title', 'الرئيسية')
@section('page_title', 'الرئيسية')

@section('content')

    @php
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
    @endphp

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.reports.clients') }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-title">إجمالي العملاء</div>
                    <div class="stat-value">{{ $clientsCount }}</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.reports.clients', ['status' => 'new']) }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="stat-title">عملاء جدد</div>
                    <div class="stat-value">{{ $newClientsCount }}</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.reports.clients', ['followupState' => 'today']) }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-calendar-event"></i>
                    </div>
                    <div class="stat-title">متابعات اليوم</div>
                    <div class="stat-value">{{ $todayFollowupsCount }}</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.reports.clients', ['followupState' => 'overdue']) }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="stat-title">متابعات متأخرة</div>
                    <div class="stat-value">{{ $overdueFollowupsCount }}</div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.reports.sales-payments') }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div class="stat-title">إجمالي المبيعات</div>
                    <div class="stat-value fs-4">{{ number_format($salesTotal, 2) }}</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.reports.sales-payments') }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="stat-title">إجمالي المدفوع</div>
                    <div class="stat-value fs-4">{{ number_format($paymentsTotal, 2) }}</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.reports.sales-payments', ['balanceState' => 'has_remaining']) }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-hourglass-bottom"></i>
                    </div>
                    <div class="stat-title">إجمالي المتبقي</div>
                    <div class="stat-value fs-4">{{ number_format($remainingTotal, 2) }}</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.reports.sales-payments', [
                    'soldFrom' => $monthStart,
                    'soldTo' => $monthEnd,
                ]) }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                    <div class="stat-title">مبيعات الشهر</div>
                    <div class="stat-value fs-4">{{ number_format($monthlySalesTotal, 2) }}</div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.reports.leads') }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bi bi-person-lines-fill"></i>
                    </div>
                    <div class="stat-title">إجمالي Leads</div>
                    <div class="stat-value">{{ $leadsCount }}</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.reports.leads', ['status' => 'new']) }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-person-plus"></i>
                    </div>
                    <div class="stat-title">Leads جديدة</div>
                    <div class="stat-value">{{ $newLeadsCount }}</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.reports.leads', ['status' => 'qualified']) }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <div class="stat-title">Leads مؤهلة</div>
                    <div class="stat-value">{{ $qualifiedLeadsCount }}</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.reports.leads', ['conversionState' => 'converted']) }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div class="stat-title">تم تحويلها</div>
                    <div class="stat-value">{{ $convertedLeadsCount }}</div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl-4">
            <a href="{{ route('admin.reports.leads', ['followupState' => 'today']) }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bi bi-chat-left-dots"></i>
                    </div>
                    <div class="stat-title">متابعات Leads اليوم</div>
                    <div class="stat-value">{{ $todayLeadFollowupsCount }}</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <a href="{{ route('admin.reports.leads', ['followupState' => 'overdue']) }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div class="stat-title">متابعات Leads متأخرة</div>
                    <div class="stat-value">{{ $overdueLeadFollowupsCount }}</div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-xl-4">
            <a href="{{ route('admin.reports.leads', ['followupState' => 'pending']) }}"
               class="stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="stat-title">متابعات Leads قيد التنفيذ</div>
                    <div class="stat-value">{{ $pendingLeadFollowupsCount }}</div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.reports.clients', ['status' => 'active']) }}"
               class="small-stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">عملاء نشطين</div>
                        <div class="fw-bold fs-4">{{ $activeClientsCount }}</div>
                    </div>
                    <i class="bi bi-check-circle text-success fs-3"></i>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <a href="{{ route('admin.reports.clients', ['followupState' => 'pending']) }}"
               class="small-stat-card card border-0 shadow-sm stat-card-link">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small">متابعات قيد التنفيذ</div>
                        <div class="fw-bold fs-4">{{ $pendingFollowupsCount }}</div>
                    </div>
                    <i class="bi bi-hourglass-split text-warning fs-3"></i>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <a href="{{ route('admin.tasks.index', ['dateFilter' => 'today']) }}"
               class="card border-0 shadow-sm small-stat-card stat-card-link">
                <div class="card-body">
                    <div class="text-muted small mb-1">مهام اليوم</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $todayTasksCount ?? 0 }}</h4>
                        <i class="bi bi-calendar-check fs-3 text-primary"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('admin.tasks.index', ['dateFilter' => 'overdue']) }}"
               class="card border-0 shadow-sm small-stat-card stat-card-link">
                <div class="card-body">
                    <div class="text-muted small mb-1">مهام متأخرة</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $overdueTasksCount ?? 0 }}</h4>
                        <i class="bi bi-exclamation-triangle fs-3 text-danger"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('admin.tasks.index', ['status' => 'in_progress']) }}"
               class="card border-0 shadow-sm small-stat-card stat-card-link">
                <div class="card-body">
                    <div class="text-muted small mb-1">قيد التنفيذ</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $inProgressTasksCount ?? 0 }}</h4>
                        <i class="bi bi-hourglass-split fs-3 text-info"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('admin.tasks.index', ['status' => 'review']) }}"
               class="card border-0 shadow-sm small-stat-card stat-card-link">
                <div class="card-body">
                    <div class="text-muted small mb-1">في المراجعة</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $reviewTasksCount ?? 0 }}</h4>
                        <i class="bi bi-search fs-3 text-warning"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <a href="{{ route('admin.projects.index', ['statusGroup' => 'active']) }}"
               class="card border-0 shadow-sm small-stat-card stat-card-link">
                <div class="card-body">
                    <div class="text-muted small mb-1">مشاريع نشطة</div>

                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $activeProjectsCount ?? 0 }}</h4>
                        <i class="bi bi-kanban fs-3 text-primary"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('admin.projects.index', ['status' => 'in_progress']) }}"
               class="card border-0 shadow-sm small-stat-card stat-card-link">
                <div class="card-body">
                    <div class="text-muted small mb-1">قيد التنفيذ</div>

                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $inProgressProjectsCount ?? 0 }}</h4>
                        <i class="bi bi-hourglass-split fs-3 text-info"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('admin.projects.index', ['dateFilter' => 'overdue']) }}"
               class="card border-0 shadow-sm small-stat-card stat-card-link">
                <div class="card-body">
                    <div class="text-muted small mb-1">مشاريع متأخرة</div>

                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $overdueProjectsCount ?? 0 }}</h4>
                        <i class="bi bi-exclamation-triangle fs-3 text-danger"></i>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('admin.projects.index', ['status' => 'completed']) }}"
               class="card border-0 shadow-sm small-stat-card stat-card-link">
                <div class="card-body">
                    <div class="text-muted small mb-1">مشاريع مكتملة</div>

                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $completedProjectsCount ?? 0 }}</h4>
                        <i class="bi bi-check2-circle fs-3 text-success"></i>
                    </div>
                </div>
            </a>
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
                    <h5 class="mb-0">آخر الleads </h5>

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

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">آخر المهام</h5>

                    @can('tasks.view')
                        <a href="{{ route('admin.tasks.index') }}" class="btn btn-sm btn-light">
                            عرض الكل
                        </a>
                    @endcan
                </div>

                <div class="card-body">
                    @forelse ($latestTasks ?? [] as $task)
                        <div class="dashboard-list-item">
                            <div>
                                <div class="fw-semibold">
                                    <a href="{{ route('admin.tasks.show', $task) }}" class="text-decoration-none">
                                        {{ $task->title }}
                                    </a>
                                </div>

                                <div class="small text-muted">
                                    المسؤول:
                                    {{ $task->assignedMember?->name ?? '-' }}

                                    @if ($task->assignedMember?->team)
                                        - {{ $task->assignedMember->team->name }}
                                    @endif
                                </div>

                                <div class="small text-muted">
                                    @if ($task->client)
                                        عميل: {{ $task->client->name }}
                                    @elseif ($task->lead)
                                        Lead: {{ $task->lead->name }}
                                    @else
                                        بدون ربط
                                    @endif
                                </div>
                            </div>

                            <div class="text-end">
                                <div class="mb-1">
                                    <span class="badge {{ $task->status_badge_class }}">
                                        {{ $task->status_label }}
                                    </span>
                                </div>

                                <div class="small text-muted">
                                    التسليم:
                                    {{ $task->due_at?->format('Y-m-d H:i') ?? '-' }}
                                </div>

                                @if ($task->is_overdue)
                                    <div class="small text-danger">
                                        متأخرة
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            لا توجد مهام حتى الآن
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4 mt-3">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">آخر المشاريع</h5>

                    @can('projects.view')
                        <a href="{{ route('admin.projects.index') }}" class="btn btn-sm btn-light">
                            عرض الكل
                        </a>
                    @endcan
                </div>

                <div class="card-body">
                    @forelse ($latestProjects ?? [] as $project)
                        <div class="dashboard-list-item">
                            <div>
                                <div class="fw-semibold">
                                    <a href="{{ route('admin.projects.show', $project) }}" class="text-decoration-none">
                                        {{ $project->name }}
                                    </a>
                                </div>

                                <div class="small text-muted">
                                    العميل:
                                    @if ($project->client)
                                        {{ $project->client->name }}
                                    @else
                                        -
                                    @endif
                                </div>

                                <div class="small text-muted">
                                    الفريق:
                                    {{ $project->team?->name ?? '-' }}

                                    @if ($project->manager)
                                        |
                                        المدير: {{ $project->manager->name }}
                                    @endif
                                </div>

                                <div class="small text-muted">
                                    المهام:
                                    الكل {{ $project->tasks_count }}
                                    /
                                    المفتوحة {{ $project->open_tasks_count }}
                                </div>

                                <div class="mt-2" style="max-width: 240px;">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>الإنجاز</span>
                                        <span>{{ $project->progress_percentage }}%</span>
                                    </div>

                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar"
                                             style="width: {{ $project->progress_percentage }}%;"
                                             aria-valuenow="{{ $project->progress_percentage }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <div class="mb-1">
                                    <span class="badge {{ $project->status_badge_class }}">
                                        {{ $project->status_label }}
                                    </span>
                                </div>

                                <div class="mb-1">
                                    <span class="badge {{ $project->priority_badge_class }}">
                                        {{ $project->priority_label }}
                                    </span>
                                </div>

                                <div class="small text-muted">
                                    التسليم:
                                    {{ $project->due_date?->format('Y-m-d') ?? '-' }}
                                </div>

                                @if ($project->is_overdue)
                                    <div class="small text-danger">
                                        متأخر
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            لا توجد مشاريع حتى الآن
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection