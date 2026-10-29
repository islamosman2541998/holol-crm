<div>
    @php
        $hasFilters = $search !== '' || $status !== '' || $priority !== '' || $teamId !== '' || $assignedMemberId !== '' || $dateFilter !== '';
    @endphp

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">قائمة المهام</h2>
                <p class="list-card-sub">{{ number_format($tasks->total()) }} مهمة{{ $hasFilters ? ' مطابقة للتصفية' : '' }}</p>
            </div>

            <div class="list-card-actions">
                @if ($hasFilters)
                    <button type="button" class="btn btn-light" wire:click="resetFilters">
                        <i class="bi bi-x-lg"></i>
                        مسح التصفية
                    </button>
                @endif

                @can('tasks.create')
                    <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        مهمة جديدة
                    </a>
                @endcan
            </div>
        </header>

        <div class="list-filters">
            <div class="list-filter list-filter-search">
                <label class="form-label" for="taskSearch">بحث</label>
                <div class="list-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="taskSearch" class="form-control" placeholder="عنوان المهمة / العميل / Lead"
                        wire:model.live.debounce.400ms="search">
                </div>
            </div>

            <div class="list-filter">
                <label class="form-label" for="taskStatus">الحالة</label>
                <select id="taskStatus" class="form-select" wire:model.live="status">
                    <option value="">كل الحالات</option>
                    <option value="new">جديدة</option>
                    <option value="in_progress">قيد التنفيذ</option>
                    <option value="review">في المراجعة</option>
                    <option value="completed">مكتملة</option>
                    <option value="cancelled">ملغية</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="taskPriority">الأولوية</label>
                <select id="taskPriority" class="form-select" wire:model.live="priority">
                    <option value="">كل الأولويات</option>
                    <option value="low">منخفضة</option>
                    <option value="medium">متوسطة</option>
                    <option value="high">عالية</option>
                    <option value="urgent">عاجلة</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="taskTeam">الفريق</label>
                <select id="taskTeam" class="form-select" wire:model.live="teamId">
                    <option value="">كل الفرق</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="taskMember">المسؤول</label>
                <select id="taskMember" class="form-select" wire:model.live="assignedMemberId">
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

            <div class="list-filter">
                <label class="form-label" for="taskDate">التاريخ</label>
                <select id="taskDate" class="form-select" wire:model.live="dateFilter">
                    <option value="">كل التواريخ</option>
                    <option value="today">تسليم اليوم</option>
                    <option value="overdue">متأخرة</option>
                    <option value="upcoming">قادمة</option>
                </select>
            </div>
        </div>

        <div class="table-responsive data-table-wrap">
            <table class="table align-middle mb-0 data-table has-actions task-table">
                <thead>
                    <tr>
                        <th>المهمة</th>
                        <th>المسؤول</th>
                        <th>مرتبطة بـ</th>
                        <th>الأولوية</th>
                        <th>الحالة</th>
                        <th>الإنشاء</th>
                        <th>التسليم</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($tasks as $task)
                        <tr @class(['is-overdue' => $task->is_overdue]) wire:key="task-{{ $task->id }}">
                            <td class="cell-title-wrap">
                                <a href="{{ route('admin.tasks.show', $task) }}" class="cell-title"
                                    title="{{ $task->title }}">
                                    {{ $task->title }}
                                </a>

                                @if ($task->is_overdue)
                                    <span class="cell-alert">
                                        <i class="bi bi-exclamation-circle"></i>
                                        متأخرة {{ $task->due_at->locale('ar')->diffForHumans(null, true) }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                @forelse ($task->assignedMembers as $assignedMember)
                                    <div class="cell-person">
                                        <span class="cell-person-avatar">{{ mb_substr($assignedMember->name, 0, 1) }}</span>
                                        <div class="cell-person-text">
                                            <span class="cell-person-name">{{ $assignedMember->name }}</span>
                                            @if ($assignedMember->team)
                                                <span class="cell-person-sub">{{ $assignedMember->team->name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <span class="text-muted">-</span>
                                @endforelse
                            </td>

                            <td class="cell-related">
                                @if ($task->project)
                                    <span class="cell-type">مشروع</span>
                                    <a href="{{ route('admin.projects.show', $task->project) }}" class="cell-link">
                                        {{ $task->project->name }}
                                    </a>
                                    @if ($task->project->client)
                                        <a href="{{ route('admin.clients.show', $task->project->client) }}" class="cell-sub-link">
                                            {{ $task->project->client->name }}
                                        </a>
                                    @endif
                                @elseif ($task->client)
                                    <span class="cell-type">عميل</span>
                                    <a href="{{ route('admin.clients.show', $task->client) }}" class="cell-link">
                                        {{ $task->client->name }}
                                    </a>
                                @elseif ($task->lead)
                                    <span class="cell-type">Lead</span>
                                    <a href="{{ route('admin.leads.show', $task->lead) }}" class="cell-link">
                                        {{ $task->lead->name }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge {{ $task->priority_badge_class }}">{{ $task->priority_label }}</span>
                            </td>

                            <td>
                                <div class="cell-status">
                                    <span class="badge {{ $task->status_badge_class }}">{{ $task->status_label }}</span>

                                @can('tasks.change_status')
                                    <form action="{{ route('admin.tasks.change-status', $task) }}" method="POST"
                                        class="cell-status-form">
                                        @csrf
                                        @method('PATCH')

                                        <select name="status" class="form-select form-select-sm cell-status-select"
                                            onchange="this.form.submit()" aria-label="تغيير حالة المهمة">
                                            <option value="new" @selected($task->status === 'new')>جديدة</option>
                                            <option value="in_progress" @selected($task->status === 'in_progress')>قيد التنفيذ</option>
                                            <option value="review" @selected($task->status === 'review')>في المراجعة</option>
                                            <option value="completed" @selected($task->status === 'completed')>مكتملة</option>
                                            <option value="cancelled" @selected($task->status === 'cancelled')>ملغية</option>
                                        </select>
                                    </form>
                                @endcan
                                </div>
                            </td>

                            <td class="cell-date-wrap">
                                <span class="cell-date">{{ $task->created_at->format('Y-m-d') }} <span class="cell-time-inline">{{ $task->created_at->format('H:i') }}</span></span>
                                <span class="cell-time">بواسطة {{ $task->creator?->name ?? 'System' }}</span>
                            </td>

                            <td class="cell-date-wrap">
                                @if ($task->due_at)
                                    <span class="cell-date {{ $task->is_overdue ? 'is-overdue' : '' }}">{{ $task->due_at->format('Y-m-d') }}</span>
                                    <span class="cell-time">{{ $task->due_at->format('H:i') }}</span>

                                    @if ($task->completed_at)
                                        <span class="cell-success">
                                            <i class="bi bi-check2"></i>
                                            تمت {{ $task->completed_at->format('Y-m-d') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.tasks.show', $task) }}" class="row-action is-view"
                                        title="عرض المهمة" aria-label="عرض المهمة">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('tasks.edit')
                                        <a href="{{ route('admin.tasks.edit', $task) }}" class="row-action is-edit"
                                            title="تعديل المهمة" aria-label="تعديل المهمة">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('tasks.delete')
                                        <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST"
                                            class="js-delete-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="row-action is-danger" title="حذف المهمة"
                                                aria-label="حذف المهمة">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="list-empty">
                                    <i class="bi bi-inbox"></i>
                                    <div>{{ $hasFilters ? 'لا توجد مهام مطابقة للتصفية' : 'لا توجد مهام حتى الآن' }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tasks->hasPages())
            <div class="list-card-foot">
                {{ $tasks->links() }}
            </div>
        @endif
    </div>
</div>
