<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">بحث</label>
                    <input type="text"
                           class="form-control"
                           placeholder="اسم الفريق / الكود / Role"
                           wire:model.live.debounce.400ms="search">
                </div>

                <div class="col-md-3">
                    <label class="form-label">الحالة</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">كل الحالات</option>
                        <option value="1">نشط</option>
                        <option value="0">غير نشط</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-light w-100" wire:click="resetFilters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Reset
                    </button>
                </div>

                <div class="col-md-2">
                    @can('teams.create')
                        <a href="{{ route('admin.teams.create') }}" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i>
                            فريق جديد
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">قائمة الفرق</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive app-table-responsive">
                <table class="table table-hover align-middle app-data-table">
                    <thead>
                        <tr>
                            <th>الفريق</th>
                            <th>الكود</th>
                            <th>Role</th>
                            <th>المدير</th>
                            <th>عدد الأعضاء</th>
                            <th>الحالة</th>
                            <th>تاريخ الإضافة</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($teams as $team)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $team->name }}</div>
                                    <div class="small text-muted">
                                        {{ str($team->description)->limit(60) }}
                                    </div>
                                </td>

                                <td>
                                    <code>{{ $team->code }}</code>
                                </td>

                                <td>
                                    <code>{{ $team->role_name }}</code>
                                </td>

                                <td>{{ $team->manager?->name ?? '-' }}</td>

                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $team->members_count }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge {{ $team->status_badge_class }}">
                                        {{ $team->status_label }}
                                    </span>
                                </td>

                                <td>{{ $team->created_at->format('Y-m-d') }}</td>

                                <td class="text-end">
                                    <a href="{{ route('admin.teams.show', $team) }}"
                                       class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('teams.edit')
                                        <a href="{{ route('admin.teams.edit', $team) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('teams.delete')
                                        <form action="{{ route('admin.teams.destroy', $team) }}"
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
                                    لا توجد فرق حتى الآن
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $teams->links() }}
            </div>
        </div>
    </div>
</div>
