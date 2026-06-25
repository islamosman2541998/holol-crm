@extends('layouts.admin')

@section('title', 'تفاصيل العضو')
@section('page_title', 'تفاصيل العضو')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="member-profile-avatar">
                @if ($member->image)
                    <img src="{{ asset('storage/' . $member->image) }}" alt="{{ $member->name }}">
                @else
                    <span>{{ mb_substr($member->name, 0, 1) }}</span>
                @endif
            </div>

            <div>
                <h4 class="mb-1">
                    {{ $member->name }}

                    @if ($member->is_manager)
                        <span class="badge bg-primary">مدير</span>
                    @endif
                </h4>

                <div class="text-muted">
                    {{ $member->job_title ?? 'بدون وظيفة' }}
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.members.index') }}" class="btn btn-light">
                <i class="bi bi-arrow-right"></i>
                رجوع
            </a>

            @can('members.edit')
                <a href="{{ route('admin.members.edit', $member) }}" class="btn btn-primary">
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
                    <h5 class="mb-0">بيانات العضو</h5>
                </div>

                <div class="card-body">
                    <div class="client-info-item">
                        <span>الفريق</span>
                        <strong>
                            @if ($member->team)
                                <a href="{{ route('admin.teams.show', $member->team) }}">
                                    {{ $member->team->name }}
                                </a>
                            @else
                                -
                            @endif
                        </strong>
                    </div>

                    <div class="client-info-item">
                        <span>المدير المباشر</span>
                        <strong>{{ $member->directManager?->name ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>القسم</span>
                        <strong>{{ $member->department ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>الإيميل</span>
                        <strong>{{ $member->email ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>الموبايل</span>
                        <strong>{{ $member->mobile ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>الهاتف</span>
                        <strong>{{ $member->phone ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>تاريخ التعيين</span>
                        <strong>{{ $member->hire_date?->format('Y-m-d') ?? '-' }}</strong>
                    </div>

                    <div class="client-info-item">
                        <span>الحالة</span>
                        <strong>
                            <span class="badge {{ $member->status_badge_class }}">
                                {{ $member->status_label }}
                            </span>
                        </strong>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">حساب الدخول</h5>
                </div>

                <div class="card-body">
                    @if ($member->user)
                        <div class="client-info-item">
                            <span>الاسم</span>
                            <strong>{{ $member->user->name }}</strong>
                        </div>

                        <div class="client-info-item">
                            <span>الإيميل</span>
                            <strong>{{ $member->user->email }}</strong>
                        </div>

                        <div class="client-info-item">
                            <span>الحالة</span>
                            <strong>
                                @if ($member->user->status)
                                    <span class="badge bg-success">نشط</span>
                                @else
                                    <span class="badge bg-secondary">غير نشط</span>
                                @endif
                            </strong>
                        </div>

                        <div class="client-info-item">
                            <span>Roles</span>
                            <strong>
                                {{ $member->user->roles->pluck('name')->join(', ') ?: '-' }}
                            </strong>
                        </div>
                    @else
                        <div class="text-muted">
                            هذا العضو غير مرتبط بحساب دخول.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">ملاحظات</h5>
                </div>

                <div class="card-body">
                    <p class="text-muted mb-0">
                        {{ $member->notes ?: 'لا توجد ملاحظات' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            @if ($member->is_manager)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">الأعضاء تحت إدارته</h5>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>العضو</th>
                                        <th>الفريق</th>
                                        <th>الوظيفة</th>
                                        <th>الحالة</th>
                                        <th class="text-end">عرض</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($member->managedMembers as $managedMember)
                                        <tr>
                                            <td>{{ $managedMember->name }}</td>
                                            <td>{{ $managedMember->team?->name ?? '-' }}</td>
                                            <td>{{ $managedMember->job_title ?? '-' }}</td>
                                            <td>
                                                <span class="badge {{ $managedMember->status_badge_class }}">
                                                    {{ $managedMember->status_label }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.members.show', $managedMember) }}"
                                                    class="btn btn-sm btn-outline-dark">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                لا يوجد أعضاء تحت إدارة هذا العضو
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
            <livewire:admin.shared.related-projects type="member" :id="$member->id" />
            <livewire:admin.shared.related-tasks type="member" :id="$member->id" />
            <livewire:admin.shared.activity-timeline :subject-type="get_class($member)" :subject-id="$member->id" />
        </div>
    </div>

@endsection
