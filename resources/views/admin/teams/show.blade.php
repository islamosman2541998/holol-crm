@extends('layouts.admin')

@section('title', 'تفاصيل الفريق')
@section('page_title', 'تفاصيل الفريق')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $team->name }}</h4>
            <div class="text-muted">
                <code>{{ $team->role_name }}</code>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.teams.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-right"></i>
                رجوع
            </a>

            @can('teams.permissions')
                <form action="{{ route('admin.teams.sync-members', $team) }}" method="POST" class="d-inline">
                    @csrf

                    <button type="submit" class="btn btn-outline-success">
                        <i class="bi bi-arrow-repeat"></i>
                        مزامنة صلاحيات الفريق
                    </button>
                </form>
            @endcan

            @can('teams.edit')
                <a href="{{ route('admin.teams.edit', $team) }}" class="btn btn-primary">
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
                    <h5 class="mb-0">بيانات الفريق</h5>
                </div>

                <div class="card-body">
                    <div class="client-info-item">
                        <span>اسم الفريق</span>
                        <strong>{{ $team->name }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>الكود</span>
                        <strong><code>{{ $team->code }}</code></strong>
                    </div>

                    <div class="client-info-item">
                        <span>Role الفريق</span>
                        <strong><code>{{ $team->role_name }}</code></strong>
                    </div>

                    <div class="client-info-item">
                        <span>مدير الفريق</span>
                        <strong>{{ $team->manager?->name ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>عدد الأعضاء</span>
                        <strong>{{ $team->members->count() }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>الحالة</span>
                        <strong>
                            <span class="badge {{ $team->status_badge_class }}">
                                {{ $team->status_label }}
                            </span>
                        </strong>
                    </div>
                </div>
            </div>


            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">الوصف</h5>
                </div>

                <div class="card-body">
                    <p class="text-muted mb-0">
                        {{ $team->description ?: 'لا يوجد وصف' }}
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">صلاحيات الفريق</h5>
                </div>

                <div class="card-body">
                    @php
                        $role = $team->role();
                        $rolePermissions = $role ? $role->permissions->pluck('name') : collect();
                    @endphp

                    @if ($rolePermissions->count())
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($rolePermissions as $permission)
                                <span class="badge bg-light text-dark border">
                                    {{ $permission }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted">
                            لا توجد صلاحيات محددة لهذا الفريق.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">أعضاء الفريق</h5>

                    @can('members.create')
                        <a href="{{ route('admin.members.create') }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle"></i>
                            إضافة عضو
                        </a>
                    @endcan
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>العضو</th>
                                    <th>الوظيفة</th>
                                    <th>المدير المباشر</th>
                                    <th>حساب الدخول</th>
                                    <th>الحالة</th>
                                    <th class="text-end">الإجراءات</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($team->members as $member)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $member->name }}</div>
                                            <div class="small text-muted">{{ $member->mobile ?? '-' }}</div>
                                        </td>

                                        <td>{{ $member->job_title ?? '-' }}</td>

                                        <td>{{ $member->directManager?->name ?? '-' }}</td>

                                        <td>
                                            @if ($member->user)
                                                {{ $member->user->email }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge {{ $member->status_badge_class }}">
                                                {{ $member->status_label }}
                                            </span>

                                            @if ($member->is_manager)
                                                <span class="badge bg-primary">مدير</span>
                                            @endif
                                        </td>

                                        <td class="text-end">
                                            <a href="{{ route('admin.members.show', $member) }}"
                                                class="btn btn-sm btn-outline-dark">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            @can('members.edit')
                                                <a href="{{ route('admin.members.edit', $member) }}"
                                                    class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            لا يوجد أعضاء داخل هذا الفريق
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <livewire:admin.shared.related-projects type="team" :id="$team->id" />
            <livewire:admin.shared.activity-timeline :subject-type="get_class($team)" :subject-id="$team->id" />
        </div>
    </div>

@endsection
