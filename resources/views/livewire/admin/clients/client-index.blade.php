<div>
    @php
        $hasFilters = $search !== '' || $status !== '' || $assignedTo !== '';
    @endphp

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">قائمة العملاء</h2>
                <p class="list-card-sub">{{ number_format($clients->total()) }} عميل{{ $hasFilters ? ' مطابق للتصفية' : '' }}</p>
            </div>

            <div class="list-card-actions">
                @if ($hasFilters)
                    <button type="button" class="btn btn-light" wire:click="resetFilters">
                        <i class="bi bi-x-lg"></i>
                        مسح التصفية
                    </button>
                @endif

                @can('clients.create')
                    <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        إضافة عميل
                    </a>
                @endcan
            </div>
        </header>

        <div class="list-filters">
            <div class="list-filter list-filter-search">
                <label class="form-label" for="clientSearch">بحث</label>
                <div class="list-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="clientSearch" class="form-control" placeholder="اسم العميل / الشركة / الموبايل"
                        wire:model.live.debounce.400ms="search">
                </div>
            </div>

            <div class="list-filter">
                <label class="form-label" for="clientStatus">الحالة</label>
                <select id="clientStatus" class="form-select" wire:model.live="status">
                    <option value="">كل الحالات</option>
                    <option value="new">جديد</option>
                    <option value="active">نشط</option>
                    <option value="inactive">غير نشط</option>
                    <option value="lost">مفقود</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="clientAssigned">الموظف المسؤول</label>
                <select id="clientAssigned" class="form-select" wire:model.live="assignedTo">
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
                        <th>العميل</th>
                        <th>التواصل</th>
                        <th>الموظف المسؤول</th>
                        <th>الحالة</th>
                        <th>آخر متابعة</th>
                        <th>التاريخ</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($clients as $client)
                        <tr wire:key="client-{{ $client->id }}">
                            <td class="cell-title-wrap">
                                <a href="{{ route('admin.clients.show', $client) }}" class="cell-title" title="{{ $client->name }}">
                                    {{ $client->name }}
                                </a>
                                <span class="cell-sub">{{ $client->company ?? 'بدون شركة' }}</span>
                            </td>

                            <td class="text-nowrap">
                                <span class="cell-date">{{ $client->mobile ?? ($client->phone ?? '-') }}</span>
                                <span class="cell-sub">{{ $client->email ?? '-' }}</span>
                            </td>

                            <td>
                                @if ($client->assignedMember)
                                    <div class="cell-person">
                                        <span class="cell-person-avatar">{{ mb_substr($client->assignedMember->name, 0, 1) }}</span>
                                        <div class="cell-person-text">
                                            <span class="cell-person-name">{{ $client->assignedMember->name }}</span>
                                            <span class="cell-person-sub">{{ $client->assignedMember->team?->name ?? '-' }}</span>
                                        </div>
                                    </div>
                                @else
                                    {{ $client->assignedUser?->name ?? '-' }}
                                @endif
                            </td>

                            <td>
                                <span class="badge {{ $client->status_badge_class }}">{{ $client->status_label }}</span>
                            </td>

                            <td style="min-width: 260px;">
                                @if ($client->latestFollowup)
                                    <div class="fw-semibold small">{{ str($client->latestFollowup->note)->limit(90) }}</div>
                                    <span class="cell-sub">
                                        {{ $client->latestFollowup->created_at->format('Y-m-d H:i') }}
                                        · بواسطة {{ $client->latestFollowup->user?->name ?? '-' }}
                                    </span>
                                    @if ($client->latestFollowup->next_followup_at)
                                        <span class="cell-sub">
                                            المتابعة القادمة: {{ $client->latestFollowup->next_followup_at->format('Y-m-d H:i') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="cell-date-wrap">
                                <span class="cell-date">{{ $client->created_at->format('Y-m-d') }}</span>
                            </td>

                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.clients.show', $client) }}" class="row-action is-view"
                                        title="عرض العميل" aria-label="عرض العميل">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('clients.edit')
                                        <a href="{{ route('admin.clients.edit', $client) }}" class="row-action is-edit"
                                            title="تعديل العميل" aria-label="تعديل العميل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('clients.delete')
                                        <form action="{{ route('admin.clients.destroy', $client) }}" method="POST"
                                            class="js-delete-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="row-action is-danger" title="حذف العميل"
                                                aria-label="حذف العميل">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="list-empty">
                                    <i class="bi bi-inbox"></i>
                                    <div>{{ $hasFilters ? 'لا يوجد عملاء مطابقين للتصفية' : 'لا يوجد عملاء حتى الآن' }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($clients->hasPages())
            <div class="list-card-foot">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</div>
