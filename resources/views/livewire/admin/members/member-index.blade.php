<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">بحث</label>
                    <input type="text"
                           class="form-control"
                           placeholder="الاسم / الوظيفة / الموبايل"
                           wire:model.live.debounce.400ms="search">
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

                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">كل الحالات</option>
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>
                        <option value="on_leave">إجازة</option>
                        <option value="left">ترك العمل</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">المديرين</label>
                    <select class="form-select" wire:model.live="isManager">
                        <option value="">الكل</option>
                        <option value="1">مدير فقط</option>
                        <option value="0">غير مدير</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <button type="button" class="btn btn-light w-100" wire:click="resetFilters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>

                <div class="col-md-2">
                    @can('members.create')
                        <a href="{{ route('admin.members.create') }}" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i>
                            عضو جديد
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">قائمة الأعضاء</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>العضو</th>
                            <th>الفريق</th>
                            <th>الوظيفة</th>
                            <th>المدير المباشر</th>
                            <th>حساب الدخول</th>
                            <th>الحالة</th>
                            <th>تاريخ التعيين</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($members as $member)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="member-avatar">
                                            @if ($member->image)
                                                <img src="{{ asset('storage/' . $member->image) }}"
                                                     alt="{{ $member->name }}">
                                            @endif
                                        </div>

                                        <div>
                                            <div class="fw-semibold">
                                                {{ $member->name }}

                                                @if ($member->is_manager)
                                                    <span class="badge bg-primary">مدير</span>
                                                @endif
                                            </div>

                                            <div class="small text-muted">
                                                {{ $member->mobile ?? $member->phone ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    @if ($member->team)
                                        <a href="{{ route('admin.teams.show', $member->team) }}"
                                           class="text-decoration-none">
                                            {{ $member->team->name }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    <div>{{ $member->job_title ?? '-' }}</div>
                                    <div class="small text-muted">
                                        {{ $member->department ?? '-' }}
                                    </div>
                                </td>

                                <td>{{ $member->directManager?->name ?? '-' }}</td>

                                <td>
                                    @if ($member->user)
                                        <div>{{ $member->user->name }}</div>
                                        <div class="small text-muted">{{ $member->user->email }}</div>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    <span class="badge {{ $member->status_badge_class }}">
                                        {{ $member->status_label }}
                                    </span>
                                </td>

                                <td>{{ $member->hire_date?->format('Y-m-d') ?? '-' }}</td>

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

                                    @can('members.delete')
                                        <form action="{{ route('admin.members.destroy', $member) }}"
                                              method="POST"
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
                                    لا يوجد أعضاء حتى الآن
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $members->links() }}
            </div>
        </div>
    </div>
</div>