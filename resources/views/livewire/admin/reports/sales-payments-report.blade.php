<div>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">عدد عمليات البيع</div>
                    <h4 class="mb-0">{{ number_format($stats['sales_count']) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">إجمالي المبيعات</div>
                    <h4 class="mb-0">{{ number_format($stats['sales_total'], 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">إجمالي المدفوع</div>
                    <h4 class="mb-0">{{ number_format($stats['paid_total'], 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">إجمالي المتبقي</div>
                    <h4 class="mb-0">{{ number_format($stats['remaining_total'], 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">مدفوع بالكامل</div>
                    <h5 class="mb-0">{{ number_format($stats['paid_count']) }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">مدفوع جزئيًا</div>
                    <h5 class="mb-0">{{ number_format($stats['partial_count']) }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">قيد الانتظار</div>
                    <h5 class="mb-0">{{ number_format($stats['pending_count']) }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">ملغي</div>
                    <h5 class="mb-0">{{ number_format($stats['cancelled_count']) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">كاش</div>
                    <strong>{{ number_format($stats['cash_total'], 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">تحويل بنكي</div>
                    <strong>{{ number_format($stats['bank_transfer_total'], 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">InstaPay</div>
                    <strong>{{ number_format($stats['instapay_total'], 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Vodafone Cash</div>
                    <strong>{{ number_format($stats['vodafone_cash_total'], 2) }}</strong>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">أخرى</div>
                    <strong>{{ number_format($stats['other_total'], 2) }}</strong>
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
                           placeholder="عميل / شركة / رقم عرض / ملاحظات"
                           wire:model.live.debounce.400ms="search">
                </div>

                <div class="col-md-2">
                    <label class="form-label">حالة البيع</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">كل الحالات</option>
                        <option value="pending">قيد الانتظار</option>
                        <option value="partial">مدفوع جزئيًا</option>
                        <option value="paid">مدفوع</option>
                        <option value="cancelled">ملغي</option>
                    </select>
                </div>

                <div class="col-md-2">
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

                <div class="col-md-3">
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
                    <label class="form-label">بيع من تاريخ</label>
                    <input type="date" class="form-control" wire:model.live="soldFrom">
                </div>

                <div class="col-md-2">
                    <label class="form-label">بيع إلى تاريخ</label>
                    <input type="date" class="form-control" wire:model.live="soldTo">
                </div>

                <div class="col-md-2">
                    <label class="form-label">دفع من تاريخ</label>
                    <input type="date" class="form-control" wire:model.live="paidFrom">
                </div>

                <div class="col-md-2">
                    <label class="form-label">دفع إلى تاريخ</label>
                    <input type="date" class="form-control" wire:model.live="paidTo">
                </div>

                <div class="col-md-2">
                    <label class="form-label">عرض سعر</label>
                    <select class="form-select" wire:model.live="quotationState">
                        <option value="">الكل</option>
                        <option value="with">مرتبط بعرض سعر</option>
                        <option value="without">غير مرتبط بعرض سعر</option>
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
                عدد النتائج: {{ $sales->total() }}
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
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
                            <th class="text-end">إجراء</th>
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

                                <td class="text-end">
                                    <a href="{{ route('admin.sales.show', $sale) }}"
                                       class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-eye"></i>
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
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</div>