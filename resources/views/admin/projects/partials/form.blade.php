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
    <div class="col-md-8">
        <label class="form-label">اسم المشروع</label>
        <input type="text"
               name="name"
               class="form-control"
               value="{{ old('name', $project?->name) }}"
               placeholder="مثال: SEO Monthly Retainer">
    </div>

    <div class="col-md-4">
        <label class="form-label">كود المشروع</label>
        <input type="text"
               name="code"
               class="form-control"
               value="{{ old('code', $project?->code) }}"
               placeholder="اختياري - يتولد تلقائيًا">
    </div>

    <div class="col-md-6">
        <label class="form-label">العميل</label>
        <select name="client_id" class="form-select">
            <option value="">اختر العميل</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}"
                    @selected(old('client_id', $project?->client_id ?? $selectedClientId ?? null) == $client->id)>
                    {{ $client->name }}
                    @if ($client->company)
                        - {{ $client->company }}
                    @endif
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">الخدمة</label>
        <select name="service_id" class="form-select">
            <option value="">بدون خدمة محددة</option>
            @foreach ($services as $service)
                <option value="{{ $service->id }}"
                    @selected(old('service_id', $project?->service_id) == $service->id)>
                    {{ $service->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">الفريق</label>
        <select name="team_id" class="form-select" id="projectTeamSelect">
            <option value="">بدون فريق</option>
            @foreach ($teams as $team)
                <option value="{{ $team->id }}"
                    @selected(old('team_id', $project?->team_id ?? $selectedTeamId ?? null) == $team->id)>
                    {{ $team->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">مدير المشروع</label>
        <select name="manager_member_id" class="form-select" id="projectManagerSelect">
            <option value="">بدون مدير مشروع</option>
            @foreach ($members as $member)
                <option value="{{ $member->id }}"
                        data-team-id="{{ $member->team_id }}"
                    @selected(old('manager_member_id', $project?->manager_member_id ?? $selectedManagerId ?? null) == $member->id)>
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
            عند اختيار فريق، سيتم ترتيب المديرين حسب نفس الفريق.
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label">الأولوية</label>
        <select name="priority" class="form-select">
            <option value="low" @selected(old('priority', $project?->priority ?? 'medium') === 'low')>منخفضة</option>
            <option value="medium" @selected(old('priority', $project?->priority ?? 'medium') === 'medium')>متوسطة</option>
            <option value="high" @selected(old('priority', $project?->priority) === 'high')>عالية</option>
            <option value="urgent" @selected(old('priority', $project?->priority) === 'urgent')>عاجلة</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">الحالة</label>
        <select name="status" class="form-select">
            <option value="new" @selected(old('status', $project?->status ?? 'new') === 'new')>جديد</option>
            <option value="planning" @selected(old('status', $project?->status) === 'planning')>مرحلة التخطيط</option>
            <option value="in_progress" @selected(old('status', $project?->status) === 'in_progress')>قيد التنفيذ</option>
            <option value="on_hold" @selected(old('status', $project?->status) === 'on_hold')>متوقف مؤقتًا</option>
            <option value="completed" @selected(old('status', $project?->status) === 'completed')>مكتمل</option>
            <option value="cancelled" @selected(old('status', $project?->status) === 'cancelled')>ملغي</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">الميزانية</label>
        <input type="number"
               step="0.01"
               min="0"
               name="budget"
               class="form-control"
               value="{{ old('budget', $project?->budget) }}"
               placeholder="اختياري">
    </div>

    <div class="col-md-6">
        <label class="form-label">تاريخ البداية</label>
        <input type="date"
               name="start_date"
               class="form-control"
               value="{{ old('start_date', $project?->start_date?->format('Y-m-d')) }}">
    </div>

    <div class="col-md-6">
        <label class="form-label">تاريخ التسليم</label>
        <input type="date"
               name="due_date"
               class="form-control"
               value="{{ old('due_date', $project?->due_date?->format('Y-m-d')) }}">
    </div>

    <div class="col-12">
        <label class="form-label">وصف المشروع</label>
        <textarea name="description"
                  rows="4"
                  class="form-control"
                  placeholder="اكتب وصف المشروع">{{ old('description', $project?->description) }}</textarea>
    </div>

    <div class="col-12">
        <label class="form-label">ملاحظات داخلية</label>
        <textarea name="notes"
                  rows="3"
                  class="form-control">{{ old('notes', $project?->notes) }}</textarea>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const teamSelect = document.getElementById('projectTeamSelect');
            const managerSelect = document.getElementById('projectManagerSelect');

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
                emptyOption.textContent = 'بدون مدير مشروع';
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