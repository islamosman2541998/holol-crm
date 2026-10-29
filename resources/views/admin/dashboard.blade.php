@extends('layouts.admin')

@section('title', 'الرئيسية')
@section('page_title', 'الرئيسية')

@section('content')

    @php
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $user = auth()->user();
        $sections = [];

        $finance = [];

        if ($user->can('sales.view')) {
            $finance[] = ['label' => 'إجمالي المبيعات', 'value' => number_format($salesTotal, 2), 'icon' => 'bi-cash-coin', 'url' => route('admin.reports.sales-payments')];
        }

        if ($user->can('payments.view')) {
            $finance[] = ['label' => 'إجمالي المدفوع', 'value' => number_format($paymentsTotal, 2), 'icon' => 'bi-wallet2', 'url' => route('admin.reports.sales-payments')];
            $finance[] = ['label' => 'إجمالي المتبقي', 'value' => number_format($remainingTotal, 2), 'icon' => 'bi-hourglass-bottom', 'url' => route('admin.reports.sales-payments', ['balanceState' => 'has_remaining'])];
        }

        if ($user->can('sales.view')) {
            $finance[] = ['label' => 'مبيعات الشهر', 'value' => number_format($monthlySalesTotal, 2), 'icon' => 'bi-calendar-month', 'url' => route('admin.reports.sales-payments', ['soldFrom' => $monthStart, 'soldTo' => $monthEnd])];
        }

        if ($finance) {
            $sections[] = ['title' => 'المالية', 'tone' => 'finance', 'link' => ['label' => 'تقرير المبيعات والمدفوعات', 'url' => route('admin.reports.sales-payments')], 'cards' => $finance];
        }

        if ($user->can('clients.view')) {
            $sections[] = ['title' => 'العملاء', 'tone' => 'clients', 'link' => ['label' => 'تقرير العملاء', 'url' => route('admin.reports.clients')], 'cards' => [
                ['label' => 'إجمالي العملاء', 'value' => $clientsCount, 'icon' => 'bi-people', 'url' => route('admin.reports.clients')],
                ['label' => 'عملاء جدد', 'value' => $newClientsCount, 'icon' => 'bi-person-plus', 'url' => route('admin.reports.clients', ['status' => 'new'])],
                ['label' => 'عملاء نشطين', 'value' => $activeClientsCount, 'icon' => 'bi-person-check', 'url' => route('admin.reports.clients', ['status' => 'active'])],
                ['label' => 'عملاء غير نشطين', 'value' => $inactiveClientsCount, 'icon' => 'bi-person-dash', 'url' => route('admin.reports.clients', ['status' => 'inactive'])],
            ]];
        }

        if ($user->can('followups.view')) {
            $sections[] = ['title' => 'متابعات العملاء', 'tone' => 'followups', 'link' => null, 'cards' => [
                ['label' => 'متابعات اليوم', 'value' => $todayFollowupsCount, 'icon' => 'bi-calendar-event', 'url' => route('admin.reports.clients', ['followupState' => 'today'])],
                ['label' => 'متابعات متأخرة', 'value' => $overdueFollowupsCount, 'icon' => 'bi-exclamation-triangle', 'url' => route('admin.reports.clients', ['followupState' => 'overdue']), 'alert' => true],
                ['label' => 'متابعات قيد التنفيذ', 'value' => $pendingFollowupsCount, 'icon' => 'bi-hourglass-split', 'url' => route('admin.reports.clients', ['followupState' => 'pending'])],
                ['label' => 'متابعات مكتملة', 'value' => $doneFollowupsCount, 'icon' => 'bi-check2-all', 'url' => route('admin.followups.index', ['status' => 'done'])],
            ]];
        }

        if ($user->can('leads.view')) {
            $sections[] = ['title' => 'Leads', 'tone' => 'leads', 'link' => ['label' => 'تقرير Leads', 'url' => route('admin.reports.leads')], 'cards' => [
                ['label' => 'إجمالي Leads', 'value' => $leadsCount, 'icon' => 'bi-person-lines-fill', 'url' => route('admin.reports.leads')],
                ['label' => 'Leads جديدة', 'value' => $newLeadsCount, 'icon' => 'bi-person-plus', 'url' => route('admin.reports.leads', ['status' => 'new'])],
                ['label' => 'Leads مؤهلة', 'value' => $qualifiedLeadsCount, 'icon' => 'bi-check2-circle', 'url' => route('admin.reports.leads', ['status' => 'qualified'])],
                ['label' => 'تم تحويلها', 'value' => $convertedLeadsCount, 'icon' => 'bi-arrow-repeat', 'url' => route('admin.reports.leads', ['conversionState' => 'converted'])],
            ]];

            $sections[] = ['title' => 'متابعات Leads', 'tone' => 'followups', 'link' => null, 'cards' => [
                ['label' => 'متابعات Leads اليوم', 'value' => $todayLeadFollowupsCount, 'icon' => 'bi-chat-left-dots', 'url' => route('admin.reports.leads', ['followupState' => 'today'])],
                ['label' => 'متابعات Leads متأخرة', 'value' => $overdueLeadFollowupsCount, 'icon' => 'bi-exclamation-triangle', 'url' => route('admin.reports.leads', ['followupState' => 'overdue']), 'alert' => true],
                ['label' => 'متابعات Leads قيد التنفيذ', 'value' => $pendingLeadFollowupsCount, 'icon' => 'bi-hourglass-split', 'url' => route('admin.reports.leads', ['followupState' => 'pending'])],
                ['label' => 'متابعات Leads مكتملة', 'value' => $doneLeadFollowupsCount, 'icon' => 'bi-check2-all', 'url' => route('admin.lead-followups.index', ['status' => 'done'])],
            ]];
        }

        if ($user->can('tasks.view')) {
            $sections[] = ['title' => 'المهام', 'tone' => 'tasks', 'link' => ['label' => 'كل المهام', 'url' => route('admin.tasks.index')], 'cards' => [
                ['label' => 'مهام جديدة', 'value' => $totalTasksCount ?? 0, 'icon' => 'bi-list-task', 'url' => route('admin.tasks.index')],
                ['label' => 'مهام اليوم', 'value' => $todayTasksCount ?? 0, 'icon' => 'bi-calendar-check', 'url' => route('admin.tasks.index', ['dateFilter' => 'today'])],
                ['label' => 'مهام متأخرة', 'value' => $overdueTasksCount ?? 0, 'icon' => 'bi-exclamation-triangle', 'url' => route('admin.tasks.index', ['dateFilter' => 'overdue']), 'alert' => true],
                ['label' => 'قيد التنفيذ', 'value' => $inProgressTasksCount ?? 0, 'icon' => 'bi-hourglass-split', 'url' => route('admin.tasks.index', ['status' => 'in_progress'])],
                ['label' => 'في المراجعة', 'value' => $reviewTasksCount ?? 0, 'icon' => 'bi-search', 'url' => route('admin.tasks.index', ['status' => 'review'])],
            ]];
        }

        if ($user->can('projects.view')) {
            $sections[] = ['title' => 'المشاريع', 'tone' => 'projects', 'link' => ['label' => 'كل المشاريع', 'url' => route('admin.projects.index')], 'cards' => [
                ['label' => 'مشاريع نشطة', 'value' => $activeProjectsCount ?? 0, 'icon' => 'bi-kanban', 'url' => route('admin.projects.index', ['statusGroup' => 'active'])],
                ['label' => 'قيد التنفيذ', 'value' => $inProgressProjectsCount ?? 0, 'icon' => 'bi-hourglass-split', 'url' => route('admin.projects.index', ['status' => 'in_progress'])],
                ['label' => 'مشاريع متأخرة', 'value' => $overdueProjectsCount ?? 0, 'icon' => 'bi-exclamation-triangle', 'url' => route('admin.projects.index', ['dateFilter' => 'overdue']), 'alert' => true],
                ['label' => 'مشاريع مكتملة', 'value' => $completedProjectsCount ?? 0, 'icon' => 'bi-check2-circle', 'url' => route('admin.projects.index', ['status' => 'completed'])],
            ]];
        }
    @endphp


    @php
        $greeting = now()->hour < 12 ? 'صباح الخير' : 'مساء الخير';
        $agenda = [];

        if ($user->can('tasks.view')) {
            $agenda[] = ['count' => $todayTasksCount ?? 0, 'label' => 'مهام اليوم', 'url' => route('admin.tasks.index', ['dateFilter' => 'today'])];
            $agenda[] = ['count' => $overdueTasksCount ?? 0, 'label' => 'مهام متأخرة', 'url' => route('admin.tasks.index', ['dateFilter' => 'overdue']), 'alert' => true];
        }

        if ($user->can('followups.view')) {
            $agenda[] = ['count' => $todayFollowupsCount, 'label' => 'متابعات اليوم', 'url' => route('admin.reports.clients', ['followupState' => 'today'])];
        }
    @endphp

    <section class="dash-hero">
        <div class="dash-hero-text">
            <h1 class="dash-hero-title">{{ $greeting }}، {{ $user->name }}</h1>
            <p class="dash-hero-sub">ملخص أداء {{ setting('general.company_name', 'Hulul') }} ومتابعة أعمالك لليوم.</p>

            @if ($agenda)
                <div class="dash-hero-agenda">
                    @foreach ($agenda as $item)
                        <a href="{{ $item['url'] }}"
                           class="dash-hero-chip {{ ! empty($item['alert']) && $item['count'] > 0 ? 'is-alert' : '' }}">
                            <strong>{{ $item['count'] }}</strong>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="dash-hero-actions">
            @can('tasks.create')
                <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i>
                    مهمة جديدة
                </a>
            @endcan

            @can('clients.create')
                <a href="{{ route('admin.clients.create') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-person-plus"></i>
                    عميل جديد
                </a>
            @endcan

            @can('leads.create')
                <a href="{{ route('admin.leads.create') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-person-lines-fill"></i>
                    Lead جديد
                </a>
            @endcan
        </div>
    </section>

    @foreach ($sections as $section)
        <section class="stat-section stat-tone-{{ $section['tone'] }}">
            <header class="stat-section-head">
                <h2 class="stat-section-title">{{ $section['title'] }}</h2>

                @if ($section['link'])
                    <a href="{{ $section['link']['url'] }}" class="stat-section-link">
                        {{ $section['link']['label'] }}
                        <i class="bi bi-chevron-left"></i>
                    </a>
                @endif
            </header>

            <div class="row g-4">
                @foreach ($section['cards'] as $card)
                    <div class="col-12 col-sm-6 col-xl-3">
                        <a href="{{ $card['url'] }}"
                           class="stat-card card border-0 shadow-sm stat-card-link {{ ! empty($card['alert']) ? 'is-alert' : '' }} {{ ! empty($card['alert']) && (float) $card['value'] > 0 ? 'has-value' : '' }}">
                            <div class="card-body">
                                <div class="stat-icon">
                                    <i class="bi {{ $card['icon'] }}"></i>
                                </div>
                                <div class="stat-title">{{ $card['label'] }}</div>
                                <div class="stat-value">{{ $card['value'] }}</div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach

    <div class="dash">
        <header class="dash-section-head">
            <h2 class="dash-section-title">آخر النشاطات</h2>
        </header>

        <div class="row g-3">
            <div class="col-12 col-xl-6">
                <section class="dash-panel dash-list-panel">
                    <header class="dash-panel-head">
                        <h3 class="dash-panel-title">آخر المهام</h3>
                        @can('tasks.view')
                            <a href="{{ route('admin.tasks.index') }}" class="dash-link">عرض الكل <i class="bi bi-chevron-left"></i></a>
                        @endcan
                    </header>

                    <div class="dash-list">
                        @forelse ($latestTasks ?? [] as $task)
                            <div class="dash-row">
                                <div class="dash-row-main">
                                    <a href="{{ route('admin.tasks.show', $task) }}" class="dash-row-title">{{ $task->title }}</a>
                                    <div class="dash-row-meta">
                                        <span><i class="bi bi-person"></i> {{ $task->assignedMembers->pluck('name')->implode('، ') ?: '-' }}</span>
                                        @if ($task->client)
                                            <span>عميل: {{ $task->client->name }}</span>
                                        @elseif ($task->lead)
                                            <span>Lead: {{ $task->lead->name }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="dash-row-aside">
                                    <span class="soft-badge {{ $task->status_badge_class }}">{{ $task->status_label }}</span>
                                    <span class="dash-row-date {{ $task->is_overdue ? 'is-alert' : '' }}">
                                        {{ $task->due_at?->format('Y-m-d') ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="dash-empty">لا توجد مهام حتى الآن</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="col-12 col-xl-6">
                <section class="dash-panel dash-list-panel">
                    <header class="dash-panel-head">
                        <h3 class="dash-panel-title">آخر المشاريع</h3>
                        @can('projects.view')
                            <a href="{{ route('admin.projects.index') }}" class="dash-link">عرض الكل <i class="bi bi-chevron-left"></i></a>
                        @endcan
                    </header>

                    <div class="dash-list">
                        @forelse ($latestProjects ?? [] as $project)
                            <div class="dash-row">
                                <div class="dash-row-main">
                                    <a href="{{ route('admin.projects.show', $project) }}" class="dash-row-title">{{ $project->name }}</a>
                                    <div class="dash-row-meta">
                                        <span>{{ $project->client?->name ?? '-' }}</span>
                                        <span>المهام المفتوحة {{ $project->open_tasks_count }} / {{ $project->tasks_count }}</span>
                                    </div>
                                    <div class="dash-row-progress">
                                        <div class="dash-progress dash-progress-sm">
                                            <span style="width: {{ $project->progress_percentage }}%"></span>
                                        </div>
                                        <span>{{ $project->progress_percentage }}%</span>
                                    </div>
                                </div>

                                <div class="dash-row-aside">
                                    <span class="soft-badge {{ $project->status_badge_class }}">{{ $project->status_label }}</span>
                                    <span class="dash-row-date {{ $project->is_overdue ? 'is-alert' : '' }}">
                                        {{ $project->due_date?->format('Y-m-d') ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="dash-empty">لا توجد مشاريع حتى الآن</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="col-12 col-lg-6 col-xl-4">
                <section class="dash-panel dash-list-panel">
                    <header class="dash-panel-head">
                        <h3 class="dash-panel-title">آخر المبيعات</h3>
                        @can('sales.view')
                            <a href="{{ route('admin.sales.index') }}" class="dash-link">عرض الكل <i class="bi bi-chevron-left"></i></a>
                        @endcan
                    </header>

                    <div class="dash-list">
                        @forelse ($latestSales as $sale)
                            <div class="dash-row">
                                <div class="dash-row-main">
                                    <a href="{{ route('admin.sales.show', $sale) }}" class="dash-row-title">بيع #{{ $sale->id }}</a>
                                    <div class="dash-row-meta">
                                        <span>{{ $sale->client?->name ?? '-' }}</span>
                                    </div>
                                    <div class="dash-row-meta">
                                        <span>الإجمالي <strong>{{ number_format($sale->total, 2) }}</strong></span>
                                        <span>المدفوع <strong>{{ number_format($sale->paid_amount, 2) }}</strong></span>
                                    </div>
                                </div>

                                <div class="dash-row-aside">
                                    <span class="soft-badge {{ $sale->status_badge_class }}">{{ $sale->status_label }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="dash-empty">لا توجد مبيعات حتى الآن</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="col-12 col-lg-6 col-xl-4">
                <section class="dash-panel dash-list-panel">
                    <header class="dash-panel-head">
                        <h3 class="dash-panel-title">آخر العملاء</h3>
                        @can('clients.view')
                            <a href="{{ route('admin.clients.index') }}" class="dash-link">عرض الكل <i class="bi bi-chevron-left"></i></a>
                        @endcan
                    </header>

                    <div class="dash-list">
                        @forelse ($latestClients as $client)
                            <div class="dash-row">
                                <div class="dash-row-main">
                                    <a href="{{ route('admin.clients.show', $client) }}" class="dash-row-title">{{ $client->name }}</a>
                                    <div class="dash-row-meta">
                                        <span>{{ $client->company ?? 'بدون شركة' }}</span>
                                    </div>
                                </div>

                                <div class="dash-row-aside">
                                    <span class="soft-badge {{ $client->status_badge_class }}">{{ $client->status_label }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="dash-empty">لا يوجد عملاء حتى الآن</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="col-12 col-lg-6 col-xl-4">
                <section class="dash-panel dash-list-panel">
                    <header class="dash-panel-head">
                        <h3 class="dash-panel-title">آخر Leads</h3>
                        @can('leads.view')
                            <a href="{{ route('admin.leads.index') }}" class="dash-link">عرض الكل <i class="bi bi-chevron-left"></i></a>
                        @endcan
                    </header>

                    <div class="dash-list">
                        @forelse ($latestLeads as $lead)
                            <div class="dash-row">
                                <div class="dash-row-main">
                                    <span class="dash-row-title">{{ $lead->name }}</span>
                                    <div class="dash-row-meta">
                                        <span>{{ $lead->company ?? 'بدون شركة' }}</span>
                                        <span>{{ $lead->source ?? 'بدون مصدر' }}</span>
                                    </div>
                                    <div class="dash-row-meta">
                                        <span><i class="bi bi-person"></i> {{ $lead->assignedUser?->name ?? '-' }}</span>
                                    </div>
                                </div>

                                <div class="dash-row-aside">
                                    <span class="soft-badge {{ $lead->status_badge_class }}">{{ $lead->status_label }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="dash-empty">لا يوجد Leads حتى الآن</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="col-12 col-lg-6">
                <section class="dash-panel dash-list-panel">
                    <header class="dash-panel-head">
                        <h3 class="dash-panel-title">آخر المتابعات</h3>
                        @can('followups.view')
                            <a href="{{ route('admin.followups.index') }}" class="dash-link">عرض الكل <i class="bi bi-chevron-left"></i></a>
                        @endcan
                    </header>

                    <div class="dash-list">
                        @forelse ($latestFollowups as $followup)
                            <div class="dash-row">
                                <div class="dash-row-main">
                                    <span class="dash-row-title">{{ $followup->client?->name ?? 'عميل محذوف' }}</span>
                                    <div class="dash-row-note">{{ str($followup->note)->limit(70) }}</div>
                                </div>

                                <div class="dash-row-aside">
                                    <span class="soft-badge {{ $followup->status_badge_class }}">{{ $followup->status_label }}</span>
                                    <span class="dash-row-date">{{ $followup->next_followup_at?->format('Y-m-d H:i') ?? '-' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="dash-empty">لا توجد متابعات حتى الآن</div>
                        @endforelse
                    </div>
                </section>
            </div>

            <div class="col-12 col-lg-6">
                <section class="dash-panel dash-list-panel">
                    <header class="dash-panel-head">
                        <h3 class="dash-panel-title">آخر متابعات Leads</h3>
                        @can('leads.view')
                            <a href="{{ route('admin.lead-followups.index') }}" class="dash-link">عرض الكل <i class="bi bi-chevron-left"></i></a>
                        @endcan
                    </header>

                    <div class="dash-list">
                        @forelse ($latestLeadFollowups as $followup)
                            <div class="dash-row">
                                <div class="dash-row-main">
                                    <span class="dash-row-title">{{ $followup->lead?->name ?? 'Lead محذوف' }}</span>
                                    <div class="dash-row-meta">
                                        <span><i class="bi {{ $followup->type_icon }}"></i> {{ $followup->type_label }}</span>
                                        <span><i class="bi bi-person"></i> {{ $followup->user?->name ?? '-' }}</span>
                                    </div>
                                    <div class="dash-row-note">{{ str($followup->note)->limit(70) }}</div>
                                </div>

                                <div class="dash-row-aside">
                                    <span class="soft-badge {{ $followup->status_badge_class }}">{{ $followup->status_label }}</span>
                                    @if ($followup->next_followup_at)
                                        <span class="dash-row-date">{{ $followup->next_followup_at->format('Y-m-d H:i') }}</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="dash-empty">لا توجد متابعات Leads حتى الآن</div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>

    @if ($showTasksPopup)
        @php
            $popupSections = collect([
                ['key' => 'assigned', 'title' => 'مهام جديدة اتسندت لك', 'icon' => 'bi-person-plus', 'tasks' => $popupAssignedTasks],
                ['key' => 'overdue', 'title' => 'مهام متأخرة', 'icon' => 'bi-exclamation-triangle', 'tasks' => $popupOverdueTasks],
                ['key' => 'today', 'title' => 'مهام اليوم', 'icon' => 'bi-calendar-check', 'tasks' => $popupTodayTasks],
            ]);
            $popupTotal = $popupSections->sum(fn ($section) => $section['tasks']->count());
        @endphp

        <div class="modal fade tasks-popup" id="tasksPopupModal" tabindex="-1" aria-labelledby="tasksPopupTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable tasks-popup-dialog">
                <div class="modal-content">
                    <div class="tasks-popup-header">
                        <div class="tasks-popup-icon">
                            <i class="bi bi-bell"></i>
                        </div>

                        <div class="tasks-popup-heading">
                            <h5 class="tasks-popup-title" id="tasksPopupTitle">تنبيهات المهام</h5>
                            <p class="tasks-popup-subtitle">عندك {{ $popupTotal }} {{ $popupTotal === 1 ? 'مهمة محتاجة' : 'مهام محتاجة' }} انتباهك</p>
                        </div>

                        <button type="button" class="tasks-popup-close" data-bs-dismiss="modal" aria-label="إغلاق">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="tasks-popup-stats">
                        @foreach ($popupSections as $section)
                            <div class="tasks-popup-stat tasks-popup-stat--{{ $section['key'] }} {{ $section['tasks']->isEmpty() ? 'is-empty' : '' }}">
                                <span class="tasks-popup-stat-value">{{ $section['tasks']->count() }}</span>
                                <span class="tasks-popup-stat-label">{{ $section['title'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="modal-body tasks-popup-body">
                        @foreach ($popupSections->filter(fn ($section) => $section['tasks']->isNotEmpty()) as $section)
                            <section class="tasks-popup-section tasks-popup-section--{{ $section['key'] }}">
                                <div class="tasks-popup-section-head">
                                    <i class="bi {{ $section['icon'] }}"></i>
                                    <span>{{ $section['title'] }}</span>
                                    <span class="tasks-popup-section-count">{{ $section['tasks']->count() }}</span>
                                </div>

                                <div class="tasks-popup-list">
                                    @foreach ($section['tasks'] as $task)
                                        @include('admin.tasks.partials.popup-item', ['task' => $task, 'variant' => $section['key']])
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>

                    <div class="tasks-popup-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">لاحقاً</button>
                        <a href="{{ route('admin.tasks.index') }}" class="btn btn-primary">
                            عرض كل المهام
                            <i class="bi bi-arrow-left"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var modalEl = document.getElementById('tasksPopupModal');

                    if (modalEl) {
                        new bootstrap.Modal(modalEl).show();
                    }
                });
            </script>
        @endpush
    @endif
@endsection