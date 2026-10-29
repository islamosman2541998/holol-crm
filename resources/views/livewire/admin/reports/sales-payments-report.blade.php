<div>
    <section class="stat-section stat-tone-finance">
        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-receipt"></i></div>
                        <div class="stat-title">عدد عمليات البيع</div>
                        <div class="stat-value">{{ number_format($stats['sales_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-cash-coin"></i></div>
                        <div class="stat-title">إجمالي المبيعات</div>
                        <div class="stat-value">{{ number_format($stats['sales_total'], 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
                        <div class="stat-title">إجمالي المدفوع</div>
                        <div class="stat-value">{{ number_format($stats['paid_total'], 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-hourglass-bottom"></i></div>
                        <div class="stat-title">إجمالي المتبقي</div>
                        <div class="stat-value">{{ number_format($stats['remaining_total'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-check2-all"></i></div>
                        <div class="stat-title">مدفوع بالكامل</div>
                        <div class="stat-value">{{ number_format($stats['paid_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-circle-half"></i></div>
                        <div class="stat-title">مدفوع جزئيًا</div>
                        <div class="stat-value">{{ number_format($stats['partial_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                        <div class="stat-title">قيد الانتظار</div>
                        <div class="stat-value">{{ number_format($stats['pending_count']) }}</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
                        <div class="stat-title">ملغي</div>
                        <div class="stat-value">{{ number_format($stats['cancelled_count']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4 row-cols-1 row-cols-sm-2 row-cols-xl-5 g-4">
            <div class="col">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-cash"></i></div>
                        <div class="stat-title">كاش</div>
                        <div class="stat-value">{{ number_format($stats['cash_total'], 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-bank"></i></div>
                        <div class="stat-title">تحويل بنكي</div>
                        <div class="stat-value">{{ number_format($stats['bank_transfer_total'], 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-phone"></i></div>
                        <div class="stat-title">InstaPay</div>
                        <div class="stat-value">{{ number_format($stats['instapay_total'], 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-phone-vibrate"></i></div>
                        <div class="stat-title">Vodafone Cash</div>
                        <div class="stat-value">{{ number_format($stats['vodafone_cash_total'], 2) }}</div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="stat-card card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="stat-icon"><i class="bi bi-three-dots"></i></div>
                        <div class="stat-title">أخرى</div>
                        <div class="stat-value">{{ number_format($stats['other_total'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">نتائج التقرير</h2>
                <p class="list-card-sub">{{ number_format($sales->total()) }} عملية بيع</p>
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
                <input type="text" class="form-control" placeholder="عميل / شركة / رقم عرض / ملاحظات" wire:model.live.debounce.400ms="search">
            </div>
            </div>

            <div class="list-filter">
                <label class="form-label">حالة البيع</label>
                <select class="form-select" wire:model.live="status">
                    <option value="">كل الحالات</option>
                    <option value="pending">قيد الانتظار</option>
                    <option value="partial">مدفوع جزئيًا</option>
                    <option value="paid">مدفوع</option>
                    <option value="cancelled">ملغي</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label">طريقة الدفع</label>
                <select class="form-select" wire:model.live="paymentMethod">
                    <option value="">كل الطرق</option>
                    <option value="cash">كاش</option>
                    <option value="bank_transfer">تحويل بنكي</option>
                    <option value="instapay">InstaPay</option>
                    <option value="vodafone_cash">Vodafone Cash</option>
                    <option value="other">أخرى</option>
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
                <label class="form-label">بيع من تاريخ</label>
                <input type="date" class="form-control" wire:model.live="soldFrom">
            </div>

            <div class="list-filter">
                <label class="form-label">بيع إلى تاريخ</label>
                <input type="date" class="form-control" wire:model.live="soldTo">
            </div>

            <div class="list-filter">
                <label class="form-label">دفع من تاريخ</label>
                <input type="date" class="form-control" wire:model.live="paidFrom">
            </div>

            <div class="list-filter">
                <label class="form-label">دفع إلى تاريخ</label>
                <input type="date" class="form-control" wire:model.live="paidTo">
            </div>

            <div class="list-filter">
                <label class="form-label">عرض سعر</label>
                <select class="form-select" wire:model.live="quotationState">
                    <option value="">الكل</option>
                    <option value="with">مرتبط بعرض سعر</option>
                    <option value="without">غير مرتبط بعرض سعر</option>
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
                        <th>البيع</th>
                        <th>العميل</th>
                        <th>الخدمات</th>
                        <th>الإجمالي</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                        <th>طريقة الدفع</th>
                        <th>تاريخ البيع</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($sales as $sale)
                        @php
                            $paidAmount = $sale->payments->sum('amount');
                            $remainingAmount = max((float) $sale->total - (float) $paidAmount, 0);
                        @endphp

                        <tr>
                            <td>
                                <div class="fw-semibold">#{{ $sale->id }}</div>

                                @if ($sale->quotation)
                                    <div class="small text-muted">
                                        عرض: {{ $sale->quotation->quotation_number }}
                                    </div>
                                @else
                                    <div class="small text-muted">
                                        بدون عرض سعر
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="fw-semibold">{{ $sale->client?->name ?? '-' }}</div>
                                <div class="small text-muted">{{ $sale->client?->company ?? '-' }}</div>
                            </td>

                            <td>
                                @foreach ($sale->items as $item)
                                    <div class="small">
                                        {{ $item->service?->name ?? '-' }}
                                        × {{ $item->quantity }}
                                    </div>
                                @endforeach
                            </td>

                            <td>{{ number_format($sale->total, 2) }}</td>
                            <td>{{ number_format($paidAmount, 2) }}</td>
                            <td>{{ number_format($remainingAmount, 2) }}</td>

                            <td>
                                <span class="badge {{ $sale->status_badge_class }}">
                                    {{ $sale->status_label }}
                                </span>
                            </td>

                            <td>{{ $sale->payment_method_label }}</td>

                            <td>{{ $sale->sold_at?->format('Y-m-d') ?? '-' }}</td>

                            <td>

                                <div class="row-actions">
                                <a href="{{ route('admin.sales.show', $sale) }}"
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

        @if ($sales->hasPages())
            <div class="list-card-foot">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</div>
