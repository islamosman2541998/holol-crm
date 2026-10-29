<div>
    @php
        $hasFilters = $search !== '' || $status !== '' || $userId !== '' || $dateFilter !== '';

        $overdueCount = \App\Models\ClientFollowup::where('status', 'pending')->whereNotNull('next_followup_at')->where('next_followup_at', '<', now())->count();

        $summary = [
            ['label' => 'كل المتابعات', 'value' => \App\Models\ClientFollowup::count(), 'icon' => 'bi-chat-left-text'],
            ['label' => 'متابعات اليوم', 'value' => \App\Models\ClientFollowup::whereDate('next_followup_at', today())->count(), 'icon' => 'bi-calendar-event'],
            ['label' => 'متأخرة', 'value' => $overdueCount, 'icon' => 'bi-exclamation-triangle', 'alert' => true],
            ['label' => 'تمت', 'value' => \App\Models\ClientFollowup::where('status', 'done')->count(), 'icon' => 'bi-check2-all'],
        ];
    @endphp

    <section class="stat-section stat-tone-followups">
        <div class="row g-4">
            @foreach ($summary as $card)
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card card border-0 shadow-sm {{ ! empty($card['alert']) ? 'is-alert' : '' }} {{ ! empty($card['alert']) && $card['value'] > 0 ? 'has-value' : '' }}">
                        <div class="card-body">
                            <div class="stat-icon"><i class="bi {{ $card['icon'] }}"></i></div>
                            <div class="stat-title">{{ $card['label'] }}</div>
                            <div class="stat-value">{{ number_format($card['value']) }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">قائمة المتابعات</h2>
                <p class="list-card-sub">{{ number_format($followups->total()) }} متابعة{{ $hasFilters ? ' مطابقة للتصفية' : '' }}</p>
            </div>

            @if ($hasFilters)
                <div class="list-card-actions">
                    <button type="button" class="btn btn-light" wire:click="resetFilters">
                        <i class="bi bi-x-lg"></i>
                        مسح التصفية
                    </button>
                </div>
            @endif
        </header>

        <div class="list-filters">
            <div class="list-filter list-filter-search">
                <label class="form-label" for="followupSearch">بحث</label>
                <div class="list-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="followupSearch" class="form-control" placeholder="اسم العميل / الشركة / الملاحظة"
                        wire:model.live.debounce.400ms="search">
                </div>
            </div>

            <div class="list-filter">
                <label class="form-label" for="followupStatus">الحالة</label>
                <select id="followupStatus" class="form-select" wire:model.live="status">
                    <option value="">كل الحالات</option>
                    <option value="pending">قيد المتابعة</option>
                    <option value="done">تمت</option>
                    <option value="cancelled">ملغاة</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="followupUser">الموظف</label>
                <select id="followupUser" class="form-select" wire:model.live="userId">
                    <option value="">كل الموظفين</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="followupDate">التاريخ</label>
                <select id="followupDate" class="form-select" wire:model.live="dateFilter">
                    <option value="">الكل</option>
                    <option value="today">اليوم</option>
                    <option value="overdue">متأخرة</option>
                    <option value="upcoming">قادمة</option>
                </select>
            </div>
        </div>

        <div class="table-responsive data-table-wrap">
            <table class="table align-middle mb-0 data-table has-actions">
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>الملاحظة</th>
                        <th>الموظف</th>
                        <th>ميعاد المتابعة</th>
                        <th>الحالة</th>
                        <th>تاريخ الإضافة</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($followups as $followup)
                        <tr wire:key="followup-{{ $followup->id }}">
                            <td class="cell-title-wrap">
                                @if ($followup->client)
                                    <a href="{{ route('admin.clients.show', $followup->client) }}" class="cell-title"
                                        title="{{ $followup->client->name }}">
                                        {{ $followup->client->name }}
                                    </a>
                                    <span class="cell-sub">{{ $followup->client->company ?? '-' }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td style="min-width: 240px; max-width: 360px;">
                                {{ $followup->note }}
                            </td>

                            <td class="text-nowrap">{{ $followup->user?->name ?? '-' }}</td>

                            <td class="cell-date-wrap">
                                @if ($followup->next_followup_at)
                                    <span class="cell-date">{{ $followup->next_followup_at->format('Y-m-d') }}</span>
                                    <span class="cell-time">{{ $followup->next_followup_at->format('H:i') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge {{ $followup->status_badge_class }}">{{ $followup->status_label }}</span>
                            </td>

                            <td class="cell-date-wrap">
                                <span class="cell-date">{{ $followup->created_at->format('Y-m-d') }}</span>
                                <span class="cell-time">{{ $followup->created_at->format('H:i') }}</span>
                            </td>

                            <td>
                                <div class="row-actions">
                                    @can('followups.edit')
                                        @if ($followup->status !== 'done')
                                            <button type="button" class="row-action is-success"
                                                wire:click="markAsDone({{ $followup->id }})"
                                                title="تحديد كمكتملة" aria-label="تحديد كمكتملة">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        @endif
                                    @endcan

                                    @can('followups.delete')
                                        <button type="button" class="row-action is-danger"
                                            onclick="confirmDeleteFollowupFromIndex({{ $followup->id }})"
                                            title="حذف المتابعة" aria-label="حذف المتابعة">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="list-empty">
                                    <i class="bi bi-inbox"></i>
                                    <div>{{ $hasFilters ? 'لا توجد متابعات مطابقة للتصفية' : 'لا توجد متابعات حتى الآن' }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($followups->hasPages())
            <div class="list-card-foot">
                {{ $followups->links() }}
            </div>
        @endif
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
