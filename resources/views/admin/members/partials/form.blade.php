@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm">
        <div class="fw-semibold mb-2">
            <i class="bi bi-exclamation-triangle"></i>
            راجعي البيانات التالية:
        </div>

        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $selectedUserId = old('user_id', $member?->user_id);
@endphp

<div class="row g-4">

    {{-- Login account section --}}
    <div class="col-12">
        <div class="card border-0 bg-light">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                    <div>
                        <h6 class="mb-1">
                            <i class="bi bi-person-badge"></i>
                            حساب الدخول
                        </h6>
                        <div class="text-muted small">
                            اختار حساب موجود أو أنشئي حساب دخول جديد للعضو.
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">حساب الدخول المرتبط</label>

                        <select name="user_id" class="form-select" id="memberUserSelect">
                            <option value="">بدون حساب دخول / إنشاء حساب جديد</option>

                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        data-phone="{{ $user->phone }}"
                                        data-image="{{ $user->image ? asset('storage/' . $user->image) : '' }}"
                                    @selected((string) $selectedUserId === (string) $user->id)>
                                    {{ $user->name }} - {{ $user->email }}
                                </option>
                            @endforeach
                        </select>

                        <div class="form-text">
                            لو اخترت حساب موجود، بيانات الاسم والإيميل والموبايل هتتسحب منه تلقائيًا.
                        </div>
                    </div>

                    <div class="col-md-6" id="createLoginBox">
                        <label class="form-label d-block">إنشاء حساب دخول</label>

                        <input type="hidden" name="create_login_account" value="0">

                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="create_login_account"
                                   value="1"
                                   id="createLoginAccountSwitch"
                                   @checked(old('create_login_account', $member ? false : true))>

                            <label class="form-check-label" for="createLoginAccountSwitch">
                                إنشاء حساب دخول لهذا العضو
                            </label>
                        </div>

                        <div class="form-text">
                            فعل لو العضو هيخش السيستم ويشوف مهامه ويحدثها.
                        </div>
                    </div>
                </div>

                <div id="linkedUserPreview" class="alert alert-primary border-0 mt-3 mb-0 d-none">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 46px; height: 46px; border-radius: 50%; background:#fff; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                            <img id="linkedUserImage"
                                 src=""
                                 alt=""
                                 style="width:100%; height:100%; object-fit:cover; display:none;">

                            <i id="linkedUserIcon" class="bi bi-person fs-4"></i>
                        </div>

                        <div>
                            <div class="fw-semibold" id="linkedUserName">-</div>
                            <div class="small" id="linkedUserEmail">-</div>
                            <div class="small" id="linkedUserPhone">-</div>
                        </div>
                    </div>

                    <div class="small mt-2">
                        سيتم استخدام بيانات هذا الحساب تلقائيًا للعضو، وكملي فقط بيانات الفريق والوظيفة.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Identity data --}}
    <div class="col-12" id="manualIdentitySection">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bi bi-person-lines-fill"></i>
                    بيانات العضو الأساسية
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">اسم العضو</label>
                        <input type="text"
                               name="name"
                               id="memberNameInput"
                               class="form-control"
                               value="{{ old('name', $member?->name) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الإيميل</label>
                        <input type="email"
                               name="email"
                               id="memberEmailInput"
                               class="form-control"
                               value="{{ old('email', $member?->email) }}">

                        <div class="form-text">
                            لو هتنشأ حساب دخول جديد، الإيميل ده هيكون إيميل تسجيل الدخول.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الهاتف</label>
                        <input type="text"
                               name="phone"
                               id="memberPhoneInput"
                               class="form-control"
                               value="{{ old('phone', $member?->phone) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الموبايل</label>
                        <input type="text"
                               name="mobile"
                               id="memberMobileInput"
                               class="form-control"
                               value="{{ old('mobile', $member?->mobile) }}">
                    </div>

                    <div class="col-md-6 login-password-field">
                        <label class="form-label">باسورد حساب الدخول</label>
                        <input type="password"
                               name="login_password"
                               class="form-control"
                               autocomplete="new-password">

                        <div class="form-text">
                            مطلوب فقط عند إنشاء حساب دخول جديد. أقل شيء 8 حروف.
                        </div>
                    </div>

                    <div class="col-md-6 login-password-field">
                        <label class="form-label">تأكيد الباسورد</label>
                        <input type="password"
                               name="login_password_confirmation"
                               class="form-control"
                               autocomplete="new-password">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Work data --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bi bi-diagram-3"></i>
                    بيانات العمل والفريق
                </h6>
            </div>

            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">الفريق / القسم التابع له العضو</label>

                        <select name="team_id" class="form-select" id="memberTeamSelect">
                            <option value="">بدون فريق</option>

                            @foreach ($teams as $team)
                                <option value="{{ $team->id }}"
                                    @selected(old('team_id', $member?->team_id) == $team->id)>
                                    {{ $team->name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="form-text">
                            العضو بياخد صلاحيات الفريق تلقائيًا لو عنده حساب دخول.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">المدير المباشر للعضو</label>

                        <select name="manager_id" class="form-select" id="memberManagerSelect">
                            <option value="">بدون مدير مباشر</option>

                            @foreach ($managers as $manager)
                                @continue($member?->id && $manager->id === $member->id)

                                <option value="{{ $manager->id }}"
                                        data-team-id="{{ $manager->team_id }}"
                                    @selected(old('manager_id', $member?->manager_id) == $manager->id)>
                                    {{ $manager->name }}

                                    @if ($manager->team)
                                        - {{ $manager->team->name }}
                                    @endif

                                    @if ($manager->job_title)
                                        - {{ $manager->job_title }}
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        <div class="form-text">
                            الشخص المسؤول عن متابعة العضو بشكل يومي.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">الوظيفة</label>
                        <input type="text"
                               name="job_title"
                               class="form-control"
                               value="{{ old('job_title', $member?->job_title) }}"
                               placeholder="مثال: Front End Developer">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">القسم المكتوب داخل بيانات العضو</label>
                        <input type="text"
                               name="department"
                               class="form-control"
                               value="{{ old('department', $member?->department) }}"
                               placeholder="مثال: Programming">

                        <div class="form-text">
                            ده وصف نصي فقط. الاختيار الأساسي للفريق من حقل الفريق.
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">تاريخ التعيين</label>
                        <input type="date"
                               name="hire_date"
                               class="form-control"
                               value="{{ old('hire_date', $member?->hire_date?->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">الحالة</label>

                        <select name="status" class="form-select">
                            <option value="active" @selected(old('status', $member?->status ?? 'active') === 'active')>
                                نشط
                            </option>

                            <option value="inactive" @selected(old('status', $member?->status) === 'inactive')>
                                غير نشط
                            </option>

                            <option value="on_leave" @selected(old('status', $member?->status) === 'on_leave')>
                                إجازة
                            </option>

                            <option value="left" @selected(old('status', $member?->status) === 'left')>
                                ترك العمل
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label d-block">دور العضو داخل الفريق</label>

                        <input type="hidden" name="is_manager" value="0">

                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="is_manager"
                                   value="1"
                                   id="isManagerSwitch"
                                @checked(old('is_manager', $member?->is_manager ?? false))>

                            <label class="form-check-label" for="isManagerSwitch">
                                هذا العضو له دور إداري
                            </label>
                        </div>

                        <div class="form-text">
                            مدير الفريق الأساسي يتم تحديده من صفحة الفريق.
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">صورة العضو</label>

                        <input type="file"
                               name="image"
                               class="form-control"
                               accept="image/*">

                        @if ($member?->image)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $member->image) }}"
                                     alt="{{ $member->name }}"
                                     style="width: 70px; height: 70px; object-fit: cover; border-radius: 12px;">
                            </div>
                        @endif
                    </div>

                    <div class="col-12">
                        <label class="form-label">ملاحظات</label>
                        <textarea name="notes"
                                  rows="3"
                                  class="form-control">{{ old('notes', $member?->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const teamSelect = document.getElementById('memberTeamSelect');
            const managerSelect = document.getElementById('memberManagerSelect');

            const userSelect = document.getElementById('memberUserSelect');
            const createLoginBox = document.getElementById('createLoginBox');
            const createLoginSwitch = document.getElementById('createLoginAccountSwitch');
            const passwordFields = document.querySelectorAll('.login-password-field');

            const manualIdentitySection = document.getElementById('manualIdentitySection');

            const nameInput = document.getElementById('memberNameInput');
            const emailInput = document.getElementById('memberEmailInput');
            const phoneInput = document.getElementById('memberPhoneInput');
            const mobileInput = document.getElementById('memberMobileInput');

            const linkedUserPreview = document.getElementById('linkedUserPreview');
            const linkedUserName = document.getElementById('linkedUserName');
            const linkedUserEmail = document.getElementById('linkedUserEmail');
            const linkedUserPhone = document.getElementById('linkedUserPhone');
            const linkedUserImage = document.getElementById('linkedUserImage');
            const linkedUserIcon = document.getElementById('linkedUserIcon');

            function getSelectedUserOption() {
                if (!userSelect || !userSelect.value) {
                    return null;
                }

                return userSelect.options[userSelect.selectedIndex];
            }

            function syncLinkedUserData() {
                const selectedOption = getSelectedUserOption();
                const hasLinkedUser = !!selectedOption;

                if (hasLinkedUser) {
                    const userName = selectedOption.dataset.name || '';
                    const userEmail = selectedOption.dataset.email || '';
                    const userPhone = selectedOption.dataset.phone || '';
                    const userImage = selectedOption.dataset.image || '';

                    if (nameInput) nameInput.value = userName;
                    if (emailInput) emailInput.value = userEmail;
                    if (phoneInput) phoneInput.value = userPhone;
                    if (mobileInput) mobileInput.value = userPhone;

                    if (manualIdentitySection) {
                        manualIdentitySection.style.display = 'none';
                    }

                    if (createLoginBox) {
                        createLoginBox.style.display = 'none';
                    }

                    if (createLoginSwitch) {
                        createLoginSwitch.checked = false;
                        createLoginSwitch.disabled = true;
                    }

                    passwordFields.forEach(function (field) {
                        field.style.display = 'none';
                    });

                    if (linkedUserPreview) {
                        linkedUserPreview.classList.remove('d-none');
                    }

                    if (linkedUserName) {
                        linkedUserName.textContent = userName || '-';
                    }

                    if (linkedUserEmail) {
                        linkedUserEmail.textContent = userEmail || '-';
                    }

                    if (linkedUserPhone) {
                        linkedUserPhone.textContent = userPhone ? 'موبايل: ' + userPhone : 'بدون موبايل';
                    }

                    if (linkedUserImage && linkedUserIcon) {
                        if (userImage) {
                            linkedUserImage.src = userImage;
                            linkedUserImage.style.display = 'block';
                            linkedUserIcon.style.display = 'none';
                        } else {
                            linkedUserImage.removeAttribute('src');
                            linkedUserImage.style.display = 'none';
                            linkedUserIcon.style.display = 'inline-block';
                        }
                    }

                    return;
                }

                if (manualIdentitySection) {
                    manualIdentitySection.style.display = 'block';
                }

                if (createLoginBox) {
                    createLoginBox.style.display = 'block';
                }

                if (createLoginSwitch) {
                    createLoginSwitch.disabled = false;
                }

                if (linkedUserPreview) {
                    linkedUserPreview.classList.add('d-none');
                }

                toggleLoginPasswordFields();
            }

            function toggleLoginPasswordFields() {
                if (!createLoginSwitch || !userSelect) {
                    return;
                }

                const hasLinkedUser = !!userSelect.value;
                const shouldShowPasswordFields = !hasLinkedUser && createLoginSwitch.checked;

                passwordFields.forEach(function (field) {
                    field.style.display = shouldShowPasswordFields ? 'block' : 'none';
                });
            }

            if (userSelect) {
                userSelect.addEventListener('change', function () {
                    syncLinkedUserData();
                    toggleLoginPasswordFields();
                });
            }

            if (createLoginSwitch) {
                createLoginSwitch.addEventListener('change', toggleLoginPasswordFields);
            }

            syncLinkedUserData();

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

                let sameTeamManagers = originalOptions.filter(function (option) {
                    if (!option.value) {
                        return false;
                    }

                    return selectedTeamId && option.teamId === selectedTeamId;
                });

                let otherManagers = originalOptions.filter(function (option) {
                    if (!option.value) {
                        return false;
                    }

                    return !selectedTeamId || option.teamId !== selectedTeamId;
                });

                function appendGroup(label, options) {
                    if (!options.length) {
                        return;
                    }

                    const group = document.createElement('optgroup');
                    group.label = label;

                    options.forEach(function (optionData) {
                        const option = document.createElement('option');

                        option.value = optionData.value;
                        option.textContent = optionData.text;
                        option.dataset.teamId = optionData.teamId;

                        if (optionData.value === currentManagerId || optionData.selected) {
                            option.selected = true;
                        }

                        group.appendChild(option);
                    });

                    managerSelect.appendChild(group);
                }

                if (selectedTeamId) {
                    appendGroup('مديرين من نفس الفريق', sameTeamManagers);
                    appendGroup('مديرين من فرق أخرى', otherManagers);
                } else {
                    appendGroup('كل المديرين المتاحين', otherManagers);
                }

                const stillExists = Array.from(managerSelect.querySelectorAll('option')).some(function (option) {
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