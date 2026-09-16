<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Member;
use App\Models\Project;
use App\Models\ProjectAttachment;
use App\Models\Service;
use App\Models\Team;
use App\Traits\AuthorizesOwnedRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    use AuthorizesOwnedRecords;

    public function index()
    {
        return view('admin.projects.index');
    }

    public function create()
    {
        $clientQuery = Client::query();
        $this->applyOwnedRecordScope($clientQuery, 'clients.view_all', 'assigned_to');
        $clients = $clientQuery->orderBy('name')->get();

        $services = Service::query()
            ->orderBy('name')
            ->get();

        $teams = Team::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $members = Member::query()
            ->where('status', 'active')
            ->with(['team', 'user'])
            ->orderBy('name')
            ->get();

        $selectedClientId = request('client_id');
        $selectedTeamId = request('team_id');
        $selectedManagerId = request('manager_member_id');

        return view('admin.projects.create', compact(
            'clients',
            'services',
            'teams',
            'members',
            'selectedClientId',
            'selectedTeamId',
            'selectedManagerId'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateProject($request);

        $data['created_by'] = auth()->id();

        if (empty($data['code'])) {
            $data['code'] = $this->generateProjectCode($data['name']);
        }

        if (($data['status'] ?? null) === 'completed') {
            $data['completed_date'] = now()->toDateString();
        }

        $project = Project::query()->create($data);

        $project->logActivity(
            event: 'created',
            title: 'تم إنشاء المشروع',
            description: 'تم إنشاء مشروع جديد: '.$project->name,
            newValues: $project->only([
                'client_id',
                'service_id',
                'team_id',
                'manager_member_id',
                'name',
                'code',
                'priority',
                'status',
                'start_date',
                'due_date',
                'budget',
            ])
        );

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'تم إضافة المشروع بنجاح');
    }

    public function show(Project $project)
    {
        $this->authorizeProjectAccess($project);

        $project->load([
            'client',
            'service',
            'team',
            'manager.user',
            'creator',
        ])->loadCount([
            'tasks',
            'openTasks',
            'milestones',
            'completedMilestones',
        ]);

        return view('admin.projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $this->authorizeProjectAccess($project);

        $clientQuery = Client::query();
        $this->applyOwnedRecordScopeIncluding($clientQuery, 'clients.view_all', 'assigned_to', $project->client_id);
        $clients = $clientQuery
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->orderBy('name')
            ->get();

        $teams = Team::query()
            ->where(function ($query) use ($project) {
                $query->where('status', true);

                if ($project->team_id) {
                    $query->orWhere('id', $project->team_id);
                }
            })
            ->orderBy('name')
            ->get();

        $members = Member::query()
            ->with(['team', 'user'])
            ->where(function ($query) use ($project) {
                $query->where('status', 'active');

                if ($project->manager_member_id) {
                    $query->orWhere('id', $project->manager_member_id);
                }
            })
            ->orderBy('name')
            ->get();

        return view('admin.projects.edit', compact(
            'project',
            'clients',
            'services',
            'teams',
            'members'
        ));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorizeProjectAccess($project);

        $oldValues = $project->only([
            'client_id',
            'service_id',
            'team_id',
            'manager_member_id',
            'name',
            'code',
            'description',
            'priority',
            'status',
            'start_date',
            'due_date',
            'completed_date',
            'budget',
            'notes',
        ]);

        $data = $this->validateProject($request, $project);

        if (empty($data['code'])) {
            $data['code'] = $this->generateProjectCode($data['name'], $project->id);
        }

        if (($data['status'] ?? null) === 'completed' && ! $project->completed_date) {
            $data['completed_date'] = now()->toDateString();
        }

        if (($data['status'] ?? null) !== 'completed') {
            $data['completed_date'] = null;
        }

        $project->update($data);

        $project->logActivity(
            event: 'updated',
            title: 'تم تحديث المشروع',
            description: 'تم تحديث بيانات المشروع: '.$project->name,
            oldValues: $oldValues,
            newValues: $project->only([
                'client_id',
                'service_id',
                'team_id',
                'manager_member_id',
                'name',
                'code',
                'description',
                'priority',
                'status',
                'start_date',
                'due_date',
                'completed_date',
                'budget',
                'notes',
            ])
        );

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'تم تحديث المشروع بنجاح');
    }

    public function destroy(Project $project)
    {
        $this->authorizeProjectAccess($project);

        if ($project->tasks()->exists()) {
            return back()->with('error', 'لا يمكن حذف المشروع لأنه مرتبط بمهام');
        }

        $project->logActivity(
            event: 'deleted',
            title: 'تم حذف المشروع',
            description: 'تم حذف المشروع: '.$project->name,
            oldValues: $project->toArray()
        );

        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'تم حذف المشروع بنجاح');
    }

    public function changeStatus(Request $request, Project $project)
    {
        abort_unless(auth()->user()->can('projects.change_status'), 403);

        $this->authorizeProjectAccess($project);

        $data = $request->validate([
            'status' => ['required', 'in:new,planning,in_progress,on_hold,completed,cancelled'],
        ]);

        $oldStatus = $project->status;

        $project->update([
            'status' => $data['status'],
            'completed_date' => $data['status'] === 'completed' ? now()->toDateString() : null,
        ]);

        $project->logActivity(
            event: 'status_changed',
            title: 'تم تغيير حالة المشروع',
            description: 'تم تغيير حالة المشروع من '.
                $this->statusLabel($oldStatus).
                ' إلى '.
                $this->statusLabel($project->status),
            oldValues: [
                'status' => $oldStatus,
            ],
            newValues: [
                'status' => $project->status,
            ]
        );

        return back()->with('success', 'تم تحديث حالة المشروع بنجاح');
    }

    private function validateProject(Request $request, ?Project $project = null): array
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'manager_member_id' => ['nullable', 'exists:members,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100', 'unique:projects,code,'.($project?->id ?? 'NULL')],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'status' => ['required', 'in:new,planning,in_progress,on_hold,completed,cancelled'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ], [
            'client_id.required' => 'العميل مطلوب',
            'name.required' => 'اسم المشروع مطلوب',
            'priority.required' => 'الأولوية مطلوبة',
            'status.required' => 'حالة المشروع مطلوبة',
            'due_date.after_or_equal' => 'تاريخ التسليم يجب أن يكون بعد أو يساوي تاريخ البداية',
            'code.unique' => 'كود المشروع مستخدم من قبل',
        ]);

        if (! empty($data['manager_member_id'])) {
            $manager = Member::query()->find($data['manager_member_id']);

            if (empty($data['team_id']) || (int) $manager?->team_id !== (int) $data['team_id']) {
                throw ValidationException::withMessages([
                    'manager_member_id' => 'مدير المشروع يجب أن يكون عضوًا في الفريق المختار.',
                ]);
            }
        }

        if (! $project || (int) $data['client_id'] !== (int) $project->client_id) {
            $client = Client::query()->findOrFail($data['client_id']);
            $this->authorizeOwnedRecordAccess('clients.view_all', $client->assigned_to);
        }

        return $data;
    }

    private function generateProjectCode(string $name, ?int $ignoreId = null): string
    {
        $base = Str::upper(Str::slug($name));

        if (! $base) {
            $base = 'PROJECT';
        }

        $base = Str::limit($base, 30, '');

        $code = $base;
        $counter = 1;

        while (
            Project::query()
                ->where('code', $code)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $code = $base.'-'.$counter;
            $counter++;
        }

        return $code;
    }

    public function downloadAttachment(Project $project, ProjectAttachment $attachment)
    {
        $this->authorizeProjectAccess($project);

        abort_unless((int) $attachment->project_id === (int) $project->id, 404);

        abort_if($attachment->is_link, 404);

        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        return Storage::disk('local')->download($attachment->file_path, $attachment->file_name);
    }

    private function authorizeProjectAccess(Project $project): void
    {
        $user = auth()->user();

        if ($user->can('projects.view_all')) {
            return;
        }

        $member = $user->member;

        abort_unless($member, 403);

        if ($project->manager_member_id && (int) $project->manager_member_id === (int) $member->id) {
            return;
        }

        if ($member->is_manager && $member->team_id && (int) $project->team_id === (int) $member->team_id) {
            return;
        }

        $hasTaskInsideProject = $project->tasks()
            ->whereHas('assignedMembers', function ($query) use ($member) {
                $query->where('members.id', $member->id);
            })
            ->exists();

        abort_unless($hasTaskInsideProject, 403);
    }

    private function statusLabel(?string $status): string
    {
        return match ($status) {
            'new' => 'جديد',
            'planning' => 'مرحلة التخطيط',
            'in_progress' => 'قيد التنفيذ',
            'on_hold' => 'متوقف مؤقتًا',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            default => 'غير معروف',
        };
    }
}
