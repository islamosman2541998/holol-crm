<div>
    @php
        $hasFilters = $search !== '' || $type !== '' || $status !== '' || $userId !== '' || $dateFilter !== '';

        $summary = [
            ['label' => 'متابعات اليوم', 'value' => $todayCount, 'icon' => 'bi-calendar-event'],
            ['label' => 'متأخرة', 'value' => $overdueCount, 'icon' => 'bi-exclamation-triangle', 'alert' => true],
            ['label' => 'قيد المتابعة', 'value' => $pendingCount, 'icon' => 'bi-hourglass-split'],
            ['label' => 'تمت', 'value' => $doneCount, 'icon' => 'bi-check2-all'],
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
                <h2 class="list-card-title">قائمة متابعات الـ Leads</h2>
                <p class="list-card-sub">{{ number_format($leadFollowups->total()) }} متابعة{{ $hasFilters ? ' مطابقة للتصفية' : '' }}</p>
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
                <label class="form-label" for="leadFollowupSearch">بحث</label>
                <div class="list-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="leadFollowupSearch" class="form-control" placeholder="اسم الـ Lead / الشركة / الملاحظة"
                        wire:model.live.debounce.400ms="search">
                </div>
            </div>

            <div class="list-filter">
                <label class="form-label" for="leadFollowupType">نوع المتابعة</label>
                <select id="leadFollowupType" class="form-select" wire:model.live="type">
                    <option value="">كل الأنواع</option>
                    <option value="note">ملاحظة</option>
                    <option value="call">مكالمة</option>
                    <option value="whatsapp">واتساب</option>
                    <option value="meeting">اجتماع</option>
                    <option value="email">إيميل</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="leadFollowupStatus">الحالة</label>
                <select id="leadFollowupStatus" class="form-select" wire:model.live="status">
                    <option value="">كل الحالات</option>
                    <option value="pending">قيد المتابعة</option>
                    <option value="done">تمت</option>
                    <option value="cancelled">ملغاة</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="leadFollowupUser">الموظف</label>
                <select id="leadFollowupUser" class="form-select" wire:model.live="userId">
                    <option value="">كل الموظفين</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="leadFollowupDate">التاريخ</label>
                <select id="leadFollowupDate" class="form-select" wire:model.live="dateFilter">
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
                        <th>Lead</th>
                        <th>النوع</th>
                        <th>الملاحظة</th>
                        <th>الموظف</th>
                        <th>ميعاد المتابعة</th>
                        <th>الحالة</th>
                        <th>تاريخ الإضافة</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($leadFollowups as $followup)
                        <tr wire:key="lead-followup-{{ $followup->id }}">
                            <td class="cell-title-wrap">
                                @if ($followup->lead)
                                    <a href="{{ route('admin.leads.show', $followup->lead) }}" class="cell-title"
                                        title="{{ $followup->lead->name }}">
                                        {{ $followup->lead->name }}
                                    </a>
                                    <span class="cell-sub">{{ $followup->lead->company ?? '-' }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                <span class="cell-type">
                                    <i class="bi {{ $followup->type_icon }}"></i>
                                    {{ $followup->type_label }}
                                </span>
                            </td>

                            <td style="min-width: 220px; max-width: 320px;">
                                {{ str($followup->note)->limit(80) }}
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
                                    @can('leads.edit')
                                        @if ($followup->status !== 'done')
                                            <button type="button" class="row-action is-success"
                                                wire:click="markAsDone({{ $followup->id }})"
                                                title="تحديد كمكتملة" aria-label="تحديد كمكتملة">
                                                <i class="bi bi-check2"></i>
                                            </button>
                                        @endif

                                        <button type="button" class="row-action is-danger"
                                            onclick="confirmDeleteLeadFollowupFromIndex({{ $followup->id }})"
                                            title="حذف المتابعة" aria-label="حذف المتابعة">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
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

        @if ($leadFollowups->hasPages())
            <div class="list-card-foot">
                {{ $leadFollowups->links() }}
            </div>
        @endif
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
