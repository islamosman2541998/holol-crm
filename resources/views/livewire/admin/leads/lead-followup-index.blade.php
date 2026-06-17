<div>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-title">متابعات اليوم</div>
                    <div class="stat-value">{{ $todayCount }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-title">متأخرة</div>
                    <div class="stat-value">{{ $overdueCount }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-title">قيد المتابعة</div>
                    <div class="stat-value">{{ $pendingCount }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-title">تمت</div>
                    <div class="stat-value">{{ $doneCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">بحث</label>
                    <input type="text"
                           class="form-control"
                           placeholder="اسم الـ Lead / الشركة / الملاحظة"
                           wire:model.live.debounce.400ms="search">
                </div>

                <div class="col-md-2">
                    <label class="form-label">نوع المتابعة</label>
                    <select class="form-select" wire:model.live="type">
                        <option value="">كل الأنواع</option>
                        <option value="note">ملاحظة</option>
                        <option value="call">مكالمة</option>
                        <option value="whatsapp">واتساب</option>
                        <option value="meeting">اجتماع</option>
                        <option value="email">إيميل</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">الحالة</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">كل الحالات</option>
                        <option value="pending">قيد المتابعة</option>
                        <option value="done">تمت</option>
                        <option value="cancelled">ملغاة</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">الموظف</label>
                    <select class="form-select" wire:model.live="userId">
                        <option value="">كل الموظفين</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">التاريخ</label>
                    <select class="form-select" wire:model.live="dateFilter">
                        <option value="">الكل</option>
                        <option value="today">اليوم</option>
                        <option value="overdue">متأخرة</option>
                        <option value="upcoming">قادمة</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <button type="button" class="btn btn-light w-100" wire:click="resetFilters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">قائمة متابعات الـ Leads</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Lead</th>
                            <th>النوع</th>
                            <th>الملاحظة</th>
                            <th>الموظف</th>
                            <th>ميعاد المتابعة</th>
                            <th>الحالة</th>
                            <th>تاريخ الإضافة</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($leadFollowups as $followup)
                            <tr>
                                <td>
                                    @if ($followup->lead)
                                        <a href="{{ route('admin.leads.show', $followup->lead) }}"
                                           class="fw-semibold text-decoration-none">
                                            {{ $followup->lead->name }}
                                        </a>

                                        <div class="small text-muted">
                                            {{ $followup->lead->company ?? '-' }}
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark">
                                        <i class="bi {{ $followup->type_icon }}"></i>
                                        {{ $followup->type_label }}
                                    </span>
                                </td>

                                <td style="max-width: 300px;">
                                    {{ str($followup->note)->limit(80) }}
                                </td>

                                <td>{{ $followup->user?->name ?? '-' }}</td>

                                <td>
                                    {{ $followup->next_followup_at?->format('Y-m-d H:i') ?? '-' }}
                                </td>

                                <td>
                                    <span class="badge {{ $followup->status_badge_class }}">
                                        {{ $followup->status_label }}
                                    </span>
                                </td>

                                <td>{{ $followup->created_at->format('Y-m-d H:i') }}</td>

                                <td class="text-end">
                                    @can('leads.edit')
                                        @if ($followup->status !== 'done')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success"
                                                    wire:click="markAsDone({{ $followup->id }})">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        @endif

                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="confirmDeleteLeadFollowupFromIndex({{ $followup->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    لا توجد متابعات
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $leadFollowups->links() }}
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteLeadFollowupFromIndex(id) {
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: 'سيتم حذف المتابعة',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('delete', id);
                }
            });
        }
    </script>
</div>