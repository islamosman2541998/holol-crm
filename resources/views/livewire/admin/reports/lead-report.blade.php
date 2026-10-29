<div>
    <section class="stat-section stat-tone-leads">
        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-person-lines-fill"></i></div>
                        <div class="stat-title">عدد Leads</div>
                        <div class="stat-value">{{ number_format($stats['leads_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-person-plus"></i></div>
                        <div class="stat-title">Leads جديدة</div>
                        <div class="stat-value">{{ number_format($stats['new_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-check2-circle"></i></div>
                        <div class="stat-title">Leads مؤهلة</div>
                        <div class="stat-value">{{ number_format($stats['qualified_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-arrow-repeat"></i></div>
                        <div class="stat-title">تم تحويلها</div>
                        <div class="stat-value">{{ number_format($stats['converted_count']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-chat-left"></i></div>
                        <div class="stat-title">بدون متابعة</div>
                        <div class="stat-value">{{ number_format($stats['without_followups']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card card border-0 shadow-sm is-alert {{ ($stats['overdue_followups']) > 0 ? 'has-value' : '' }}">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                        <div class="stat-title">متابعات متأخرة</div>
                        <div class="stat-value">{{ number_format($stats['overdue_followups']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
                        <div class="stat-title">Conversion Rate</div>
                        <div class="stat-value">{{ $stats['conversion_rate'] }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">نتائج التقرير</h2>
                <p class="list-card-sub">{{ number_format($leads->total()) }} Lead</p>
            </div>

            <div class="list-card-actions">
                <button type="button"
                        class="btn btn-light"
                        wire:click="resetFilters">
                    <i class="bi bi-x-lg"></i>
                    مسح التصفية
                </button>

                <button type="button"
                        class="btn btn-success"
                        wire:click="exportExcel"
                        wire:loading.attr="disabled">
                    <i class="bi bi-file-earmark-excel"></i>
                    تصدير Excel حسب الفلتر
                </button>
            </div>
        </header>

        <div class="list-filters">
            <div class="list-filter list-filter-search">
                <label class="form-label">بحث</label>
                <div class="list-search">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" placeholder="اسم / شركة / موبايل / إيميل" wire:model.live.debounce.400ms="search">
            </div>
            </div>

            <div class="list-filter">
                <label class="form-label">الحالة</label>
                <select class="form-select" wire:model.live="status">
                    <option value="">كل الحالات</option>
                    <option value="new">جديد</option>
                    <option value="contacted">تم التواصل</option>
                    <option value="qualified">مؤهل</option>
                    <option value="unqualified">غير مؤهل</option>
                    <option value="converted">تم تحويله</option>
                    <option value="lost">مفقود</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label">المصدر</label>
                <select class="form-select" wire:model.live="source">
                    <option value="">كل المصادر</option>
                    @foreach ($sources as $sourceItem)
                        <option value="{{ $sourceItem }}">{{ $sourceItem }}</option>
                    @endforeach
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label">المدينة</label>
                <select class="form-select" wire:model.live="city">
                    <option value="">كل المدن</option>
                    @foreach ($cities as $cityItem)
                        <option value="{{ $cityItem }}">{{ $cityItem }}</option>
                    @endforeach
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label">الموظف المسؤول</label>
                <select class="form-select" wire:model.live="assignedTo">
                    <option value="">الكل</option>
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

            <div class="list-filter">
                <label class="form-label">من تاريخ</label>
                <input type="date" class="form-control" wire:model.live="dateFrom">
            </div>

            <div class="list-filter">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" class="form-control" wire:model.live="dateTo">
            </div>

            <div class="list-filter">
                <label class="form-label">المتابعات</label>
                <select class="form-select" wire:model.live="followupState">
                    <option value="">كل Leads</option>
                    <option value="with">لديهم متابعات</option>
                    <option value="without">بدون متابعات</option>
                    <option value="today">متابعات اليوم</option>
                    <option value="overdue">متابعات متأخرة</option>
                    <option value="upcoming">متابعات قادمة</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label">التحويل</label>
                <select class="form-select" wire:model.live="conversionState">
                    <option value="">الكل</option>
                    <option value="converted">تم تحويلها لعملاء</option>
                    <option value="not_converted">لم تتحول بعد</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label">المهام</label>
                <select class="form-select" wire:model.live="taskState">
                    <option value="">الكل</option>
                    <option value="with">لديها مهام</option>
                    <option value="without">بدون مهام</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label">المحذوف / الأرشيف</label>
                <select class="form-select" wire:model.live="trashedState">
                    <option value="without">بدون المحذوف</option>
                    <option value="with">مع المحذوف</option>
                    <option value="only">المحذوف فقط</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label">عرض</label>
                <select class="form-select" wire:model.live="perPage">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <div class="table-responsive data-table-wrap">
            <table class="table align-middle mb-0 data-table has-actions">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>التواصل</th>
                        <th>الحالة</th>
                        <th>المسؤول</th>
                        <th>المصدر / المدينة</th>
                        <th>آخر متابعة</th>
                        <th>المهام</th>
                        <th>التحويل</th>
                        <th>تاريخ الإضافة</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($leads as $lead)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $lead->name }}</div>
                                <div class="small text-muted">{{ $lead->company ?? '-' }}</div>
                                <div class="small text-muted">{{ $lead->email ?? '-' }}</div>

                                @if ($lead->trashed())
                                    <span class="badge bg-secondary mt-1">محذوف</span>
                                @endif
                            </td>

                            <td>
                                <div>موبايل: {{ $this->formatPhone($lead->mobile) }}</div>
                                <div class="small text-muted">هاتف: {{ $this->formatPhone($lead->phone) }}</div>
                            </td>

                            <td>
                                <span class="badge {{ $lead->status_badge_class }}">
                                    {{ $lead->status_label }}
                                </span>
                            </td>

                            <td>
                                {{ $lead->assignedMember?->name ?? $lead->assignedUser?->name ?? '-' }}

                                @if ($lead->assignedMember?->team)
                                    <div class="small text-muted">
                                        {{ $lead->assignedMember->team->name }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div>{{ $lead->source ?? '-' }}</div>
                                <div class="small text-muted">{{ $lead->city ?? '-' }}</div>
                            </td>

                            <td>
                                @if ($lead->latestFollowup)
                                    <div class="small">
                                        {{ $lead->latestFollowup->next_followup_at?->format('Y-m-d') ?? '-' }}
                                    </div>

                                    <div class="small text-muted">
                                        {{ $lead->latestFollowup->status ?? '-' }}
                                    </div>
                                @else
                                    <span class="text-muted">لا يوجد</span>
                                @endif
                            </td>

                            <td>
                                {{ $lead->tasks_count }}
                            </td>

                            <td>
                                @if ($lead->convertedClient)
                                    <a href="{{ route('admin.clients.show', $lead->convertedClient) }}">
                                        {{ $lead->convertedClient->name }}
                                    </a>

                                    <div class="small text-muted">
                                        {{ $lead->converted_at?->format('Y-m-d') ?? '-' }}
                                    </div>
                                @else
                                    <span class="text-muted">لم يتحول</span>
                                @endif
                            </td>

                            <td>{{ $lead->created_at?->format('Y-m-d') }}</td>

                            <td>

                                <div class="row-actions">
                                @if (! $lead->trashed())
                                    <a href="{{ route('admin.leads.show', $lead) }}"
                                       class="row-action is-view" title="عرض" aria-label="عرض">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @else
                                    <span class="text-muted small">محذوف</span>
                                @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                            <div class="list-empty">
                                <i class="bi bi-inbox"></i>
                                <div>لا توجد نتائج مطابقة للفلاتر الحالية</div>
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
