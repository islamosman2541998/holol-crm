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
                        <option value="new">جديد</option>
                        <option value="active">نشط</option>
                        <option value="inactive">غير نشط</option>
                        <option value="lost">مفقود</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">الموظف المسؤول</label>
                    <select class="form-select" wire:model.live="assignedTo">
                        <option value="">كل الموظفين</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
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
            <h5 class="mb-0">قائمة العملاء</h5>

            @can('clients.create')
                <a href="{{ route('admin.clients.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    إضافة عميل
                </a>
            @endcan
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>العميل</th>
                            <th>الشركة</th>
                            <th>الموبايل</th>
                            <th>الإيميل</th>
                            <th>الموظف المسؤول</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th class="text-end">الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($clients as $client)
                            <tr>
                                <td class="fw-semibold">{{ $client->name }}</td>
                                <td>{{ $client->company ?? '-' }}</td>
                                <td>{{ $client->mobile ?? ($client->phone ?? '-') }}</td>
                                <td>{{ $client->email ?? '-' }}</td>
                                <td>{{ $client->assignedUser?->name ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $client->status_badge_class }}">
                                        {{ $client->status_label }}
                                    </span>
                                </td>
                                <td>{{ $client->created_at->format('Y-m-d') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.clients.show', $client) }}"
                                        class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @can('clients.edit')
                                        <a href="{{ route('admin.clients.edit', $client) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    @endcan

                                    @can('clients.delete')
                                        <form action="{{ route('admin.clients.destroy', $client) }}" method="POST"
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
                                    لا يوجد عملاء
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $clients->links() }}
            </div>
        </div>
    </div>
</div>
