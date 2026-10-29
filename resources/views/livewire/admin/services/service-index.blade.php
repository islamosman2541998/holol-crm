<div>
    @php
        $hasFilters = $search !== '' || $status !== '';
    @endphp

    <div class="list-card">
        <header class="list-card-head">
            <div>
                <h2 class="list-card-title">قائمة الخدمات</h2>
                <p class="list-card-sub">{{ number_format($services->total()) }} خدمة{{ $hasFilters ? ' مطابقة للتصفية' : '' }}</p>
            </div>

            <div class="list-card-actions">
                @if ($hasFilters)
                    <button type="button" class="btn btn-light" wire:click="resetFilters">
                        <i class="bi bi-x-lg"></i>
                        مسح التصفية
                    </button>
                @endif

                @can('services.create')
                    <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg"></i>
                        إضافة خدمة
                    </a>
                @endcan
            </div>
        </header>

        <div class="list-filters">
            <div class="list-filter list-filter-search">
                <label class="form-label" for="serviceSearch">بحث</label>
                <div class="list-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="serviceSearch" class="form-control" placeholder="اسم الخدمة / الكود / الوصف"
                        wire:model.live.debounce.400ms="search">
                </div>
            </div>

            <div class="list-filter">
                <label class="form-label" for="serviceStatus">الحالة</label>
                <select id="serviceStatus" class="form-select" wire:model.live="status">
                    <option value="">كل الحالات</option>
                    <option value="1">نشط</option>
                    <option value="0">غير نشط</option>
                </select>
            </div>
        </div>

        <div class="table-responsive data-table-wrap">
            <table class="table align-middle mb-0 data-table has-actions">
                <thead>
                    <tr>
                        <th>اسم الخدمة</th>
                        <th>الكود</th>
                        <th>السعر الافتراضي</th>
                        <th>الحالة</th>
                        <th>تاريخ الإضافة</th>
                        <th class="col-actions">الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($services as $service)
                        <tr wire:key="service-{{ $service->id }}">
                            <td style="min-width: 240px;">
                                <span class="cell-person-name">{{ $service->name }}</span>
                                <span class="cell-sub">{{ str($service->description)->limit(70) ?: '-' }}</span>
                            </td>

                            <td>
                                <span class="cell-type">{{ $service->code }}</span>
                            </td>

                            <td class="cell-amount">{{ number_format($service->default_price, 2) }}</td>

                            <td>
                                <span class="badge {{ $service->status_badge_class }}">{{ $service->status_label }}</span>
                            </td>

                            <td class="cell-date-wrap">
                                <span class="cell-date">{{ $service->created_at->format('Y-m-d') }}</span>
                            </td>

                            <td>
                                <div class="row-actions">
                                    @can('services.edit')
                                        <a href="{{ route('admin.services.edit', $service) }}" class="row-action is-edit"
                                            title="تعديل الخدمة" aria-label="تعديل الخدمة">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('services.delete')
                                        <form action="{{ route('admin.services.destroy', $service) }}" method="POST"
                                            class="js-delete-form">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="row-action is-danger" title="حذف الخدمة"
                                                aria-label="حذف الخدمة">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="list-empty">
                                    <i class="bi bi-inbox"></i>
                                    <div>{{ $hasFilters ? 'لا توجد خدمات مطابقة للتصفية' : 'لا توجد خدمات حتى الآن' }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($services->hasPages())
            <div class="list-card-foot">
                {{ $services->links() }}
            </div>
        @endif
    </div>
</div>
