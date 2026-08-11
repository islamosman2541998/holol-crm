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

        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>المهمة</th>
                            <th>المسؤول</th>
                            <th>مرتبطة بـ</th>
                            <th>الأولوية</th>
                            <th>الحالة</th>
                            <th>تاريخ التسليم</th>
                            <th>أنشئت بواسطة</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($tasks as $task)
                            <tr @class(['table-warning' => $task->is_overdue])>
                                <td>
                                    <div class="fw-semibold">{{ $task->title }}</div>

                                    @if ($task->description)
                                        <div class="small text-muted">
                                            {{ str($task->description)->limit(70) }}
                                        </div>
                                    @endif

                                    @if ($task->is_overdue)
                                        <div class="small text-danger mt-1">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            متأخرة
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    @forelse ($task->assignedMembers as $assignedMember)
                                        <div>
                                            {{ $assignedMember->name }}
                                            <span class="small text-muted">
                                                {{ $assignedMember->team?->name ? '- ' . $assignedMember->team->name : '' }}
                                            </span>
                                        </div>
                                    @empty
                                        -
                                    @endforelse
                                </td>

                                <td>
                                    @if ($task->project)
                                        <div>
                                            <span class="badge bg-light text-dark border">مشروع</span>
                                            <a href="{{ route('admin.projects.show', $task->project) }}"
                                                class="text-decoration-none">
                                                {{ $task->project->name }}
                                            </a>
                                        </div>

                                        <div class="small text-muted mt-1">
                                            العميل:
                                            @if ($task->project->client)
                                                <a href="{{ route('admin.clients.show', $task->project->client) }}"
                                                    class="text-decoration-none">
                                                    {{ $task->project->client->name }}
                                                </a>
                                            @else
                                                -
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

                                <td>
                                    <span class="badge {{ $task->priority_badge_class }}">
                                        {{ $task->priority_label }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge {{ $task->status_badge_class }}">
                                        {{ $task->status_label }}
                                    </span>

                                    @can('tasks.change_status')
                                        <form action="{{ route('admin.tasks.change-status', $task) }}" method="POST"
                                            class="mt-2">
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
                                </td>

                                <td>
                                    @if ($task->due_at)
                                        <div>{{ $task->due_at->format('Y-m-d H:i') }}</div>

                                        @if ($task->completed_at)
                                            <div class="small text-success">
                                                تمت: {{ $task->completed_at->format('Y-m-d H:i') }}
                                            </div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    {{ $task->creator?->name ?? 'System' }}
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('admin.tasks.show', $task) }}"
                                        class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('tasks.edit')
                                        <a href="{{ route('admin.tasks.edit', $task) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('tasks.delete')
                                        <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST"
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
                                <td colspan="8" class="text-center text-muted py-4">
                                    لا توجد مهام حتى الآن
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $tasks->links() }}
            </div>
        </div>
    </div>
</div>
