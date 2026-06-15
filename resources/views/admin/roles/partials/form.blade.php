@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-4">
    <label class="form-label">اسم الدور</label>
    <input type="text"
           name="name"
           class="form-control"
           value="{{ old('name', $role?->name) }}">
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">الصلاحيات</h5>

    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAllPermissions()">
        تحديد / إلغاء الكل
    </button>
</div>

<div class="row g-4">
    @foreach ($permissions as $group => $items)
        <div class="col-md-6 col-xl-4">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-light fw-semibold">
                    {{ ucfirst($group) }}
                </div>

                <div class="card-body">
                    @foreach ($items as $permission)
                        <div class="form-check mb-2">
                            <input class="form-check-input permission-checkbox"
                                   type="checkbox"
                                   name="permissions[]"
                                   value="{{ $permission->name }}"
                                   id="permission_{{ $permission->id }}"
                                   @checked(in_array($permission->name, old('permissions', $rolePermissions)))>

                            <label class="form-check-label" for="permission_{{ $permission->id }}">
                                {{ $permission->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
    function toggleAllPermissions() {
        const checkboxes = document.querySelectorAll('.permission-checkbox');
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);

        checkboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
        });
    }
</script>