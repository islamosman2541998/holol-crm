<div>
    @php
        $hasFilters = $search !== '' || $status !== '';
    @endphp

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">عروض الأسعار</h2>
                <p class="list-card-sub">{{ number_format($quotations->total()) }} عرض سعر{{ $hasFilters ? ' مطابق للتصفية' : '' }}</p>
            </div>

            <div class="list-card-actions">
                @if ($hasFilters)
                    <button type="button" class="btn btn-light" wire:click="resetFilters">
                        <i class="bi bi-x-lg"></i>
                        مسح التصفية
                    </button>
                @endif

                @can('quotations.create')
                    <a href="{{ route('admin.quotations.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        إضافة عرض سعر
                    </a>
                @endcan
            </div>
        </header>

        <div class="list-filters">
            <div class="list-filter list-filter-search">
                <label class="form-label" for="quotationSearch">بحث</label>
                <div class="list-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="quotationSearch" class="form-control" placeholder="رقم العرض / العميل / الشركة"
                        wire:model.live.debounce.400ms="search">
                </div>
            </div>

            <div class="list-filter">
                <label class="form-label" for="quotationStatus">الحالة</label>
                <select id="quotationStatus" class="form-select" wire:model.live="status">
                    <option value="">كل الحالات</option>
                    <option value="pending">معلق</option>
                    <option value="open">مفتوح</option>
                    <option value="closed">مغلق</option>
                    <option value="cancelled">ملغي</option>
                </select>
            </div>
        </div>

        <div class="table-responsive data-table-wrap">
            <table class="table align-middle mb-0 data-table has-actions">
                <thead>
                    <tr>
                        <th>رقم العرض</th>
                        <th>العميل</th>
                        <th>الإجمالي</th>
                        <th>الحالة</th>
                        <th>تاريخ العرض</th>
                        <th>مرتبط ببيع؟</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($quotations as $quotation)
                        <tr wire:key="quotation-{{ $quotation->id }}">
                            <td class="text-nowrap">
                                <a href="{{ route('admin.quotations.show', $quotation) }}" class="cell-title">
                                    {{ $quotation->quotation_number }}
                                </a>
                            </td>

                            <td>
                                @if ($quotation->client)
                                    <span class="cell-person-name">{{ $quotation->client->name }}</span>
                                    <span class="cell-sub">{{ $quotation->client->company ?? '-' }}</span>
                                @elseif ($quotation->lead)
                                    <span class="cell-type">Lead</span>
                                    <span class="cell-person-name">{{ $quotation->lead->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>

                            <td class="cell-amount">{{ number_format($quotation->total, 2) }}</td>

                            <td>
                                <span class="badge {{ $quotation->status_badge_class }}">{{ $quotation->status_label }}</span>
                            </td>

                            <td class="cell-date-wrap">
                                <span class="cell-date">{{ $quotation->quotation_date?->format('Y-m-d') ?? '-' }}</span>
                            </td>

                            <td>
                                @if ($quotation->sale)
                                    <span class="badge bg-success">نعم</span>
                                @else
                                    <span class="badge bg-secondary">لا</span>
                                @endif
                            </td>

                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.quotations.show', $quotation) }}" class="row-action is-view"
                                        title="عرض عرض السعر" aria-label="عرض عرض السعر">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('quotations.edit')
                                        @if (! in_array($quotation->status, ['closed', 'cancelled']))
                                            <a href="{{ route('admin.quotations.edit', $quotation) }}" class="row-action is-edit"
                                                title="تعديل عرض السعر" aria-label="تعديل عرض السعر">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="list-empty">
                                    <i class="bi bi-inbox"></i>
                                    <div>{{ $hasFilters ? 'لا توجد عروض أسعار مطابقة للتصفية' : 'لا توجد عروض أسعار حتى الآن' }}</div>
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
