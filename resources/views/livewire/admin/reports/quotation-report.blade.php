<div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">عدد العروض</div>
                    <h4 class="mb-0">{{ number_format($stats['quotations_count']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">إجمالي قيمة العروض</div>
                    <h4 class="mb-0">{{ number_format($stats['grand_total'], 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">تحولت لبيع</div>
                    <h4 class="mb-0">{{ number_format($stats['converted_count']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">مفتوحة بدون بيع</div>
                    <h4 class="mb-0">{{ number_format($stats['open_without_sale_count']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">معلقة</div>
                    <h5 class="mb-0">{{ number_format($stats['pending_count']) }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">مفتوحة</div>
                    <h5 class="mb-0">{{ number_format($stats['open_count']) }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">مغلقة</div>
                    <h5 class="mb-0">{{ number_format($stats['closed_count']) }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">منتهية الصلاحية</div>
                    <h5 class="mb-0">{{ number_format($stats['expired_count']) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">فلاتر التقرير</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">بحث</label>
                    <input type="text"
                           class="form-control"
                           placeholder="رقم العرض / العميل / شركة / موبايل"
                           wire:model.live.debounce.400ms="search">
                </div>

                <div class="col-md-2">
                    <label class="form-label">حالة العرض</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">كل الحالات</option>
                        <option value="pending">معلق</option>
                        <option value="open">مفتوح</option>
                        <option value="closed">مغلق</option>
                        <option value="cancelled">ملغي</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">العميل</label>
                    <select class="form-select" wire:model.live="clientId">
                        <option value="">كل العملاء</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">الخدمة</label>
                    <select class="form-select" wire:model.live="serviceId">
                        <option value="">كل الخدمات</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
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

                <div class="col-md-2">
                    <label class="form-label">تاريخ العرض من</label>
                    <input type="date" class="form-control" wire:model.live="quotationFrom">
                </div>

                <div class="col-md-2">
                    <label class="form-label">تاريخ العرض إلى</label>
                    <input type="date" class="form-control" wire:model.live="quotationTo">
                </div>

                <div class="col-md-2">
                    <label class="form-label">صالح من</label>
                    <input type="date" class="form-control" wire:model.live="validFrom">
                </div>

                <div class="col-md-2">
                    <label class="form-label">صالح إلى</label>
                    <input type="date" class="form-control" wire:model.live="validTo">
                </div>

                <div class="col-md-2">
                    <label class="form-label">الصلاحية</label>
                    <select class="form-select" wire:model.live="validityState">
                        <option value="">الكل</option>
                        <option value="valid">ساري</option>
                        <option value="expired">منتهي الصلاحية</option>
                        <option value="no_validity">بدون تاريخ صلاحية</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">التحويل لبيع</label>
                    <select class="form-select" wire:model.live="saleState">
                        <option value="">الكل</option>
                        <option value="with_sale">تحول لبيع</option>
                        <option value="without_sale">لم يتحول لبيع</option>
                        <option value="open_without_sale">مفتوح ولم يتحول لبيع</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <label class="form-label">عرض</label>
                    <select class="form-select" wire:model.live="perPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>

                <div class="col-md-12 d-flex justify-content-end gap-2">
                    <button type="button"
                            class="btn btn-light"
                            wire:click="resetFilters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        تفريغ الفلاتر
                    </button>

                    <button type="button"
                            class="btn btn-success"
                            wire:click="exportExcel"
                            wire:loading.attr="disabled">
                        <i class="bi bi-file-earmark-excel"></i>
                        تصدير Excel حسب الفلتر
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">نتائج التقرير</h5>

            <div class="small text-muted">
                عدد النتائج: {{ $quotations->total() }}
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive app-table-responsive">
                <table class="table table-hover align-middle app-data-table">
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
                            <th class="text-end">إجراء</th>
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

                                <td class="text-end">
                                    <a href="{{ route('admin.quotations.show', $quotation) }}"
                                       class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a href="{{ route('admin.quotations.pdf', $quotation) }}"
                                       class="btn btn-sm btn-outline-danger"
                                       target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    لا توجد نتائج مطابقة للفلاتر الحالية
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $quotations->links() }}
            </div>
        </div>
    </div>
</div>
