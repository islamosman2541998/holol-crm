<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">بحث</label>
                    <input type="text"
                           class="form-control"
                           placeholder="اسم الخدمة / الكود / الوصف"
                           wire:model.live.debounce.400ms="search">
                </div>

                <div class="col-md-3">
                    <label class="form-label">الحالة</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">كل الحالات</option>
                        <option value="1">نشط</option>
                        <option value="0">غير نشط</option>
                    </select>
                </div>

                <div class="col-md-3">
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
            <h5 class="mb-0">قائمة الخدمات</h5>

            @can('services.create')
                <a href="{{ route('admin.services.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    إضافة خدمة
                </a>
            @endcan
        </div>

        <div class="card-body">
            <div class="table-responsive app-table-responsive">
                <table class="table table-hover align-middle app-data-table">
                    <thead>
                        <tr>
                            <th>اسم الخدمة</th>
                            <th>الكود</th>
                            <th>السعر الافتراضي</th>
                            <th>الحالة</th>
                            <th>تاريخ الإضافة</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($services as $service)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $service->name }}</div>
                                    <div class="small text-muted">
                                        {{ str($service->description)->limit(70) ?: '-' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $service->code }}
                                    </span>
                                </td>

                                <td>{{ number_format($service->default_price, 2) }}</td>

                                <td>
                                    <span class="badge {{ $service->status_badge_class }}">
                                        {{ $service->status_label }}
                                    </span>
                                </td>

                                <td>{{ $service->created_at->format('Y-m-d') }}</td>

                                <td class="text-end">
                                    @can('services.edit')
                                        <a href="{{ route('admin.services.edit', $service) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('services.delete')
                                        <form action="{{ route('admin.services.destroy', $service) }}"
                                              method="POST"
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
                                <td colspan="6" class="text-center text-muted py-4">
                                    لا توجد خدمات
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $services->links() }}
            </div>
        </div>
    </div>
</div>
