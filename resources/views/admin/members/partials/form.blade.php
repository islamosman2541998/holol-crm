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
        <label class="form-label">اسم العضو</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $member?->name) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">حساب الدخول المرتبط</label>
        <select name="user_id" class="form-select">
            <option value="">بدون حساب دخول</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(old('user_id', $member?->user_id) == $user->id)>
                    {{ $user->name }} - {{ $user->email }}
                </option>
            @endforeach
        </select>
        <div class="form-text">
            لو العضو له حساب دخول، هياخد صلاحيات الفريق تلقائيًا.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">المدير المباشر</label>
        <select name="manager_id" class="form-select" id="memberManagerSelect">
            <option value="">بدون مدير مباشر</option>
            @foreach ($managers as $manager)
                <option value="{{ $manager->id }}" data-team-id="{{ $manager->team_id }}" @selected(old('manager_id', $member?->manager_id) == $manager->id)>
                    {{ $manager->name }}
                    @if ($manager->team)
                        - {{ $manager->team->name }}
                    @endif
                </option>
            @endforeach
        </select>

        <div class="form-text">
            سيتم عرض مديرين نفس الفريق أولًا.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">المدير المباشر</label>
        <select name="manager_id" class="form-select">
            <option value="">بدون مدير مباشر</option>
            @foreach ($managers as $manager)
                <option value="{{ $manager->id }}" @selected(old('manager_id', $member?->manager_id) == $manager->id)>
                    {{ $manager->name }}
                    @if ($manager->team)
                        - {{ $manager->team->name }}
                    @endif
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">الوظيفة</label>
        <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $member?->job_title) }}"
            placeholder="مثال: Sales Executive">
    </div>

    <div class="col-md-6">
        <label class="form-label">القسم</label>
        <input type="text" name="department" class="form-control"
            value="{{ old('department', $member?->department) }}" placeholder="مثال: Sales">
    </div>

    <div class="col-md-4">
        <label class="form-label">الإيميل</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $member?->email) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">الهاتف</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $member?->phone) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">الموبايل</label>
        <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $member?->mobile) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">تاريخ التعيين</label>
        <input type="date" name="hire_date" class="form-control"
            value="{{ old('hire_date', $member?->hire_date?->format('Y-m-d')) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">الحالة</label>
        <select name="status" class="form-select">
            <option value="active" @selected(old('status', $member?->status ?? 'active') === 'active')>نشط</option>
            <option value="inactive" @selected(old('status', $member?->status) === 'inactive')>غير نشط</option>
            <option value="on_leave" @selected(old('status', $member?->status) === 'on_leave')>إجازة</option>
            <option value="left" @selected(old('status', $member?->status) === 'left')>ترك العمل</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label d-block">هل العضو مدير؟</label>

        <input type="hidden" name="is_manager" value="0">

        <div class="form-check form-switch mt-2">
            <input class="form-check-input" type="checkbox" name="is_manager" value="1" id="isManagerSwitch"
                @checked(old('is_manager', $member?->is_manager ?? false))>

            <label class="form-check-label" for="isManagerSwitch">
                نعم، مدير
            </label>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">صورة العضو</label>
        <input type="file" name="image" class="form-control" accept="image/*">

        @if ($member?->image)
            <div class="mt-2">
                <img src="{{ asset('storage/' . $member->image) }}" alt="{{ $member->name }}"
                    style="width: 70px; height: 70px; object-fit: cover; border-radius: 12px;">
            </div>
        @endif
    </div>

    <div class="col-12">
        <label class="form-label">ملاحظات</label>
        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $member?->notes) }}</textarea>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const teamSelect = document.getElementById('memberTeamSelect');
            const managerSelect = document.getElementById('memberManagerSelect');

            if (!teamSelect || !managerSelect) {
                return;
            }

            const originalOptions = Array.from(managerSelect.options).map(function (option) {
                return {
                    value: option.value,
                    text: option.text,
                    teamId: option.dataset.teamId || '',
                    selected: option.selected
                };
            });

            function rebuildManagerOptions() {
                const selectedTeamId = teamSelect.value;
                const currentManagerId = managerSelect.value;

                managerSelect.innerHTML = '';

                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = 'بدون مدير مباشر';
                managerSelect.appendChild(emptyOption);

                let filteredManagers = originalOptions.filter(function (option) {
                    if (!option.value) {
                        return false;
                    }

                    if (!selectedTeamId) {
                        return true;
                    }

                    return option.teamId === selectedTeamId;
                });

                if (selectedTeamId && filteredManagers.length === 0) {
                    filteredManagers = originalOptions.filter(function (option) {
                        return option.value;
                    });
                }

                filteredManagers.forEach(function (optionData) {
                    const option = document.createElement('option');

                    option.value = optionData.value;
                    option.textContent = optionData.text;
                    option.dataset.teamId = optionData.teamId;

                    if (optionData.value === currentManagerId || optionData.selected) {
                        option.selected = true;
                    }

                    managerSelect.appendChild(option);
                });

                const stillExists = Array.from(managerSelect.options).some(function (option) {
                    return option.value === currentManagerId;
                });

                if (!stillExists) {
                    managerSelect.value = '';
                }
            }

            teamSelect.addEventListener('change', rebuildManagerOptions);

            rebuildManagerOptions();
        });
    </script>
@endpush