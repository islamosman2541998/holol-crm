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
        <label class="form-label">عنوان المهمة</label>
        <input type="text"
               name="title"
               class="form-control"
               value="{{ old('title', $task?->title) }}"
               placeholder="مثال: متابعة العميل بخصوص عرض السعر">
    </div>

    <div class="col-md-4">
        <label class="form-label">المسؤولون عن المهمة</label>
        @php
            $selectedMemberIds = old(
                'assigned_member_ids',
                $task?->assignedMembers->pluck('id')->all()
                    ?? ($selectedMemberId ? [(int) $selectedMemberId] : [])
            );

            $memberOptions = $members->map(fn ($member) => [
                'id' => $member->id,
                'label' => trim(
                    $member->name
                    . ($member->team ? ' - ' . $member->team->name : '')
                    . ($member->job_title ? ' - ' . $member->job_title : '')
                ),
            ]);
        @endphp

        <div id="taskAssignedMemberWidget"
             data-members="{{ $memberOptions->values()->toJson() }}"
             data-selected="{{ json_encode(array_values(array_map('intval', $selectedMemberIds))) }}">
            <div id="taskAssignedMembersChips" class="d-flex flex-wrap gap-2 mb-2"></div>

            <select class="form-select" id="taskAssignedMemberPicker">
                <option value="">-- اختر عضو لإضافته --</option>
            </select>

            <div id="taskAssignedMemberInputs"></div>
        </div>

        <div class="form-text">اختر عضو من القائمة لإضافته كمسؤول، وكرر الاختيار لإضافة أكتر من عضو</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">المشروع المرتبط</label>
        <select name="project_id" class="form-select" id="taskProjectSelect">
            <option value="">بدون مشروع</option>

            @foreach (($projects ?? []) as $project)
                <option value="{{ $project->id }}"
                        data-client-id="{{ $project->client_id }}"
                        data-manager-id="{{ $project->manager_member_id }}"
                    @selected(old('project_id', $task?->project_id ?? ($selectedProjectId ?? null)) == $project->id)>
                    {{ $project->name }}

                    @if ($project->client)
                        - {{ $project->client->name }}
                    @endif
                </option>
            @endforeach
        </select>

        <div class="form-text">
            عند اختيار مشروع، سيتم ربط المهمة بعميل المشروع تلقائيًا، ولا يمكن اختيار Lead.
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">العميل المرتبط</label>
        <select name="client_id" class="form-select" id="taskClientSelect">
            <option value="">بدون عميل</option>

            @foreach ($clients as $client)
                <option value="{{ $client->id }}"
                    @selected(old('client_id', $task?->client_id ?? ($selectedClientId ?? null)) == $client->id)>
                    {{ $client->name }}

                    @if ($client->company)
                        - {{ $client->company }}
                    @endif
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Lead مرتبط</label>
        <select name="lead_id" class="form-select" id="taskLeadSelect">
            <option value="">بدون Lead</option>

            @foreach ($leads as $lead)
                <option value="{{ $lead->id }}"
                    @selected(old('lead_id', $task?->lead_id ?? ($selectedLeadId ?? null)) == $lead->id)>
                    {{ $lead->name }}

                    @if ($lead->company)
                        - {{ $lead->company }}
                    @endif
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">الأولوية</label>
        <select name="priority" class="form-select">
            <option value="low" @selected(old('priority', $task?->priority ?? 'medium') === 'low')>
                منخفضة
            </option>

            <option value="medium" @selected(old('priority', $task?->priority ?? 'medium') === 'medium')>
                متوسطة
            </option>

            <option value="high" @selected(old('priority', $task?->priority) === 'high')>
                عالية
            </option>

            <option value="urgent" @selected(old('priority', $task?->priority) === 'urgent')>
                عاجلة
            </option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">الحالة</label>
        <select name="status" class="form-select">
            <option value="new" @selected(old('status', $task?->status ?? 'new') === 'new')>
                جديدة
            </option>

            <option value="in_progress" @selected(old('status', $task?->status) === 'in_progress')>
                قيد التنفيذ
            </option>

            <option value="review" @selected(old('status', $task?->status) === 'review')>
                في المراجعة
            </option>

            <option value="completed" @selected(old('status', $task?->status) === 'completed')>
                مكتملة
            </option>

            <option value="cancelled" @selected(old('status', $task?->status) === 'cancelled')>
                ملغية
            </option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">تاريخ البداية</label>
        <input type="datetime-local"
               name="start_at"
               class="form-control"
               value="{{ old('start_at', $task?->start_at?->format('Y-m-d\TH:i')) }}">
    </div>

    <div class="col-md-4">
        <label class="form-label">تاريخ التسليم</label>
        <input type="datetime-local"
               name="due_at"
               class="form-control"
               value="{{ old('due_at', $task?->due_at?->format('Y-m-d\TH:i')) }}">
    </div>

    <div class="col-12">
        <label class="form-label">وصف المهمة</label>
        <textarea name="description"
                  rows="4"
                  class="form-control"
                  placeholder="اكتب تفاصيل المهمة">{{ old('description', $task?->description) }}</textarea>
    </div>

    <div class="col-12">
        <label class="form-label">ملاحظات داخلية</label>
        <textarea name="notes"
                  rows="3"
                  class="form-control">{{ old('notes', $task?->notes) }}</textarea>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const projectSelect = document.getElementById('taskProjectSelect');
            const clientSelect = document.getElementById('taskClientSelect');
            const leadSelect = document.getElementById('taskLeadSelect');

            const widget = document.getElementById('taskAssignedMemberWidget');
            const chipsContainer = document.getElementById('taskAssignedMembersChips');
            const picker = document.getElementById('taskAssignedMemberPicker');
            const inputsContainer = document.getElementById('taskAssignedMemberInputs');

            const allMembers = JSON.parse(widget.dataset.members);
            let selectedIds = JSON.parse(widget.dataset.selected)
                .filter(id => allMembers.some(member => member.id === id));

            function renderAssignedMembers() {
                chipsContainer.innerHTML = '';
                inputsContainer.innerHTML = '';

                selectedIds.forEach(function (id) {
                    const member = allMembers.find(m => m.id === id);

                    if (!member) {
                        return;
                    }

                    const chip = document.createElement('span');
                    chip.className = 'badge rounded-pill text-bg-light border d-inline-flex align-items-center gap-2 py-2 px-3';
                    chip.innerHTML = '<span></span><button type="button" class="btn-close" aria-label="إزالة" style="font-size:.6rem;"></button>';
                    chip.querySelector('span').textContent = member.label;
                    chip.querySelector('button').addEventListener('click', function () {
                        selectedIds = selectedIds.filter(existingId => existingId !== id);
                        renderAssignedMembers();
                    });
                    chipsContainer.appendChild(chip);

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'assigned_member_ids[]';
                    input.value = id;
                    inputsContainer.appendChild(input);
                });

                picker.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = '-- اختر عضو لإضافته --';
                picker.appendChild(placeholder);

                allMembers
                    .filter(member => ! selectedIds.includes(member.id))
                    .forEach(function (member) {
                        const option = document.createElement('option');
                        option.value = member.id;
                        option.textContent = member.label;
                        picker.appendChild(option);
                    });
            }

            function addAssignedMember(id) {
                if (selectedIds.includes(id) || ! allMembers.some(member => member.id === id)) {
                    return;
                }

                selectedIds.push(id);
                renderAssignedMembers();
            }

            picker.addEventListener('change', function () {
                if (picker.value) {
                    addAssignedMember(parseInt(picker.value, 10));
                }
            });

            renderAssignedMembers();

            if (!clientSelect || !leadSelect) {
                return;
            }

            function syncTaskRelationSelects() {
                const selectedProjectOption = projectSelect && projectSelect.value
                    ? projectSelect.options[projectSelect.selectedIndex]
                    : null;

                const selectedProjectClientId = selectedProjectOption
                    ? selectedProjectOption.dataset.clientId
                    : '';

                const selectedProjectManagerId = selectedProjectOption
                    ? selectedProjectOption.dataset.managerId
                    : '';

                if (projectSelect && projectSelect.value) {
                    if (selectedProjectClientId) {
                        clientSelect.value = selectedProjectClientId;
                    }

                    if (selectedProjectManagerId && selectedIds.length === 0) {
                        addAssignedMember(parseInt(selectedProjectManagerId, 10));
                    }

                    leadSelect.value = '';
                    leadSelect.disabled = true;
                    clientSelect.disabled = true;

                    return;
                }

                clientSelect.disabled = false;
                leadSelect.disabled = false;

                if (clientSelect.value) {
                    leadSelect.value = '';
                    leadSelect.disabled = true;
                }

                if (leadSelect.value) {
                    clientSelect.value = '';
                    clientSelect.disabled = true;
                }
            }

            if (projectSelect) {
                projectSelect.addEventListener('change', syncTaskRelationSelects);
            }

            clientSelect.addEventListener('change', syncTaskRelationSelects);
            leadSelect.addEventListener('change', syncTaskRelationSelects);

            syncTaskRelationSelects();
        });
    </script>
@endpush