<div>
    <section class="stat-section stat-tone-clients">
        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-people"></i></div>
                        <div class="stat-title">عدد العملاء</div>
                        <div class="stat-value">{{ number_format($stats['clients_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-person-check"></i></div>
                        <div class="stat-title">عملاء نشطين</div>
                        <div class="stat-value">{{ number_format($stats['active_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-chat-left"></i></div>
                        <div class="stat-title">بدون متابعة</div>
                        <div class="stat-value">{{ number_format($stats['without_followups']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm is-alert {{ ($stats['overdue_followups']) > 0 ? 'has-value' : '' }}">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
                        <div class="stat-title">متابعات متأخرة</div>
                        <div class="stat-value">{{ number_format($stats['overdue_followups']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
                        <div class="stat-title">إجمالي المبيعات</div>
                        <div class="stat-value">{{ number_format($stats['sales_total'], 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
                        <div class="stat-title">إجمالي المدفوع</div>
                        <div class="stat-value">{{ number_format($stats['paid_total'], 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-4">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-hourglass-bottom"></i></div>
                        <div class="stat-title">إجمالي المتبقي</div>
                        <div class="stat-value">{{ number_format($stats['remaining_total'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">نتائج التقرير</h2>
                <p class="list-card-sub">{{ number_format($clients->total()) }} عميل</p>
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
                    <option value="active">نشط</option>
                    <option value="inactive">غير نشط</option>
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
                    <option value="">كل العملاء</option>
                    <option value="with">لديهم متابعات</option>
                    <option value="without">بدون متابعات</option>
                    <option value="today">متابعات اليوم</option>
                    <option value="overdue">متابعات متأخرة</option>
                    <option value="upcoming">متابعات قادمة</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label">عروض الأسعار</label>
                <select class="form-select" wire:model.live="quotationState">
                    <option value="">كل العملاء</option>
                    <option value="pending">لديهم عروض معلقة</option>
                    <option value="open">لديهم عروض مفتوحة</option>
                    <option value="closed">لديهم عروض مغلقة</option>
                    <option value="cancelled">لديهم عروض ملغية</option>
                    <option value="without">بدون عروض أسعار</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label">المبيعات</label>
                <select class="form-select" wire:model.live="salesState">
                    <option value="">كل العملاء</option>
                    <option value="with">لديهم مبيعات</option>
                    <option value="without">بدون مبيعات</option>
                    <option value="pending">مبيعات معلقة</option>
                    <option value="partial">مبيعات جزئية</option>
                    <option value="paid">مبيعات مدفوعة</option>
                    <option value="cancelled">مبيعات ملغية</option>
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
                        <th>العميل</th>
                        <th>التواصل</th>
                        <th>الحالة</th>
                        <th>المسؤول</th>
                        <th>المصدر / المدينة</th>
                        <th>آخر متابعة</th>
                        <th>العروض</th>
                        <th>المبيعات</th>
                        <th>تاريخ الإضافة</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($clients as $client)
                        @php
                            $salesTotal = $client->sales->sum(fn ($sale) => (float) $sale->total);
                            $paidTotal = $client->sales->sum(fn ($sale) => (float) $sale->payments->sum('amount'));
                            $remainingTotal = max($salesTotal - $paidTotal, 0);
                            $openQuotations = $client->quotations->where('status', 'open');
                        @endphp

                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $client->name }}</div>
                                <div class="small text-muted">{{ $client->company ?? '-' }}</div>
                                <div class="small text-muted">{{ $client->email ?? '-' }}</div>
                            </td>

                            <td>
                                <div>موبايل: {{ $this->formatPhone($client->mobile) }}</div>
                                <div class="small text-muted">هاتف: {{ $this->formatPhone($client->phone) }}</div>
                            </td>

                            <td>
                                <span class="badge {{ $client->status_badge_class }}">
                                    {{ $client->status_label }}
                                </span>
                            </td>

                            <td>
                                {{ $client->assignedMember?->name ?? $client->assignedUser?->name ?? '-' }}

                                @if ($client->assignedMember?->team)
                                    <div class="small text-muted">
                                        {{ $client->assignedMember->team->name }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div>{{ $client->source ?? '-' }}</div>
                                <div class="small text-muted">{{ $client->city ?? '-' }}</div>
                            </td>

                            <td>
                                @if ($client->latestFollowup)
                                    <div class="small">
                                        {{ $client->latestFollowup->next_followup_at?->format('Y-m-d') ?? '-' }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ $client->latestFollowup->status ?? '-' }}
                                    </div>
                                @else
                                    <span class="text-muted">لا يوجد</span>
                                @endif
                            </td>

                            <td>
                                <div>الكل: {{ $client->quotations_count }}</div>
                                <div class="small text-muted">
                                    مفتوحة: {{ $openQuotations->count() }}
                                </div>
                            </td>

                            <td>
                                <div>عدد: {{ $client->sales_count }}</div>
                                <div class="small text-muted">
                                    إجمالي: {{ number_format($salesTotal, 2) }}
                                </div>
                                <div class="small text-muted">
                                    متبقي: {{ number_format($remainingTotal, 2) }}
                                </div>
                            </td>

                            <td>{{ $client->created_at?->format('Y-m-d') }}</td>

                            <td>

                                <div class="row-actions">
                                <a href="{{ route('admin.clients.show', $client) }}"
                                   class="row-action is-view" title="عرض" aria-label="عرض">
                                    <i class="bi bi-eye"></i>
                                </a>
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

        @if ($clients->hasPages())
            <div class="list-card-foot">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</div>
