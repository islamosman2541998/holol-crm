<div>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-title">كل المتابعات</div>
                    <div class="stat-value">{{ \App\Models\ClientFollowup::count() }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-title">متابعات اليوم</div>
                    <div class="stat-value">
                        {{ \App\Models\ClientFollowup::whereDate('next_followup_at', today())->count() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-title">متأخرة</div>
                    <div class="stat-value">
                        {{ \App\Models\ClientFollowup::where('status', 'pending')->whereNotNull('next_followup_at')->where('next_followup_at', '<', now())->count() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="stat-title">تمت</div>
                    <div class="stat-value">
                        {{ \App\Models\ClientFollowup::where('status', 'done')->count() }}
                    </div>
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
                           placeholder="اسم العميل / الشركة / الملاحظة"
                           wire:model.live.debounce.400ms="search">
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

                <div class="col-md-3">
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
        <div class="card-header bg-white">
            <h5 class="mb-0">قائمة المتابعات</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive app-table-responsive">
                <table class="table table-hover align-middle app-data-table">
                    <thead>
                        <tr>
                            <th>العميل</th>
                            <th>الملاحظة</th>
                            <th>الموظف</th>
                            <th>ميعاد المتابعة</th>
                            <th>الحالة</th>
                            <th>تاريخ الإضافة</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($followups as $followup)
                            <tr>
                                <td>
                                    @if ($followup->client)
                                        <a href="{{ route('admin.clients.show', $followup->client) }}"
                                           class="fw-semibold text-decoration-none">
                                            {{ $followup->client->name }}
                                        </a>
                                        <div class="small text-muted">
                                            {{ $followup->client->company ?? '-' }}
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td style="max-width: 320px;">
                                    {{ $followup->note }}
                                </td>

                                <td>{{ $followup->user?->name ?? '-' }}</td>

                                <td>
                                    @if ($followup->next_followup_at)
                                        {{ $followup->next_followup_at->format('Y-m-d H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    <span class="badge {{ $followup->status_badge_class }}">
                                        {{ $followup->status_label }}
                                    </span>
                                </td>

                                <td>{{ $followup->created_at->format('Y-m-d H:i') }}</td>

                                <td class="text-end">
                                    @can('followups.edit')
                                        @if ($followup->status !== 'done')
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-success"
                                                    wire:click="markAsDone({{ $followup->id }})">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        @endif
                                    @endcan

                                    @can('followups.delete')
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="confirmDeleteFollowupFromIndex({{ $followup->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    لا توجد متابعات
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $followups->links() }}
            </div>
        </div>
    </div>

    <script>
        function confirmDeleteFollowupFromIndex(id) {
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
