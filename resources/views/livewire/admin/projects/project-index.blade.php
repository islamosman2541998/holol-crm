<div>
    @php
        $hasFilters = $search !== '' || $status !== '' || $priority !== '' || $teamId !== '' || $managerMemberId !== '' || $dateFilter !== '' || $statusGroup !== '';
    @endphp

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">قائمة المشاريع</h2>
                <p class="list-card-sub">{{ number_format($projects->total()) }} مشروع{{ $hasFilters ? ' مطابق للتصفية' : '' }}</p>
            </div>

            <div class="list-card-actions">
                @if ($hasFilters)
                    <button type="button" class="btn btn-light" wire:click="resetFilters">
                        <i class="bi bi-x-lg"></i>
                        مسح التصفية
                    </button>
                @endif

                @can('projects.create')
                    <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        مشروع جديد
                    </a>
                @endcan
            </div>
        </header>

        <div class="list-filters">
            <div class="list-filter list-filter-search">
                <label class="form-label" for="projectSearch">بحث</label>
                <div class="list-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="projectSearch" class="form-control" placeholder="اسم المشروع / العميل / الخدمة"
                        wire:model.live.debounce.400ms="search">
                </div>
            </div>

            <div class="list-filter">
                <label class="form-label" for="projectStatus">الحالة</label>
                <select id="projectStatus" class="form-select" wire:model.live="status">
                    <option value="">كل الحالات</option>
                    <option value="new">جديد</option>
                    <option value="planning">مرحلة التخطيط</option>
                    <option value="in_progress">قيد التنفيذ</option>
                    <option value="on_hold">متوقف مؤقتًا</option>
                    <option value="completed">مكتمل</option>
                    <option value="cancelled">ملغي</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="projectPriority">الأولوية</label>
                <select id="projectPriority" class="form-select" wire:model.live="priority">
                    <option value="">كل الأولويات</option>
                    <option value="low">منخفضة</option>
                    <option value="medium">متوسطة</option>
                    <option value="high">عالية</option>
                    <option value="urgent">عاجلة</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="projectTeam">الفريق</label>
                <select id="projectTeam" class="form-select" wire:model.live="teamId">
                    <option value="">كل الفرق</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="projectManager">مدير المشروع</label>
                <select id="projectManager" class="form-select" wire:model.live="managerMemberId">
                    <option value="">كل المديرين</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}">
                            {{ $member->name }}
                            @if ($member->team)
                                - {{ $member->team->name }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="projectDate">التاريخ</label>
                <select id="projectDate" class="form-select" wire:model.live="dateFilter">
                    <option value="">كل التواريخ</option>
                    <option value="overdue">متأخرة</option>
                    <option value="this_month">تسليم هذا الشهر</option>
                    <option value="completed">مكتملة</option>
                </select>
            </div>
        </div>

        <div class="table-responsive data-table-wrap">
            <table class="table align-middle mb-0 data-table has-actions">
                <thead>
                    <tr>
                        <th>المشروع</th>
                        <th>العميل والخدمة</th>
                        <th>الفريق</th>
                        <th>المهام</th>
                        <th>الأولوية</th>
                        <th>الإنجاز</th>
                        <th>الحالة</th>
                        <th>التسليم</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($projects as $project)
                        <tr @class(['is-overdue' => $project->is_overdue]) wire:key="project-{{ $project->id }}">
                            <td class="cell-title-wrap">
                                <a href="{{ route('admin.projects.show', $project) }}" class="cell-title" title="{{ $project->name }}">
                                    {{ $project->name }}
                                </a>
                                <span class="cell-sub">{{ $project->code ?: '-' }}</span>

                                @if ($project->is_overdue)
                                    <span class="cell-alert">
                                        <i class="bi bi-exclamation-circle"></i>
                                        متأخر
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ($project->client)
                                    <a href="{{ route('admin.clients.show', $project->client) }}" class="cell-link">
                                        {{ $project->client->name }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                                <span class="cell-sub">{{ $project->service?->name ?? '-' }}</span>
                            </td>

                            <td class="text-nowrap">
                                <span class="cell-date">{{ $project->team?->name ?? '-' }}</span>
                                <span class="cell-sub">{{ $project->manager?->name ?? '-' }}</span>
                            </td>

                            <td class="text-nowrap">
                                <span class="badge bg-light text-dark border">الكل: {{ $project->tasks_count }}</span>
                                <span class="badge bg-warning">مفتوحة: {{ $project->open_tasks_count }}</span>
                            </td>

                            <td>
                                <span class="badge {{ $project->priority_badge_class }}">{{ $project->priority_label }}</span>
                            </td>

                            <td style="min-width: 120px;">
                                <div class="cell-progress">
                                    <div class="cell-progress-bar"
                                        role="progressbar"
                                        aria-valuenow="{{ $project->progress_percentage }}" aria-valuemin="0" aria-valuemax="100">
                                        <span style="width: {{ $project->progress_percentage }}%"></span>
                                    </div>
                                    <span class="cell-progress-value">{{ $project->progress_percentage }}%</span>
                                </div>
                            </td>

                            <td>
                                <div class="cell-status">
                                    <span class="badge {{ $project->status_badge_class }}">{{ $project->status_label }}</span>

                                    @can('projects.change_status')
                                        <form action="{{ route('admin.projects.change-status', $project) }}" method="POST"
                                            class="cell-status-form">
                                            @csrf
                                            @method('PATCH')

                                            <select name="status" class="form-select form-select-sm cell-status-select"
                                                onchange="this.form.submit()" aria-label="تغيير حالة المشروع">
                                                <option value="new" @selected($project->status === 'new')>جديد</option>
                                                <option value="planning" @selected($project->status === 'planning')>مرحلة التخطيط</option>
                                                <option value="in_progress" @selected($project->status === 'in_progress')>قيد التنفيذ</option>
                                                <option value="on_hold" @selected($project->status === 'on_hold')>متوقف مؤقتًا</option>
                                                <option value="completed" @selected($project->status === 'completed')>مكتمل</option>
                                                <option value="cancelled" @selected($project->status === 'cancelled')>ملغي</option>
                                            </select>
                                        </form>
                                    @endcan
                                </div>
                            </td>

                            <td class="cell-date-wrap">
                                <span class="cell-date {{ $project->is_overdue ? 'is-overdue' : '' }}">
                                    {{ $project->due_date?->format('Y-m-d') ?? '-' }}
                                </span>

                                @if ($project->completed_date)
                                    <span class="cell-success">
                                        <i class="bi bi-check2"></i>
                                        اكتمل {{ $project->completed_date->format('Y-m-d') }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.projects.show', $project) }}" class="row-action is-view"
                                        title="عرض المشروع" aria-label="عرض المشروع">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('projects.edit')
                                        <a href="{{ route('admin.projects.edit', $project) }}" class="row-action is-edit"
                                            title="تعديل المشروع" aria-label="تعديل المشروع">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('projects.delete')
                                        <form action="{{ route('admin.projects.destroy', $project) }}" method="POST"
                                            class="js-delete-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="row-action is-danger" title="حذف المشروع"
                                                aria-label="حذف المشروع">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="list-empty">
                                    <i class="bi bi-inbox"></i>
                                    <div>{{ $hasFilters ? 'لا توجد مشاريع مطابقة للتصفية' : 'لا توجد مشاريع حتى الآن' }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($projects->hasPages())
            <div class="list-card-foot">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</div>
