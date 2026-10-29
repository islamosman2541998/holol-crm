<div>
    @php
        $hasFilters = $search !== '' || $status !== '';
    @endphp

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">قائمة الفرق</h2>
                <p class="list-card-sub">{{ number_format($teams->total()) }} فريق{{ $hasFilters ? ' مطابق للتصفية' : '' }}</p>
            </div>

            <div class="list-card-actions">
                @if ($hasFilters)
                    <button type="button" class="btn btn-light" wire:click="resetFilters">
                        <i class="bi bi-x-lg"></i>
                        مسح التصفية
                    </button>
                @endif

                @can('teams.create')
                    <a href="{{ route('admin.teams.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        فريق جديد
                    </a>
                @endcan
            </div>
        </header>

        <div class="list-filters">
            <div class="list-filter list-filter-search">
                <label class="form-label" for="teamSearch">بحث</label>
                <div class="list-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="teamSearch" class="form-control" placeholder="اسم الفريق / الكود / Role"
                        wire:model.live.debounce.400ms="search">
                </div>
            </div>

            <div class="list-filter">
                <label class="form-label" for="teamStatus">الحالة</label>
                <select id="teamStatus" class="form-select" wire:model.live="status">
                    <option value="">كل الحالات</option>
                    <option value="1">نشط</option>
                    <option value="0">غير نشط</option>
                </select>
            </div>
        </div>

        <div class="table-responsive data-table-wrap">
            <table class="table align-middle mb-0 data-table has-actions">
                <thead>
                    <tr>
                        <th>الفريق</th>
                        <th>الكود</th>
                        <th>Role</th>
                        <th>المدير</th>
                        <th>عدد الأعضاء</th>
                        <th>الحالة</th>
                        <th>تاريخ الإضافة</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($teams as $team)
                        <tr wire:key="team-{{ $team->id }}">
                            <td style="min-width: 220px;">
                                <a href="{{ route('admin.teams.show', $team) }}" class="cell-title">{{ $team->name }}</a>
                                <span class="cell-sub">{{ str($team->description)->limit(60) }}</span>
                            </td>

                            <td><span class="cell-type">{{ $team->code }}</span></td>

                            <td><span class="cell-type">{{ $team->role_name }}</span></td>

                            <td class="text-nowrap">{{ $team->manager?->name ?? '-' }}</td>

                            <td>
                                <span class="badge bg-light text-dark border">{{ $team->members_count }}</span>
                            </td>

                            <td>
                                <span class="badge {{ $team->status_badge_class }}">{{ $team->status_label }}</span>
                            </td>

                            <td class="cell-date-wrap">
                                <span class="cell-date">{{ $team->created_at->format('Y-m-d') }}</span>
                            </td>

                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.teams.show', $team) }}" class="row-action is-view"
                                        title="عرض الفريق" aria-label="عرض الفريق">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('teams.edit')
                                        <a href="{{ route('admin.teams.edit', $team) }}" class="row-action is-edit"
                                            title="تعديل الفريق" aria-label="تعديل الفريق">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('teams.delete')
                                        <form action="{{ route('admin.teams.destroy', $team) }}" method="POST"
                                            class="js-delete-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="row-action is-danger" title="حذف الفريق"
                                                aria-label="حذف الفريق">
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
                                    <div>{{ $hasFilters ? 'لا توجد فرق مطابقة للتصفية' : 'لا توجد فرق حتى الآن' }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($teams->hasPages())
            <div class="list-card-foot">
                {{ $teams->links() }}
            </div>
        @endif
    </div>
</div>
