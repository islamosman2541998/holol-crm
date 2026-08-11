<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">المهام المرتبطة</h5>

            @can('tasks.create')
                @php
                    $createTaskUrl = match ($type) {
                        'client' => route('admin.tasks.create', ['client_id' => $id]),
                        'lead' => route('admin.tasks.create', ['lead_id' => $id]),
                        'member' => route('admin.tasks.create', ['assigned_member_id' => $id]),
                        'project' => route('admin.tasks.create', ['project_id' => $id]),
                        default => route('admin.tasks.create'),
                    };
                @endphp

                <a href="{{ $createTaskUrl }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    إضافة مهمة
                </a>
            @endcan
        </div>

        <div class="card-body">
            @forelse ($tasks as $task)
                <div class="related-task-item">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold">
                                <a href="{{ route('admin.tasks.show', $task) }}" class="text-decoration-none">
                                    {{ $task->title }}
                                </a>
                            </div>

                            @if ($task->description)
                                <div class="text-muted small">
                                    {{ str($task->description)->limit(90) }}
                                </div>
                            @endif

                            <div class="small text-muted mt-1">
                                المسؤول:
                                {{ $task->assignedMembers->pluck('name')->implode('، ') ?: '-' }}
                            </div>
                            <div class="small text-muted">
                                @if ($task->project)
                                    المشروع: {{ $task->project->name }}
                                @elseif ($task->client)
                                    العميل: {{ $task->client->name }}
                                @elseif ($task->lead)
                                    Lead: {{ $task->lead->name }}
                                @endif
                            </div>
                        </div>

                        <div class="text-end">
                            <div class="mb-1">
                                <span class="badge {{ $task->status_badge_class }}">
                                    {{ $task->status_label }}
                                </span>
                            </div>

                            <div>
                                <span class="badge {{ $task->priority_badge_class }}">
                                    {{ $task->priority_label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="small text-muted">
                            تاريخ التسليم:
                            {{ $task->due_at?->format('Y-m-d H:i') ?? '-' }}

                            @if ($task->is_overdue)
                                <span class="text-danger ms-1">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    متأخرة
                                </span>
                            @endif
                        </div>

                        <div>
                            <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-sm btn-outline-dark">
                                <i class="bi bi-eye"></i>
                            </a>

                            @can('tasks.edit')
                                <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    لا توجد مهام مرتبطة حتى الآن
                </div>
            @endforelse
        </div>
    </div>
</div>
