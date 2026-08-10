@extends('layouts.admin')

@section('title', 'تفاصيل المشروع')
@section('page_title', 'تفاصيل المشروع')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $project->name }}</h4>
            <div class="text-muted">
                {{ $project->code ?: 'بدون كود' }}
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.projects.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-right"></i>
                رجوع
            </a>

            @can('projects.edit')
                <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i>
                    تعديل
                </a>
            @endcan

            @can('tasks.create')
                <a href="{{ route('admin.tasks.create', [
                    'project_id' => $project->id,
                    'client_id' => $project->client_id,
                    'assigned_member_id' => $project->manager_member_id,
                ]) }}"
                    class="btn btn-success">
                    <i class="bi bi-plus-circle"></i>
                    إضافة مهمة
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">بيانات المشروع</h5>
                </div>

                <div class="card-body">
                    <div class="client-info-item">
                        <span>العميل</span>
                        <strong>
                            @if ($project->client)
                                <a href="{{ route('admin.clients.show', $project->client) }}">
                                    {{ $project->client->name }}
                                </a>
                            @else
                                -
                            @endif
                        </strong>
                    </div>

                    <div class="client-info-item">
                        <span>الخدمة</span>
                        <strong>{{ $project->service?->name ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>الفريق</span>
                        <strong>{{ $project->team?->name ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>مدير المشروع</span>
                        <strong>
                            @if ($project->manager)
                                <a href="{{ route('admin.members.show', $project->manager) }}">
                                    {{ $project->manager->name }}
                                </a>
                            @else
                                -
                            @endif
                        </strong>
                    </div>

                    <div class="client-info-item">
                        <span>الأولوية</span>
                        <strong>
                            <span class="badge {{ $project->priority_badge_class }}">
                                {{ $project->priority_label }}
                            </span>
                        </strong>
                    </div>

                    <div class="client-info-item">
                        <span>الحالة</span>
                        <strong>
                            <span class="badge {{ $project->status_badge_class }}">
                                {{ $project->status_label }}
                            </span>
                        </strong>
                    </div>
                    <div class="client-info-item">
                        <span>نسبة الإنجاز</span>
                        <strong>{{ $project->progress_percentage }}%</strong>
                    </div>

                    <div class="mb-3">
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" role="progressbar"
                                style="width: {{ $project->progress_percentage }}%;"
                                aria-valuenow="{{ $project->progress_percentage }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>

                        <div class="small text-muted mt-1">
                            {{ $project->progress_source_label }}
                        </div>
                    </div>

                    <div class="client-info-item">
                        <span>البداية</span>
                        <strong>{{ $project->start_date?->format('Y-m-d') ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>التسليم</span>
                        <strong>
                            {{ $project->due_date?->format('Y-m-d') ?? '-' }}

                            @if ($project->is_overdue)
                                <span class="badge bg-danger ms-1">متأخر</span>
                            @endif
                        </strong>
                    </div>

                    <div class="client-info-item">
                        <span>تاريخ الإكمال</span>
                        <strong>{{ $project->completed_date?->format('Y-m-d') ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>الميزانية</span>
                        <strong>
                            {{ $project->budget ? number_format($project->budget, 2) : '-' }}
                        </strong>
                    </div>
                </div>
            </div>

            @can('projects.change_status')
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">تغيير الحالة</h5>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.projects.change-status', $project) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <select name="status" class="form-select mb-3">
                                <option value="new" @selected($project->status === 'new')>جديد</option>
                                <option value="planning" @selected($project->status === 'planning')>مرحلة التخطيط</option>
                                <option value="in_progress" @selected($project->status === 'in_progress')>قيد التنفيذ</option>
                                <option value="on_hold" @selected($project->status === 'on_hold')>متوقف مؤقتًا</option>
                                <option value="completed" @selected($project->status === 'completed')>مكتمل</option>
                                <option value="cancelled" @selected($project->status === 'cancelled')>ملغي</option>
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
                    <h5 class="mb-0">وصف المشروع</h5>
                </div>

                <div class="card-body">
                    <p class="text-muted mb-0">
                        {{ $project->description ?: 'لا يوجد وصف' }}
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">ملاحظات</h5>
                </div>

                <div class="card-body">
                    <p class="text-muted mb-0">
                        {{ $project->notes ?: 'لا توجد ملاحظات' }}
                    </p>
                </div>
            </div>
            <livewire:admin.projects.project-milestones :project="$project" />
            <livewire:admin.shared.related-tasks type="project" :id="$project->id" />

            <livewire:admin.projects.project-comments :project="$project" />

            <livewire:admin.projects.project-attachments :project="$project" />

            <livewire:admin.shared.activity-timeline :subject-type="get_class($project)" :subject-id="$project->id" />
        </div>
    </div>

@endsection
