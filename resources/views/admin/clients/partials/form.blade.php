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
        <label class="form-label">اسم العميل</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $client?->name) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">اسم الشركة</label>
        <input type="text" name="company" class="form-control" value="{{ old('company', $client?->company) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">البريد الإلكتروني</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $client?->email) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">الموبايل</label>
        <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $client?->mobile) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">الهاتف</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $client?->phone) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">المدينة</label>
        <input type="text" name="city" class="form-control" value="{{ old('city', $client?->city) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">مصدر العميل</label>
        <input type="text" name="source" class="form-control" placeholder="Facebook / Referral / Call / Website"
            value="{{ old('source', $client?->source) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">الموظف المسؤول</label>
        <select name="assigned_to" class="form-select">
            <option value="">بدون موظف مسؤول</option>

            @foreach ($members as $member)
                <option value="{{ $member->user_id }}" @selected(old('assigned_to', $client?->assigned_to) == $member->user_id)>
                    {{ $member->name }}
                    @if ($member->team)
                        - {{ $member->team->name }}
                    @endif
                    @if ($member->job_title)
                        - {{ $member->job_title }}
                    @endif
                </option>
            @endforeach
        </select>

        <div class="form-text">
            يظهر هنا الأعضاء المرتبطين بحساب دخول فقط.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">حالة العميل</label>
        <select name="status" class="form-select">
            <option value="new" @selected(old('status', $client?->status ?? 'new') === 'new')>جديد</option>
            <option value="active" @selected(old('status', $client?->status) === 'active')>نشط</option>
            <option value="inactive" @selected(old('status', $client?->status) === 'inactive')>غير نشط</option>
            <option value="lost" @selected(old('status', $client?->status) === 'lost')>مفقود</option>
        </select>
    </div>

    <div class="col-12">
        <label class="form-label">ملاحظات</label>
        <textarea name="notes" rows="4" class="form-control">{{ old('notes', $client?->notes) }}</textarea>
    </div>
</div>
