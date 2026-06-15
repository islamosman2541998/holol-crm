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
        <label class="form-label">الاسم</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $user?->name) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">البريد الإلكتروني</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $user?->email) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">الموبايل</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user?->phone) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">الدور</label>

        <select name="role" class="form-select" required>
            <option value="">اختار الدور</option>

            @foreach ($roles as $role)
                <option value="{{ $role->name }}" @selected(old('role', $selectedRole) === $role->name)>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>

        @if ($roles->isEmpty())
            <div class="text-danger small mt-2">
                لا يوجد أدوار مسجلة. شغّل Seeder الصلاحيات.
            </div>
        @endif
    </div>

    <div class="col-md-6">
        <label class="form-label">
            كلمة المرور
            @if ($user)
                <small class="text-muted">اتركها فارغة لو مش هتغيرها</small>
            @endif
        </label>
        <input type="password" name="password" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">تأكيد كلمة المرور</label>
        <input type="password" name="password_confirmation" class="form-control">
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="status" value="0">

            <input class="form-check-input" type="checkbox" name="status" value="1" id="status"
                @checked(old('status', $user?->status ?? true))>

            <label class="form-check-label" for="status">
                المستخدم نشط
            </label>
        </div>
    </div>
</div>
