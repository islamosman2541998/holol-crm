@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    <div class="col-md-6">
        <label class="form-label">اسم الفريق</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $team?->name) }}"
            placeholder="مثال: فريق البرمجة">
    </div>

    <div class="col-md-6">
        <label class="form-label">كود الفريق</label>
        <input type="text" name="code" class="form-control" value="{{ old('code', $team?->code) }}"
            placeholder="مثال: programming">
        <div class="form-text">
            الكود يكون إنجليزي بدون مسافات. منه بيتعمل Role تلقائي مثل team_programming
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">مدير الفريق</label>
        <select name="manager_member_id" class="form-select">
            <option value="">بدون مدير</option>
            @foreach ($managers as $manager)
                <option value="{{ $manager->id }}" @selected(old('manager_member_id', $team?->manager_member_id) == $manager->id)>
                    {{ $manager->name }}
                    @if ($manager->job_title)
                        - {{ $manager->job_title }}
                    @endif
                </option>
            @endforeach
        </select>
        <div class="form-text">
            يظهر هنا الأعضاء المعلّم عليهم كمدير فقط.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label d-block">حالة الفريق</label>

        <input type="hidden" name="status" value="0">

        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="status" value="1" id="statusSwitch"
                @checked(old('status', $team?->status ?? true))>

            <label class="form-check-label" for="statusSwitch">
                الفريق نشط
            </label>
        </div>
    </div>

    <div class="col-12">
        <label class="form-label">وصف الفريق</label>
        <textarea name="description" rows="3" class="form-control" placeholder="وصف مختصر لدور الفريق">{{ old('description', $team?->description) }}</textarea>
    </div>
</div>

<hr class="my-4">

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="mb-1">صلاحيات الفريق</h5>
        <div class="text-muted small">
            أي عضو داخل الفريق ومعاه User هيأخذ الصلاحيات دي تلقائيًا من Role الفريق.
        </div>
    </div>

    @can('teams.permissions')
        <button type="button" class="btn btn-sm btn-light" onclick="toggleAllPermissions(true)">
            تحديد الكل
        </button>

        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllPermissions(false)">
            إلغاء الكل
        </button>
    @endcan
</div>

@can('teams.permissions')
    <div class="row g-3">
        @foreach ($permissions as $group => $groupPermissions)
            <div class="col-lg-4 col-md-6">
                <div class="permission-group-box">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>{{ $group }}</strong>

                        <button type="button" class="btn btn-sm btn-outline-primary"
                            onclick="togglePermissionGroup('{{ $group }}')">
                            تحديد المجموعة
                        </button>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        @foreach ($groupPermissions as $permission)
                            <label class="form-check">
                                <input type="checkbox" class="form-check-input permission-checkbox"
                                    data-permission-group="{{ $group }}" name="permissions[]"
                                    value="{{ $permission->name }}" @checked(in_array($permission->name, old('permissions', $teamPermissions ?? [])))>

                                <span class="form-check-label">
                                    {{ $permission->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="alert alert-warning mb-0">
        ليس لديك صلاحية تعديل صلاحيات الفريق.
    </div>
@endcan

@push('scripts')
    <script>
        window.toggleAllPermissions = function(checked) {
            document.querySelectorAll('.permission-checkbox').forEach(function(checkbox) {
                checkbox.checked = checked;
            });
        }

        window.togglePermissionGroup = function(group) {
            const checkboxes = document.querySelectorAll('[data-permission-group="' + group + '"]');

            let shouldCheck = false;

            checkboxes.forEach(function(checkbox) {
                if (!checkbox.checked) {
                    shouldCheck = true;
                }
            });

            checkboxes.forEach(function(checkbox) {
                checkbox.checked = shouldCheck;
            });
        }
    </script>
@endpush
