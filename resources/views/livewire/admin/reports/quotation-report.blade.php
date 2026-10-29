<div>
    <section class="stat-section stat-tone-tasks">
        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
                        <div class="stat-title">عدد العروض</div>
                        <div class="stat-value">{{ number_format($stats['quotations_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                        <div class="stat-title">إجمالي قيمة العروض</div>
                        <div class="stat-value">{{ number_format($stats['grand_total'], 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-arrow-left-right"></i></div>
                        <div class="stat-title">تحولت لبيع</div>
                        <div class="stat-value">{{ number_format($stats['converted_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-folder2-open"></i></div>
                        <div class="stat-title">مفتوحة بدون بيع</div>
                        <div class="stat-value">{{ number_format($stats['open_without_sale_count']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-pause-circle"></i></div>
                        <div class="stat-title">معلقة</div>
                        <div class="stat-value">{{ number_format($stats['pending_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-envelope-open"></i></div>
                        <div class="stat-title">مفتوحة</div>
                        <div class="stat-value">{{ number_format($stats['open_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-lock"></i></div>
                        <div class="stat-title">مغلقة</div>
                        <div class="stat-value">{{ number_format($stats['closed_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm is-alert {{ ($stats['expired_count']) > 0 ? 'has-value' : '' }}">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-calendar-x"></i></div>
                        <div class="stat-title">منتهية الصلاحية</div>
                        <div class="stat-value">{{ number_format($stats['expired_count']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">نتائج التقرير</h2>
                <p class="list-card-sub">{{ number_format($quotations->total()) }} عرض سعر</p>
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
                <input type="text" class="form-control" placeholder="رقم العرض / العميل / شركة / موبايل" wire:model.live.debounce.400ms="search">
            </div>
            </div>

            <div class="list-filter">
                <label class="form-label">حالة العرض</label>
                <select class="form-select" wire:model.live="status">
                    <option value="">كل الحالات</option>
                    <option value="pending">معلق</option>
                    <option value="open">مفتوح</option>
                    <option value="closed">مغلق</option>
                    <option value="cancelled">ملغي</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label">العميل</label>
                <select class="form-select" wire:model.live="clientId">
                    <option value="">كل العملاء</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label">الخدمة</label>
                <select class="form-select" wire:model.live="serviceId">
                    <option value="">كل الخدمات</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label">الموظف</label>
                <select class="form-select" wire:model.live="userId">
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
                <label class="form-label">تاريخ العرض من</label>
                <input type="date" class="form-control" wire:model.live="quotationFrom">
            </div>

            <div class="list-filter">
                <label class="form-label">تاريخ العرض إلى</label>
                <input type="date" class="form-control" wire:model.live="quotationTo">
            </div>

            <div class="list-filter">
                <label class="form-label">صالح من</label>
                <input type="date" class="form-control" wire:model.live="validFrom">
            </div>

            <div class="list-filter">
                <label class="form-label">صالح إلى</label>
                <input type="date" class="form-control" wire:model.live="validTo">
            </div>

            <div class="list-filter">
                <label class="form-label">الصلاحية</label>
                <select class="form-select" wire:model.live="validityState">
                    <option value="">الكل</option>
                    <option value="valid">ساري</option>
                    <option value="expired">منتهي الصلاحية</option>
                    <option value="no_validity">بدون تاريخ صلاحية</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label">التحويل لبيع</label>
                <select class="form-select" wire:model.live="saleState">
                    <option value="">الكل</option>
                    <option value="with_sale">تحول لبيع</option>
                    <option value="without_sale">لم يتحول لبيع</option>
                    <option value="open_without_sale">مفتوح ولم يتحول لبيع</option>
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
                        <th>عرض السعر</th>
                        <th>العميل</th>
                        <th>الخدمات</th>
                        <th>الإجمالي</th>
                        <th>الحالة</th>
                        <th>الصلاحية</th>
                        <th>البيع</th>
                        <th>الموظف</th>
                        <th>تاريخ العرض</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($quotations as $quotation)
                        @php
                            $isExpired = $quotation->valid_until
                                && $quotation->valid_until->lt(today())
                                && ! in_array($quotation->status, ['closed', 'cancelled']);
                        @endphp

                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $quotation->quotation_number }}</div>
                                <div class="small text-muted">
                                    Items: {{ $quotation->items_count }}
                                </div>
                            </td>

                            <td>
                                @if ($quotation->client)
                                    <div class="fw-semibold">{{ $quotation->client->name }}</div>
                                    <div class="small text-muted">{{ $quotation->client->company ?? '-' }}</div>
                                    <div class="small text-muted">{{ $quotation->client->mobile ?? '-' }}</div>
                                @elseif ($quotation->lead)
                                    <div class="fw-semibold">
                                        {{ $quotation->lead->name }}
                                        <span class="badge bg-light text-dark border">Lead</span>
                                    </div>
                                    <div class="small text-muted">{{ $quotation->lead->company ?? '-' }}</div>
                                    <div class="small text-muted">{{ $quotation->lead->mobile ?? '-' }}</div>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                @foreach ($quotation->items as $item)
                                    <div class="small">
                                        {{ $item->service?->name ?? '-' }}
                                        × {{ $item->quantity }}
                                    </div>
                                @endforeach
                            </td>

                            <td>
                                <div>{{ number_format($quotation->total, 2) }}</div>
                                <div class="small text-muted">
                                    VAT: {{ number_format($quotation->vat, 2) }}
                                </div>
                            </td>

                            <td>
                                <span class="badge {{ $quotation->status_badge_class }}">
                                    {{ $quotation->status_label }}
                                </span>
                            </td>

                            <td>
                                <div>{{ $quotation->valid_until?->format('Y-m-d') ?? '-' }}</div>

                                @if ($isExpired)
                                    <span class="badge bg-danger mt-1">منتهي</span>
                                @else
                                    <span class="badge bg-success mt-1">ساري / غير منتهي</span>
                                @endif
                            </td>

                            <td>
                                @if ($quotation->sale)
                                    <a href="{{ route('admin.sales.show', $quotation->sale) }}">
                                        Sale #{{ $quotation->sale->id }}
                                    </a>

                                    <div class="small text-muted">
                                        {{ $quotation->sale->status_label }}
                                    </div>
                                @else
                                    <span class="text-muted">لم يتحول</span>
                                @endif
                            </td>

                            <td>{{ $quotation->user?->name ?? '-' }}</td>

                            <td>{{ $quotation->quotation_date?->format('Y-m-d') ?? '-' }}</td>

                            <td>

                                <div class="row-actions">
                                <a href="{{ route('admin.quotations.show', $quotation) }}"
                                   class="row-action is-view" title="عرض" aria-label="عرض">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <a href="{{ route('admin.quotations.pdf', $quotation) }}"
                                   class="row-action is-danger" title="PDF" aria-label="PDF"
                                   target="_blank">
                                    <i class="bi bi-file-earmark-pdf"></i>
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

        @if ($quotations->hasPages())
            <div class="list-card-foot">
                {{ $quotations->links() }}
            </div>
        @endif
    </div>
</div>
