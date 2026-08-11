<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <input type="text"
                           wire:model.live.debounce.400ms="search"
                           class="form-control"
                           placeholder="بحث برقم العرض أو العميل أو الشركة">
                </div>

                <div class="col-md-3">
                    <select wire:model.live="status" class="form-select">
                        <option value="">كل الحالات</option>
                        <option value="pending">معلق</option>
                        <option value="open">مفتوح</option>
                        <option value="closed">مغلق</option>
                        <option value="cancelled">ملغي</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <button type="button"
                            wire:click="resetFilters"
                            class="btn btn-light w-100">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>رقم العرض</th>
                            <th>العميل</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th>تاريخ العرض</th>
                            <th>مرتبط ببيع؟</th>
                            <th class="text-end">إجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($quotations as $quotation)
                            <tr>
                                <td>
                                    <strong>{{ $quotation->quotation_number }}</strong>
                                </td>

                                <td>
                                    @if ($quotation->client)
                                        {{ $quotation->client->name }}
                                        <div class="small text-muted">
                                            {{ $quotation->client->company ?? '-' }}
                                        </div>
                                    @elseif ($quotation->lead)
                                        {{ $quotation->lead->name }}
                                        <span class="badge bg-light text-dark border">Lead</span>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>{{ number_format($quotation->total, 2) }}</td>

                                <td>
                                    <span class="badge {{ $quotation->status_badge_class }}">
                                        {{ $quotation->status_label }}
                                    </span>
                                </td>

                                <td>{{ $quotation->quotation_date?->format('Y-m-d') ?? '-' }}</td>

                                <td>
                                    @if ($quotation->sale)
                                        <span class="badge bg-success">نعم</span>
                                    @else
                                        <span class="badge bg-secondary">لا</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a href="{{ route('admin.quotations.show', $quotation) }}"
                                       class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    @can('quotations.edit')
                                        @if (! in_array($quotation->status, ['closed', 'cancelled']))
                                            <a href="{{ route('admin.quotations.edit', $quotation) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    لا توجد عروض أسعار
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $quotations->links() }}
        </div>
    </div>
</div>