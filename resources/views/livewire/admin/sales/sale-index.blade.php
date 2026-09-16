<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">بحث</label>
                    <input type="text" class="form-control" placeholder="اسم العميل / الشركة / الموبايل"
                        wire:model.live.debounce.400ms="search">
                </div>

                <div class="col-md-3">
                    <label class="form-label">الحالة</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">كل الحالات</option>
                        <option value="pending">قيد الانتظار</option>
                        <option value="partial">مدفوع جزئيًا</option>
                        <option value="paid">مدفوع</option>
                        <option value="cancelled">ملغي</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">الموظف</label>
                    <select class="form-select" wire:model.live="userId">
                        <option value="">كل الموظفين</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-light w-100" wire:click="resetFilters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">قائمة المبيعات</h5>

            @can('sales.create')
                <a href="{{ route('admin.sales.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    إضافة عملية بيع
                </a>
            @endcan
        </div>

        <div class="card-body">
            <div class="table-responsive app-table-responsive">
                <table class="table table-hover align-middle app-data-table">
                    <thead>
                        <tr>
                            <th>العميل</th>
                            <th>الخدمات</th>
                            <th>الإجمالي</th>
                            <th>طريقة الدفع</th>
                            <th>الحالة</th>
                            <th>الموظف</th>
                            <th>التاريخ</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td>
                                    <div class="fw-semibold">
                                        {{ $sale->client?->name ?? '-' }}
                                    </div>
                                    <div class="small text-muted">
                                        {{ $sale->client?->company ?? '-' }}
                                    </div>
                                </td>

                                <td>
                                    @foreach ($sale->items as $item)
                                        <span class="badge bg-light text-dark mb-1">
                                            {{ $item->service?->name ?? 'خدمة محذوفة' }}
                                        </span>
                                    @endforeach
                                </td>

                                <td class="fw-bold">
                                    {{ number_format($sale->total, 2) }}
                                </td>

                                <td>{{ $sale->payment_method_label }}</td>

                                <td>
                                    <span class="badge {{ $sale->status_badge_class }}">
                                        {{ $sale->status_label }}
                                    </span>
                                </td>

                                <td>{{ $sale->user?->name ?? '-' }}</td>

                                <td>{{ $sale->sold_at?->format('Y-m-d') ?? '-' }}</td>

                                <td class="text-end">
                                    <a href="{{ route('admin.sales.show', $sale) }}"
                                        class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('sales.edit')
                                        <a href="{{ route('admin.sales.edit', $sale) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('sales.delete')
                                        <form action="{{ route('admin.sales.destroy', $sale) }}" method="POST"
                                            class="d-inline js-delete-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    لا توجد مبيعات
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
