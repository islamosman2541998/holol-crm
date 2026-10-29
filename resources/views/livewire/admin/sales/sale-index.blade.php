<div>
    @php
        $hasFilters = $search !== '' || $status !== '' || $userId !== '';
    @endphp

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">قائمة المبيعات</h2>
                <p class="list-card-sub">{{ number_format($sales->total()) }} عملية بيع{{ $hasFilters ? ' مطابقة للتصفية' : '' }}</p>
            </div>

            <div class="list-card-actions">
                @if ($hasFilters)
                    <button type="button" class="btn btn-light" wire:click="resetFilters">
                        <i class="bi bi-x-lg"></i>
                        مسح التصفية
                    </button>
                @endif

                @can('sales.create')
                    <a href="{{ route('admin.sales.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        إضافة عملية بيع
                    </a>
                @endcan
            </div>
        </header>

        <div class="list-filters">
            <div class="list-filter list-filter-search">
                <label class="form-label" for="saleSearch">بحث</label>
                <div class="list-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="saleSearch" class="form-control" placeholder="اسم العميل / الشركة / الموبايل"
                        wire:model.live.debounce.400ms="search">
                </div>
            </div>

            <div class="list-filter">
                <label class="form-label" for="saleStatus">الحالة</label>
                <select id="saleStatus" class="form-select" wire:model.live="status">
                    <option value="">كل الحالات</option>
                    <option value="pending">قيد الانتظار</option>
                    <option value="partial">مدفوع جزئيًا</option>
                    <option value="paid">مدفوع</option>
                    <option value="cancelled">ملغي</option>
                </select>
            </div>

            <div class="list-filter">
                <label class="form-label" for="saleUser">الموظف</label>
                <select id="saleUser" class="form-select" wire:model.live="userId">
                    <option value="">كل الموظفين</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive data-table-wrap">
            <table class="table align-middle mb-0 data-table has-actions">
                <thead>
                    <tr>
                        <th>العميل</th>
                        <th>الخدمات</th>
                        <th>الإجمالي</th>
                        <th>طريقة الدفع</th>
                        <th>الحالة</th>
                        <th>الموظف</th>
                        <th>التاريخ</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($sales as $sale)
                        <tr wire:key="sale-{{ $sale->id }}">
                            <td class="cell-title-wrap">
                                <a href="{{ route('admin.sales.show', $sale) }}" class="cell-title">
                                    {{ $sale->client?->name ?? '-' }}
                                </a>
                                <span class="cell-sub">بيع #{{ $sale->id }} · {{ $sale->client?->company ?? '-' }}</span>
                            </td>

                            <td style="min-width: 160px;">
                                @foreach ($sale->items as $item)
                                    <span class="cell-type mb-1">{{ $item->service?->name ?? 'خدمة محذوفة' }}</span>
                                @endforeach
                            </td>

                            <td class="cell-amount">{{ number_format($sale->total, 2) }}</td>

                            <td class="text-nowrap">{{ $sale->payment_method_label }}</td>

                            <td>
                                <span class="badge {{ $sale->status_badge_class }}">{{ $sale->status_label }}</span>
                            </td>

                            <td class="text-nowrap">{{ $sale->user?->name ?? '-' }}</td>

                            <td class="cell-date-wrap">
                                <span class="cell-date">{{ $sale->sold_at?->format('Y-m-d') ?? '-' }}</span>
                            </td>

                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.sales.show', $sale) }}" class="row-action is-view"
                                        title="عرض عملية البيع" aria-label="عرض عملية البيع">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('sales.edit')
                                        <a href="{{ route('admin.sales.edit', $sale) }}" class="row-action is-edit"
                                            title="تعديل عملية البيع" aria-label="تعديل عملية البيع">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('sales.delete')
                                        <form action="{{ route('admin.sales.destroy', $sale) }}" method="POST"
                                            class="js-delete-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="row-action is-danger" title="حذف عملية البيع"
                                                aria-label="حذف عملية البيع">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="list-empty">
                                    <i class="bi bi-inbox"></i>
                                    <div>{{ $hasFilters ? 'لا توجد مبيعات مطابقة للتصفية' : 'لا توجد مبيعات حتى الآن' }}</div>
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
