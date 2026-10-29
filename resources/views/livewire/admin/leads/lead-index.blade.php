<div>
    @php
        $hasFilters = $search !== '' || $status !== '' || $assignedTo !== '';
    @endphp

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">قائمة العملاء المحتملين</h2>
                <p class="list-card-sub">{{ number_format($leads->total()) }} Lead{{ $hasFilters ? ' مطابق للتصفية' : '' }}</p>
            </div>

            <div class="list-card-actions">
                @if ($hasFilters)
                    <button type="button" class="btn btn-light" wire:click="resetFilters">
                        <i class="bi bi-x-lg"></i>
                        مسح التصفية
                    </button>
                @endif

                @can('leads.create')
                    <a href="{{ route('admin.leads.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        إضافة Lead
                    </a>
                @endcan
            </div>
        </header>

        <div class="list-filters">
            <div class="list-filter list-filter-search">
                <label class="form-label" for="leadSearch">بحث</label>
                <div class="list-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="leadSearch" class="form-control" placeholder="الاسم / الشركة / الموبايل / المصدر"
                        wire:model.live.debounce.400ms="search">
                </div>
            </div>

            <div class="list-filter">
                <label class="form-label" for="leadStatus">الحالة</label>
                <select id="leadStatus" class="form-select" wire:model.live="status">
                    <option value="">كل الحالات</option>
                    <option value="new">جديد</option>
                    <option value="contacted">تم التواصل</option>
                    <option value="qualified">مؤهل</option>
                    <option value="unqualified">غير مؤهل</option>
                    <option value="lost">مفقود</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="leadAssigned">الموظف المسؤول</label>
                <select id="leadAssigned" class="form-select" wire:model.live="assignedTo">
                    <option value="">كل الموظفين</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->user_id }}">
                            {{ $member->name }}
                            @if ($member->team)
                                - {{ $member->team->name }}
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive data-table-wrap">
            <table class="table align-middle mb-0 data-table has-actions">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>التواصل</th>
                        <th>المصدر</th>
                        <th>الموظف المسؤول</th>
                        <th>الحالة</th>
                        <th>آخر متابعة</th>
                        <th>تاريخ الإضافة</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($leads as $lead)
                        <tr wire:key="lead-{{ $lead->id }}">
                            <td class="cell-title-wrap">
                                <a href="{{ route('admin.leads.show', $lead) }}" class="cell-title" title="{{ $lead->name }}">
                                    {{ $lead->name }}
                                </a>
                                <span class="cell-sub">{{ $lead->company ?? 'بدون شركة' }}</span>
                            </td>

                            <td class="text-nowrap">
                                <span class="cell-date">{{ $lead->mobile ?? '-' }}</span>
                                <span class="cell-sub">{{ $lead->email ?? '-' }}</span>
                            </td>

                            <td>{{ $lead->source ?? '-' }}</td>

                            <td>
                                @if ($lead->assignedMember)
                                    <div class="cell-person">
                                        <span class="cell-person-avatar">{{ mb_substr($lead->assignedMember->name, 0, 1) }}</span>
                                        <div class="cell-person-text">
                                            <span class="cell-person-name">{{ $lead->assignedMember->name }}</span>
                                            <span class="cell-person-sub">{{ $lead->assignedMember->team?->name ?? '-' }}</span>
                                        </div>
                                    </div>
                                @else
                                    {{ $lead->assignedUser?->name ?? '-' }}
                                @endif
                            </td>

                            <td class="text-nowrap">
                                <span class="badge {{ $lead->status_badge_class }}">{{ $lead->status_label }}</span>

                                @if ($lead->convertedClient)
                                    <a href="{{ route('admin.clients.show', $lead->convertedClient) }}" class="cell-sub-link">
                                        عرض العميل
                                    </a>
                                @endif
                            </td>

                            <td style="min-width: 260px;">
                                @if ($lead->latestFollowup)
                                    <div class="fw-semibold small">{{ str($lead->latestFollowup->note)->limit(90) }}</div>
                                    <span class="cell-sub">
                                        {{ $lead->latestFollowup->created_at->format('Y-m-d H:i') }}
                                        · {{ $lead->latestFollowup->type_label }}
                                        · بواسطة {{ $lead->latestFollowup->user?->name ?? '-' }}
                                    </span>
                                    @if ($lead->latestFollowup->next_followup_at)
                                        <span class="cell-sub">
                                            المتابعة القادمة: {{ $lead->latestFollowup->next_followup_at->format('Y-m-d H:i') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="cell-date-wrap">
                                <span class="cell-date">{{ $lead->created_at->format('Y-m-d') }}</span>
                            </td>

                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.leads.show', $lead) }}" class="row-action is-view"
                                        title="عرض Lead" aria-label="عرض Lead">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('leads.edit')
                                        <a href="{{ route('admin.leads.edit', $lead) }}" class="row-action is-edit"
                                            title="تعديل Lead" aria-label="تعديل Lead">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('leads.delete')
                                        <form action="{{ route('admin.leads.destroy', $lead) }}" method="POST"
                                            class="js-delete-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="row-action is-danger" title="حذف Lead"
                                                aria-label="حذف Lead">
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
                                    <div>{{ $hasFilters ? 'لا يوجد عملاء محتملين مطابقين للتصفية' : 'لا يوجد عملاء محتملين حتى الآن' }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($leads->hasPages())
            <div class="list-card-foot">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
</div>
