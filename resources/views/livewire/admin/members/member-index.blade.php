<div>
    @php
        $hasFilters = $search !== '' || $teamId !== '' || $status !== '' || $isManager !== '';
    @endphp

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">قائمة الأعضاء</h2>
                <p class="list-card-sub">{{ number_format($members->total()) }} عضو{{ $hasFilters ? ' مطابق للتصفية' : '' }}</p>
            </div>

            <div class="list-card-actions">
                @if ($hasFilters)
                    <button type="button" class="btn btn-light" wire:click="resetFilters">
                        <i class="bi bi-x-lg"></i>
                        مسح التصفية
                    </button>
                @endif

                @can('members.create')
                    <a href="{{ route('admin.members.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        عضو جديد
                    </a>
                @endcan
            </div>
        </header>

        <div class="list-filters">
            <div class="list-filter list-filter-search">
                <label class="form-label" for="memberSearch">بحث</label>
                <div class="list-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="memberSearch" class="form-control" placeholder="الاسم / الوظيفة / الموبايل"
                        wire:model.live.debounce.400ms="search">
                </div>
            </div>

            <div class="list-filter">
                <label class="form-label" for="memberTeam">الفريق</label>
                <select id="memberTeam" class="form-select" wire:model.live="teamId">
                    <option value="">كل الفرق</option>
                    @foreach ($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="memberStatus">الحالة</label>
                <select id="memberStatus" class="form-select" wire:model.live="status">
                    <option value="">كل الحالات</option>
                    <option value="active">نشط</option>
                    <option value="inactive">غير نشط</option>
                    <option value="on_leave">إجازة</option>
                    <option value="left">ترك العمل</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="memberManager">المديرين</label>
                <select id="memberManager" class="form-select" wire:model.live="isManager">
                    <option value="">الكل</option>
                    <option value="1">مدير فقط</option>
                    <option value="0">غير مدير</option>
                </select>
            </div>
        </div>

        <div class="table-responsive data-table-wrap">
            <table class="table align-middle mb-0 data-table has-actions">
                <thead>
                    <tr>
                        <th>العضو</th>
                        <th>الفريق</th>
                        <th>الوظيفة</th>
                        <th>المدير المباشر</th>
                        <th>حساب الدخول</th>
                        <th>الحالة</th>
                        <th>تاريخ التعيين</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($members as $member)
                        <tr wire:key="member-{{ $member->id }}">
                            <td>
                                <div class="cell-person">
                                    <span class="cell-person-avatar is-lg">
                                        @if ($member->image)
                                            <img src="{{ asset('storage/' . $member->image) }}" alt="{{ $member->name }}">
                                        @else
                                            {{ mb_substr($member->name, 0, 1) }}
                                        @endif
                                    </span>
                                    <div class="cell-person-text">
                                        <a href="{{ route('admin.members.show', $member) }}" class="cell-title">
                                            {{ $member->name }}
                                            @if ($member->is_manager)
                                                <span class="badge bg-primary ms-1">مدير</span>
                                            @endif
                                        </a>
                                        <span class="cell-person-sub">{{ $member->mobile ?? $member->phone ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="text-nowrap">
                                @if ($member->team)
                                    <a href="{{ route('admin.teams.show', $member->team) }}" class="cell-link">
                                        {{ $member->team->name }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>
                                <span class="cell-date">{{ $member->job_title ?? '-' }}</span>
                                <span class="cell-sub">{{ $member->department ?? '-' }}</span>
                            </td>

                            <td class="text-nowrap">{{ $member->directManager?->name ?? '-' }}</td>

                            <td>
                                @if ($member->user)
                                    <span class="cell-date">{{ $member->user->name }}</span>
                                    <span class="cell-sub">{{ $member->user->email }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge {{ $member->status_badge_class }}">{{ $member->status_label }}</span>
                            </td>

                            <td class="cell-date-wrap">
                                <span class="cell-date">{{ $member->hire_date?->format('Y-m-d') ?? '-' }}</span>
                            </td>

                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.members.show', $member) }}" class="row-action is-view"
                                        title="عرض العضو" aria-label="عرض العضو">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('members.edit')
                                        <a href="{{ route('admin.members.edit', $member) }}" class="row-action is-edit"
                                            title="تعديل العضو" aria-label="تعديل العضو">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('members.delete')
                                        <form action="{{ route('admin.members.destroy', $member) }}" method="POST"
                                            class="js-delete-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="row-action is-danger" title="حذف العضو"
                                                aria-label="حذف العضو">
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
                                    <div>{{ $hasFilters ? 'لا يوجد أعضاء مطابقين للتصفية' : 'لا يوجد أعضاء حتى الآن' }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($members->hasPages())
            <div class="list-card-foot">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</div>
