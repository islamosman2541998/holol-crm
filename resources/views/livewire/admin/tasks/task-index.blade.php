<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">بحث</label>
                    <input type="text" class="form-control" placeholder="عنوان المهمة / العميل / Lead"
                        wire:model.live.debounce.400ms="search">
                </div>

                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">كل الحالات</option>
                        <option value="new">جديدة</option>
                        <option value="in_progress">قيد التنفيذ</option>
                        <option value="review">في المراجعة</option>
                        <option value="completed">مكتملة</option>
                        <option value="cancelled">ملغية</option>
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
                    <label class="form-label">المسؤول</label>
                    <select class="form-select" wire:model.live="assignedMemberId">
                        <option value="">كل الأعضاء</option>
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
                        <option value="today">تسليم اليوم</option>
                        <option value="overdue">متأخرة</option>
                        <option value="upcoming">قادمة</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-light w-100" wire:click="resetFilters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Reset
                    </button>
                </div>

                <div class="col-md-3 ms-auto">
                    @can('tasks.create')
                        <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i>
                            مهمة جديدة
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">قائمة المهام</h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive task-table-responsive">
                <table class="table table-hover align-middle mb-0 task-table">
                    <thead>
                        <tr>
                            <th>المهمة</th>
                            <th>المسؤول</th>
                            <th>مرتبطة بـ</th>
                            <th>الأولوية</th>
                            <th>الحالة</th>
                            <th>تاريخ الانشاء</th>

                            <th>تاريخ التسليم</th>
                            <th>أنشئت بواسطة</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($tasks as $task)
                            <tr @class(['table-warning' => $task->is_overdue])>
                                <td class="task-title-cell">
                                    <div class="d-flex align-items-center gap-2 text-nowrap">
                                        <a href="{{ route('admin.tasks.show', $task) }}" class="task-title-link"
                                            title="{{ $task->title }}">
                                            {{ $task->title }}
                                        </a>
                                    @if ($task->is_overdue)
                                        <span class="task-overdue-label">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            متأخرة
                                        </span>
                                    @endif
                                    </div>
                                </td>

                                <td class="text-nowrap">
                                    @forelse ($task->assignedMembers as $assignedMember)
                                        <span class="task-assignee">
                                            {{ $assignedMember->name }}
                                            <small class="text-muted">
                                                {{ $assignedMember->team?->name ? '- ' . $assignedMember->team->name : '' }}
                                            </small>
                                        </span>@if (! $loop->last)<span class="text-muted mx-1">،</span>@endif
                                    @empty
                                        -
                                    @endforelse
                                </td>

                                <td class="text-nowrap">
                                    @if ($task->project)
                                        <div class="d-flex align-items-center gap-2 text-nowrap">
                                            <span class="badge bg-light text-dark border">مشروع</span>
                                            <a href="{{ route('admin.projects.show', $task->project) }}"
                                                class="text-decoration-none">
                                                {{ $task->project->name }}
                                            </a>
                                            @if ($task->project->client)
                                                <span class="text-muted">•</span>
                                                <span class="small text-muted">العميل:</span>
                                                <a href="{{ route('admin.clients.show', $task->project->client) }}"
                                                    class="text-decoration-none small">
                                                    {{ $task->project->client->name }}
                                                </a>
                                            @endif
                                        </div>
                                    @elseif ($task->client)
                                        <div>
                                            <span class="badge bg-light text-dark border">عميل</span>
                                            <a href="{{ route('admin.clients.show', $task->client) }}"
                                                class="text-decoration-none">
                                                {{ $task->client->name }}
                                            </a>
                                        </div>
                                    @elseif ($task->lead)
                                        <div>
                                            <span class="badge bg-light text-dark border">Lead</span>
                                            <a href="{{ route('admin.leads.show', $task->lead) }}"
                                                class="text-decoration-none">
                                                {{ $task->lead->name }}
                                            </a>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="text-nowrap">
                                    <span class="badge {{ $task->priority_badge_class }}">
                                        {{ $task->priority_label }}
                                    </span>
                                </td>

                                <td class="text-nowrap">
                                    <div class="d-flex align-items-center gap-2 flex-nowrap">
                                        <span class="badge {{ $task->status_badge_class }}">
                                            {{ $task->status_label }}
                                        </span>
                                    @can('tasks.change_status')
                                        <form action="{{ route('admin.tasks.change-status', $task) }}" method="POST"
                                            class="task-status-form">
                                            @csrf
                                            @method('PATCH')

                                            <select name="status" class="form-select form-select-sm"
                                                onchange="this.form.submit()">
                                                <option value="new" @selected($task->status === 'new')>جديدة</option>
                                                <option value="in_progress" @selected($task->status === 'in_progress')>قيد التنفيذ
                                                </option>
                                                <option value="review" @selected($task->status === 'review')>في المراجعة</option>
                                                <option value="completed" @selected($task->status === 'completed')>مكتملة</option>
                                                <option value="cancelled" @selected($task->status === 'cancelled')>ملغية</option>
                                            </select>
                                        </form>
                                    @endcan
                                    </div>
                                </td>
                                  
                                <td class="text-nowrap task-date-cell">{{ $task->created_at->format('Y-m-d H:i') }}</td>

                                <td class="text-nowrap task-date-cell">
                                    @if ($task->due_at)
                                        <span>{{ $task->due_at->format('Y-m-d H:i') }}</span>

                                        @if ($task->completed_at)
                                            <span class="small text-success ms-2">
                                                تمت: {{ $task->completed_at->format('Y-m-d H:i') }}
                                            </span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="text-nowrap">
                                    {{ $task->creator?->name ?? 'System' }}
                                </td>

                                <td class="text-end text-nowrap">
                                    <div class="task-actions">
                                    <a href="{{ route('admin.tasks.show', $task) }}"
                                        class="btn btn-sm btn-outline-dark" title="عرض المهمة" aria-label="عرض المهمة">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('tasks.edit')
                                        <a href="{{ route('admin.tasks.edit', $task) }}"
                                            class="btn btn-sm btn-outline-primary" title="تعديل المهمة" aria-label="تعديل المهمة">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('tasks.delete')
                                        <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST"
                                            class="d-inline js-delete-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف المهمة"
                                                aria-label="حذف المهمة">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    لا توجد مهام حتى الآن
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                {{ $tasks->links() }}
            </div>
        </div>
    </div>
</div>
