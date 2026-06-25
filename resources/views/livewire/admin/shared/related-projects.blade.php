<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">المشاريع المرتبطة</h5>

            @can('projects.create')
                @php
                    $createProjectUrl = match ($type) {
                        'client' => route('admin.projects.create', ['client_id' => $id]),
                        'team' => route('admin.projects.create', ['team_id' => $id]),
                        'member' => route('admin.projects.create', ['manager_member_id' => $id]),
                        default => route('admin.projects.create'),
                    };
                @endphp

                <a href="{{ $createProjectUrl }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    إضافة مشروع
                </a>
            @endcan
        </div>

        <div class="card-body">
            @forelse ($projects as $project)
                <div class="related-project-item">
                    <div class="d-flex justify-content-between gap-3">
                        <div>
                            <div class="fw-semibold">
                                <a href="{{ route('admin.projects.show', $project) }}" class="text-decoration-none">
                                    {{ $project->name }}
                                </a>
                            </div>

                            <div class="small text-muted">
                                الكود: {{ $project->code ?: '-' }}
                            </div>

                            <div class="small text-muted">
                                العميل:
                                @if ($project->client)
                                    <a href="{{ route('admin.clients.show', $project->client) }}"
                                        class="text-decoration-none">
                                        {{ $project->client->name }}
                                    </a>
                                @else
                                    -
                                @endif
                            </div>

                            <div class="small text-muted">
                                الفريق: {{ $project->team?->name ?? '-' }}
                                |
                                المدير: {{ $project->manager?->name ?? '-' }}
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

                            @if ($project->is_overdue)
                                <div class="small text-danger">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    متأخر
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="small text-muted">
                            المهام:
                            الكل {{ $project->tasks_count }}
                            /
                            المفتوحة {{ $project->open_tasks_count }}

                            <span class="mx-2">|</span>

                            التسليم:
                            {{ $project->due_date?->format('Y-m-d') ?? '-' }}
                        </div>
                        <div class="mt-2" style="max-width: 260px;">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>الإنجاز</span>
                                <span>{{ $project->progress_percentage }}%</span>
                            </div>

                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" role="progressbar"
                                    style="width: {{ $project->progress_percentage }}%;"
                                    aria-valuenow="{{ $project->progress_percentage }}" aria-valuemin="0"
                                    aria-valuemax="100">
                                </div>
                            </div>
                        </div>

                        <div>
                            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-outline-dark">
                                <i class="bi bi-eye"></i>
                            </a>

                            @can('projects.edit')
                                <a href="{{ route('admin.projects.edit', $project) }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    لا توجد مشاريع مرتبطة حتى الآن
                </div>
            @endforelse
        </div>
    </div>
</div>
