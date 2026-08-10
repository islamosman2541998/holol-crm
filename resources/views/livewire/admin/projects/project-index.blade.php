<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">بحث</label>
                    <input type="text" class="form-control" placeholder="اسم المشروع / العميل / الخدمة"
                        wire:model.live.debounce.400ms="search">
                </div>

                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">كل الحالات</option>
                        <option value="new">جديد</option>
                        <option value="planning">مرحلة التخطيط</option>
                        <option value="in_progress">قيد التنفيذ</option>
                        <option value="on_hold">متوقف مؤقتًا</option>
                        <option value="completed">مكتمل</option>
                        <option value="cancelled">ملغي</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">الأولوية</label>
                    <select class="form-select" wire:model.live="priority">
                        <option value="">كل الأولويات</option>
                        <option value="low">منخفضة</option>
                        <option value="medium">متوسطة</option>
                        <option value="high">عالية</option>
                        <option value="urgent">عاجلة</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">الفريق</label>
                    <select class="form-select" wire:model.live="teamId">
                        <option value="">كل الفرق</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">مدير المشروع</label>
                    <select class="form-select" wire:model.live="managerMemberId">
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

                <div class="col-md-3">
                    <label class="form-label">التاريخ</label>
                    <select class="form-select" wire:model.live="dateFilter">
                        <option value="">كل التواريخ</option>
                        <option value="overdue">متأخرة</option>
                        <option value="this_month">تسليم هذا الشهر</option>
                        <option value="completed">مكتملة</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-light w-100" wire:click="resetFilters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Reset
                    </button>
                </div>

                <div class="col-md-3 ms-auto">
                    @can('projects.create')
                        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i>
                            مشروع جديد
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">قائمة المشاريع</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>المشروع</th>
                            <th>العميل</th>
                            <th>الخدمة</th>
                            <th>الفريق</th>
                            <th>مدير المشروع</th>
                            <th>المهام</th>
                            <th>الأولوية</th>
                            <th>الإنجاز</th>
                            <th>الحالة</th>
                            <th>التسليم</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($projects as $project)
                            <tr @class(['table-warning' => $project->is_overdue])>
                                <td>
                                    <div class="fw-semibold">
                                        <a href="{{ route('admin.projects.show', $project) }}"
                                            class="text-decoration-none">
                                            {{ $project->name }}
                                        </a>
                                    </div>

                                    <div class="small text-muted">
                                        {{ $project->code ?: '-' }}
                                    </div>

                                    @if ($project->is_overdue)
                                        <div class="small text-danger mt-1">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            متأخر
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    @if ($project->client)
                                        <a href="{{ route('admin.clients.show', $project->client) }}"
                                            class="text-decoration-none">
                                            {{ $project->client->name }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>{{ $project->service?->name ?? '-' }}</td>

                                <td>{{ $project->team?->name ?? '-' }}</td>

                                <td>{{ $project->manager?->name ?? '-' }}</td>

                                <td>
                                    <span class="badge bg-light text-dark border">
                                        الكل: {{ $project->tasks_count }}
                                    </span>

                                    <span class="badge bg-warning">
                                        مفتوحة: {{ $project->open_tasks_count }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge {{ $project->priority_badge_class }}">
                                        {{ $project->priority_label }}
                                    </span>
                                </td>
                                <td style="min-width: 130px;">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>{{ $project->progress_percentage }}%</span>
                                    </div>

                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: {{ $project->progress_percentage }}%;"
                                            aria-valuenow="{{ $project->progress_percentage }}" aria-valuemin="0"
                                            aria-valuemax="100">
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $project->status_badge_class }}">
                                        {{ $project->status_label }}
                                    </span>

                                    @can('projects.change_status')
                                        <form action="{{ route('admin.projects.change-status', $project) }}" method="POST"
                                            class="mt-2">
                                            @csrf
                                            @method('PATCH')

                                            <select name="status" class="form-select form-select-sm"
                                                onchange="this.form.submit()">
                                                <option value="new" @selected($project->status === 'new')>جديد</option>
                                                <option value="planning" @selected($project->status === 'planning')>مرحلة التخطيط</option>
                                                <option value="in_progress" @selected($project->status === 'in_progress')>قيد التنفيذ
                                                </option>
                                                <option value="on_hold" @selected($project->status === 'on_hold')>متوقف مؤقتًا</option>
                                                <option value="completed" @selected($project->status === 'completed')>مكتمل</option>
                                                <option value="cancelled" @selected($project->status === 'cancelled')>ملغي</option>
                                            </select>
                                        </form>
                                    @endcan
                                </td>

                                <td>
                                    {{ $project->due_date?->format('Y-m-d') ?? '-' }}

                                    @if ($project->completed_date)
                                        <div class="small text-success">
                                            اكتمل: {{ $project->completed_date->format('Y-m-d') }}
                                        </div>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('admin.projects.show', $project) }}"
                                        class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('projects.edit')
                                        <a href="{{ route('admin.projects.edit', $project) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('projects.delete')
                                        <form action="{{ route('admin.projects.destroy', $project) }}" method="POST"
                                            class="d-inline js-delete-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    لا توجد مشاريع حتى الآن
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $projects->links() }}
            </div>
        </div>
    </div>
</div>
