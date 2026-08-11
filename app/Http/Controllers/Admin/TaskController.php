<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Member;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Project;

class TaskController extends Controller
{
    public function index()
    {
        return view('admin.tasks.index');
    }

    public function create()
    {
        $members = Member::query()
            ->where('status', 'active')
            ->with(['team', 'user'])
            ->orderBy('name')
            ->get();

        $clients = Client::query()
            ->orderBy('name')
            ->get();

        $leads = Lead::query()
            ->where('status', '!=', 'converted')
            ->orderBy('name')
            ->get();

        $projects = Project::query()
            ->with(['client', 'team', 'manager'])
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->orderBy('name')
            ->get();

        $selectedClientId = request('client_id');
        $selectedLeadId = request('lead_id');
        $selectedMemberId = request('assigned_member_id');
        $selectedProjectId = request('project_id');

        return view('admin.tasks.create', compact(
            'members',
            'clients',
            'leads',
            'projects',
            'selectedClientId',
            'selectedLeadId',
            'selectedMemberId',
            'selectedProjectId'
        ));
    }
    public function store(Request $request)
    {
        $data = $this->validateTask($request);

        $memberIds = $data['assigned_member_ids'] ?? [];
        unset($data['assigned_member_ids']);

        $data['created_by'] = auth()->id();

        if (($data['status'] ?? null) === 'completed') {
            $data['completed_at'] = now();
        }

        $task = Task::query()->create($data);

        $task->assignedMembers()->sync($memberIds);

        $task->logActivity(
            event: 'created',
            title: 'تم إنشاء المهمة',
            description: 'تم إنشاء مهمة جديدة: ' . $task->title,
            newValues: $task->only([
                'client_id',
                'lead_id',
                'project_id',
                'title',
                'priority',
                'status',
                'start_at',
                'due_at',
            ]) + [
                'assigned_members' => $task->assignedMembers()->pluck('name')->all(),
            ]
        );

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'تم إضافة المهمة بنجاح');
    }
    public function downloadAttachment(Task $task, TaskAttachment $attachment)
    {
        $this->authorizeTaskAccess($task);

        abort_unless((int) $attachment->task_id === (int) $task->id, 404);

        abort_if($attachment->is_link, 404);

        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        return Storage::disk('local')->download($attachment->file_path, $attachment->file_name);
    }

    private function authorizeTaskAccess(Task $task): void
    {
        $user = auth()->user();

        if ($user->can('tasks.view_all')) {
            return;
        }

        $member = $user->member;

        abort_unless($member, 403);

        if ($member->is_manager && $member->team_id) {
            $isTeamTask = $task->assignedMembers()
                ->where('team_id', $member->team_id)
                ->exists();

            abort_unless($isTeamTask, 403);

            return;
        }

        abort_unless(
            $task->assignedMembers()->where('members.id', $member->id)->exists(),
            403
        );
    }

    public function show(Task $task)
    {
        $this->authorizeTaskAccess($task);
        $task->load([
            'assignedMembers.team',
            'assignedMembers.user',
            'creator',
            'client',
            'lead',
            'project.client',
        ]);

        return view('admin.tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorizeTaskAccess($task);

        $assignedMemberIds = $task->assignedMembers()->pluck('members.id')->all();

        $members = Member::query()
            ->with(['team', 'user'])
            ->where(function ($query) use ($assignedMemberIds) {
                $query->where('status', 'active');

                if ($assignedMemberIds) {
                    $query->orWhereIn('id', $assignedMemberIds);
                }
            })
            ->orderBy('name')
            ->get();

        $clients = Client::query()
            ->orderBy('name')
            ->get();

        $leads = Lead::query()
            ->where(function ($query) use ($task) {
                $query->where('status', '!=', 'converted');

                if ($task->lead_id) {
                    $query->orWhere('id', $task->lead_id);
                }
            })
            ->orderBy('name')
            ->get();

        $projects = Project::query()
            ->with(['client', 'team', 'manager'])
            ->where(function ($query) use ($task) {
                $query->whereNotIn('status', ['completed', 'cancelled']);

                if ($task->project_id) {
                    $query->orWhere('id', $task->project_id);
                }
            })
            ->orderBy('name')
            ->get();

        return view('admin.tasks.edit', compact(
            'task',
            'members',
            'clients',
            'leads',
            'projects'
        ));
    }
    public function update(Request $request, Task $task)
    {
        $this->authorizeTaskAccess($task);
        $oldValues = $task->only([
            'client_id',
            'lead_id',
            'project_id',
            'title',
            'description',
            'priority',
            'status',
            'start_at',
            'due_at',
            'completed_at',
            'notes',
        ]) + [
            'assigned_members' => $task->assignedMembers()->pluck('name')->all(),
        ];

        $data = $this->validateTask($request, $task);

        $memberIds = $data['assigned_member_ids'] ?? [];
        unset($data['assigned_member_ids']);

        if (($data['status'] ?? null) === 'completed' && ! $task->completed_at) {
            $data['completed_at'] = now();
        }

        if (($data['status'] ?? null) !== 'completed') {
            $data['completed_at'] = null;
        }

        $task->update($data);

        $task->assignedMembers()->sync($memberIds);

        $task->logActivity(
            event: 'updated',
            title: 'تم تحديث المهمة',
            description: 'تم تحديث بيانات المهمة: ' . $task->title,
            oldValues: $oldValues,
            newValues: $task->only([
                'client_id',
                'lead_id',
                'project_id',
                'title',
                'description',
                'priority',
                'status',
                'start_at',
                'due_at',
                'completed_at',
                'notes',
            ]) + [
                'assigned_members' => $task->assignedMembers()->pluck('name')->all(),
            ]
        );

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'تم تحديث المهمة بنجاح');
    }

    public function destroy(Task $task)
    {
        $this->authorizeTaskAccess($task);
        $task->logActivity(
            event: 'deleted',
            title: 'تم حذف المهمة',
            description: 'تم حذف المهمة: ' . $task->title,
            oldValues: $task->toArray()
        );

        $task->delete();

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', 'تم حذف المهمة بنجاح');
    }

    public function changeStatus(Request $request, Task $task)
    {
        abort_unless(auth()->user()->can('tasks.change_status'), 403);
        $this->authorizeTaskAccess($task);
        $data = $request->validate([
            'status' => ['required', 'in:new,in_progress,review,completed,cancelled'],
        ]);

        $oldStatus = $task->status;

        $task->update([
            'status' => $data['status'],
            'completed_at' => $data['status'] === 'completed' ? now() : null,
        ]);

        $task->logActivity(
            event: 'status_changed',
            title: 'تم تغيير حالة المهمة',
            description: 'تم تغيير حالة المهمة من ' .
                $this->statusLabel($oldStatus) .
                ' إلى ' .
                $this->statusLabel($task->status),
            oldValues: [
                'status' => $oldStatus,
            ],
            newValues: [
                'status' => $task->status,
            ]
        );

        return back()->with('success', 'تم تحديث حالة المهمة بنجاح');
    }

    private function validateTask(Request $request, ?Task $task = null): array
    {
        $data = $request->validate([
            'assigned_member_ids' => ['nullable', 'array'],
            'assigned_member_ids.*' => ['integer', 'exists:members,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'status' => ['required', 'in:new,in_progress,review,completed,cancelled'],
            'start_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'notes' => ['nullable', 'string'],
        ], [
            'title.required' => 'عنوان المهمة مطلوب',
            'priority.required' => 'الأولوية مطلوبة',
            'status.required' => 'حالة المهمة مطلوبة',
            'due_at.after_or_equal' => 'تاريخ التسليم يجب أن يكون بعد أو يساوي تاريخ البداية',
        ]);

        $hasClient = ! empty($data['client_id']);
        $hasLead = ! empty($data['lead_id']);
        $hasProject = ! empty($data['project_id']);

        if ($hasProject) {
            $project = Project::query()->findOrFail($data['project_id']);

            $data['client_id'] = $project->client_id;
            $data['lead_id'] = null;

            $hasClient = true;
            $hasLead = false;
        }

        if (! $hasClient && ! $hasLead) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'client_id' => 'يجب اختيار عميل أو Lead أو مشروع للمهمة',
            ]);
        }

        if ($hasClient && $hasLead) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'client_id' => 'لا يمكن ربط المهمة بعميل و Lead في نفس الوقت',
                'lead_id' => 'لا يمكن ربط المهمة بعميل و Lead في نفس الوقت',
            ]);
        }

        if ($hasProject && $hasLead) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'project_id' => 'لا يمكن ربط المهمة بمشروع و Lead في نفس الوقت',
                'lead_id' => 'لا يمكن ربط المهمة بمشروع و Lead في نفس الوقت',
            ]);
        }

        return $data;
    }

    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'new' => 'جديدة',
            'in_progress' => 'قيد التنفيذ',
            'review' => 'في المراجعة',
            'completed' => 'مكتملة',
            'cancelled' => 'ملغية',
            default => 'غير معروف',
        };
    }
}
