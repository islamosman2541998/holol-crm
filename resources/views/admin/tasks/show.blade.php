@extends('layouts.admin')

@section('title', 'تفاصيل المهمة')
@section('page_title', 'تفاصيل المهمة')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $task->title }}</h4>
            <div class="text-muted">
                أنشئت بواسطة: {{ $task->creator?->name ?? 'System' }}
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.tasks.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-right"></i>
                رجوع
            </a>

            @can('tasks.edit')
                <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-primary">
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
                    <h5 class="mb-0">بيانات المهمة</h5>
                </div>

                <div class="card-body">
                    <div class="client-info-item">
                        <span>المسؤول</span>
                        <strong>
                            @if ($task->assignedMember)
                                <a href="{{ route('admin.members.show', $task->assignedMember) }}">
                                    {{ $task->assignedMember->name }}
                                </a>
                            @else
                                -
                            @endif
                        </strong>
                    </div>

                    <div class="client-info-item">
                        <span>الفريق</span>
                        <strong>{{ $task->assignedMember?->team?->name ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>الأولوية</span>
                        <strong>
                            <span class="badge {{ $task->priority_badge_class }}">
                                {{ $task->priority_label }}
                            </span>
                        </strong>
                    </div>

                    <div class="client-info-item">
                        <span>الحالة</span>
                        <strong>
                            <span class="badge {{ $task->status_badge_class }}">
                                {{ $task->status_label }}
                            </span>
                        </strong>
                    </div>

                    <div class="client-info-item">
                        <span>تاريخ البداية</span>
                        <strong>{{ $task->start_at?->format('Y-m-d H:i') ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>تاريخ التسليم</span>
                        <strong>
                            {{ $task->due_at?->format('Y-m-d H:i') ?? '-' }}

                            @if ($task->is_overdue)
                                <span class="badge bg-danger ms-1">متأخرة</span>
                            @endif
                        </strong>
                    </div>

                    <div class="client-info-item">
                        <span>تاريخ الإكمال</span>
                        <strong>{{ $task->completed_at?->format('Y-m-d H:i') ?? '-' }}</strong>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">مرتبطة بـ</h5>
                </div>

                <div class="card-body">
                    @if ($task->project)
                        <div class="client-info-item">
                            <span>النوع</span>
                            <strong>مشروع</strong>
                        </div>

                        <div class="client-info-item">
                            <span>المشروع</span>
                            <strong>
                                <a href="{{ route('admin.projects.show', $task->project) }}">
                                    {{ $task->project->name }}
                                </a>
                            </strong>
                        </div>

                        <div class="client-info-item">
                            <span>العميل</span>
                            <strong>
                                <a href="{{ route('admin.clients.show', $task->project->client) }}">
                                    {{ $task->project->client?->name ?? '-' }}
                                </a>
                            </strong>
                        </div>
                    @elseif ($task->client)
                        <div class="client-info-item">
                            <span>النوع</span>
                            <strong>عميل</strong>
                        </div>

                        <div class="client-info-item">
                            <span>العميل</span>
                            <strong>
                                <a href="{{ route('admin.clients.show', $task->client) }}">
                                    {{ $task->client->name }}
                                </a>
                            </strong>
                        </div>
                    @elseif ($task->lead)
                        <div class="client-info-item">
                            <span>النوع</span>
                            <strong>Lead</strong>
                        </div>

                        <div class="client-info-item">
                            <span>Lead</span>
                            <strong>
                                <a href="{{ route('admin.leads.show', $task->lead) }}">
                                    {{ $task->lead->name }}
                                </a>
                            </strong>
                        </div>
                    @else
                        <div class="text-muted">
                            المهمة غير مرتبطة بعميل أو Lead أو مشروع.
                        </div>
                    @endif
                </div>
            </div>

            @can('tasks.change_status')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">تغيير الحالة</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.tasks.change-status', $task) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <select name="status" class="form-select mb-3">
                                <option value="new" @selected($task->status === 'new')>جديدة</option>
                                <option value="in_progress" @selected($task->status === 'in_progress')>قيد التنفيذ</option>
                                <option value="review" @selected($task->status === 'review')>في المراجعة</option>
                                <option value="completed" @selected($task->status === 'completed')>مكتملة</option>
                                <option value="cancelled" @selected($task->status === 'cancelled')>ملغية</option>
                            </select>

                            <button class="btn btn-success w-100">
                                <i class="bi bi-check2-circle"></i>
                                تحديث الحالة
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">وصف المهمة</h5>
                </div>

                <div class="card-body">
                    <p class="text-muted mb-0">
                        {{ $task->description ?: 'لا يوجد وصف' }}
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">ملاحظات</h5>
                </div>

                <div class="card-body">
                    <p class="text-muted mb-0">
                        {{ $task->notes ?: 'لا توجد ملاحظات' }}
                    </p>
                </div>
            </div>
            <livewire:admin.tasks.task-comments :task="$task" />

            <livewire:admin.tasks.task-attachments :task="$task" />
            <livewire:admin.shared.activity-timeline :subject-type="get_class($task)" :subject-id="$task->id" />
        </div>
    </div>

@endsection
