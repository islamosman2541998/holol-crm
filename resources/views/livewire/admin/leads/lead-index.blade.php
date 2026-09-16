<div>
    <style>
        .lead-contact-box {
            padding: 16px;
            border: 1px solid #edf0f3;
            border-radius: 16px;
            background-color: #fff;
            height: 100%;
        }
    </style>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">بحث</label>
                    <input type="text" class="form-control" placeholder="الاسم / الشركة / الموبايل / المصدر"
                        wire:model.live.debounce.400ms="search">
                </div>

                <div class="col-md-3">
                    <label class="form-label">الحالة</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">كل الحالات</option>
                        <option value="new">جديد</option>
                        <option value="contacted">تم التواصل</option>
                        <option value="qualified">مؤهل</option>
                        <option value="unqualified">غير مؤهل</option>
                        <option value="lost">مفقود</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">الموظف المسؤول</label>
                    <select class="form-select" wire:model.live="assignedTo">
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

                <div class="col-md-2">
                    <button type="button" class="btn btn-light w-100" wire:click="resetFilters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">قائمة العملاء المحتملين</h5>

            @can('leads.create')
                <a href="{{ route('admin.leads.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    إضافة Lead
                </a>
            @endcan
        </div>

        <div class="card-body">
            <div class="table-responsive app-table-responsive">
                <table class="table table-hover align-middle app-data-table">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>التواصل</th>
                            <th>المصدر</th>
                            <th>الموظف المسؤول</th>
                            <th>الحالة</th>
                            <th>تاريخ آخر متابعة</th>
                            <th>محتوى آخر متابعة</th>
                            <th>تاريخ الإضافة</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($leads as $lead)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $lead->name }}</div>
                                    <div class="small text-muted">
                                        {{ $lead->company ?? 'بدون شركة' }}
                                    </div>
                                </td>

                                <td>
                                    <div>{{ $lead->mobile ?? '-' }}</div>
                                    <div class="small text-muted">{{ $lead->email ?? '-' }}</div>
                                </td>

                                <td>{{ $lead->source ?? '-' }}</td>

                                <td>
                                    @if ($lead->assignedMember)
                                        <div>{{ $lead->assignedMember->name }}</div>
                                        <div class="small text-muted">
                                            {{ $lead->assignedMember->team?->name ?? '-' }}
                                        </div>
                                    @else
                                        {{ $lead->assignedUser?->name ?? '-' }}
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $lead->status_badge_class }}">
                                        {{ $lead->status_label }}
                                    </span>

                                    @if ($lead->convertedClient)
                                        <div class="small mt-1">
                                            <a href="{{ route('admin.clients.show', $lead->convertedClient) }}">
                                                عرض العميل
                                            </a>
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    @if ($lead->latestFollowup)
                                        {{ $lead->latestFollowup->created_at->format('Y-m-d H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td style="min-width: 260px;">
                                    @if ($lead->latestFollowup)
                                        <div class="fw-semibold small">
                                            {{ str($lead->latestFollowup->note)->limit(90) }}
                                        </div>

                                        <div class="text-muted small mt-1">
                                            النوع: {{ $lead->latestFollowup->type_label }}
                                            -
                                            بواسطة: {{ $lead->latestFollowup->user?->name ?? '-' }}
                                        </div>

                                        @if ($lead->latestFollowup->next_followup_at)
                                            <div class="text-muted small">
                                                المتابعة القادمة:
                                                {{ $lead->latestFollowup->next_followup_at->format('Y-m-d H:i') }}
                                            </div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>{{ $lead->created_at->format('Y-m-d') }}</td>

                                <td class="text-end">
                                    <a href="{{ route('admin.leads.show', $lead) }}"
                                        class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-eye"></i>
                                    </a>


                                    @can('leads.edit')
                                        <a href="{{ route('admin.leads.edit', $lead) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('leads.delete')
                                        <form action="{{ route('admin.leads.destroy', $lead) }}" method="POST"
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
                                <td colspan="9" class="text-center text-muted py-4">
                                    لا يوجد عملاء محتملين
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $leads->links() }}
            </div>
        </div>
    </div>
</div>
