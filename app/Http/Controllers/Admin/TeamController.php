<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Team;
use App\Traits\RestrictsPermissionGrants;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class TeamController extends Controller
{
    use RestrictsPermissionGrants;

    public function index()
    {
        return view('admin.teams.index');
    }

    public function create()
    {
        $managers = Member::query()
            ->where('is_manager', true)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                return str($permission->name)->before('.')->toString();
            });

        return view('admin.teams.create', compact('managers', 'permissions'));
    }

    public function store(Request $request)
    {
        $data = $this->validateTeam($request);

        $code = Str::slug($data['code'], '_');

        $team = Team::query()->create([
            'manager_member_id' => $data['manager_member_id'] ?? null,
            'name' => $data['name'],
            'code' => $code,
            'role_name' => 'team_' . $code,
            'description' => $data['description'] ?? null,
            'status' => $request->boolean('status'),
        ]);

        $team->ensureRole();

        if (auth()->user()->can('teams.permissions')) {
            $team->syncRolePermissions($this->filterGrantablePermissions($data['permissions'] ?? []));
        }

        $team->syncMembersTeamRole();

        $team->logActivity(
            event: 'created',
            title: 'تم إنشاء الفريق',
            description: 'تم إنشاء فريق جديد باسم: ' . $team->name,
            newValues: $team->only([
                'name',
                'code',
                'role_name',
                'manager_member_id',
                'status',
            ])
        );

        return redirect()
            ->route('admin.teams.index')
            ->with('success', 'تم إضافة الفريق بنجاح');
    }

    public function show(Team $team)
    {
        $team->load([
            'manager',
            'members.user',
            'members.directManager',
        ]);

        return view('admin.teams.show', compact('team'));
    }

    public function edit(Team $team)
    {
        $managers = Member::query()
            ->where('is_manager', true)
            ->where(function ($query) use ($team) {
                $query->where('status', 'active')
                    ->orWhere('id', $team->manager_member_id);
            })
            ->orderBy('name')
            ->get();

        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                return str($permission->name)->before('.')->toString();
            });

        $teamPermissions = $team->role()
            ? $team->role()->permissions()->pluck('name')->toArray()
            : [];

        return view('admin.teams.edit', compact(
            'team',
            'managers',
            'permissions',
            'teamPermissions'
        ));
    }

    public function update(Request $request, Team $team)
    {
        $data = $this->validateTeam($request, $team);

        $oldValues = $team->only([
            'name',
            'code',
            'role_name',
            'manager_member_id',
            'description',
            'status',
        ]);

        $code = Str::slug($data['code'], '_');
        $oldRoleName = $team->role_name;
        $newRoleName = 'team_' . $code;

        $team->update([
            'manager_member_id' => $data['manager_member_id'] ?? null,
            'name' => $data['name'],
            'code' => $code,
            'role_name' => $newRoleName,
            'description' => $data['description'] ?? null,
            'status' => $request->boolean('status'),
        ]);

        if ($oldRoleName !== $newRoleName) {
            $oldRole = \Spatie\Permission\Models\Role::query()
                ->where('name', $oldRoleName)
                ->where('guard_name', 'web')
                ->first();

            $newRole = $team->ensureRole();

            if ($oldRole) {
                $newRole->syncPermissions($oldRole->permissions);
                $oldRole->delete();
            }
        } else {
            $team->ensureRole();
        }

        if (auth()->user()->can('teams.permissions')) {
            $team->syncRolePermissions($this->filterGrantablePermissions($data['permissions'] ?? []));
        }

        $team->syncMembersTeamRole();

        $team->logActivity(
            event: 'updated',
            title: 'تم تحديث الفريق',
            description: 'تم تحديث بيانات فريق: ' . $team->name,
            oldValues: $oldValues,
            newValues: $team->only([
                'name',
                'code',
                'role_name',
                'manager_member_id',
                'description',
                'status',
            ])
        );

        return redirect()
            ->route('admin.teams.index')
            ->with('success', 'تم تحديث الفريق بنجاح');
    }

    public function destroy(Team $team)
    {
        if ($team->members()->exists()) {
            return redirect()
                ->route('admin.teams.index')
                ->with('error', 'لا يمكن حذف الفريق لأنه يحتوي على أعضاء');
        }

        $role = $team->role();

        $team->logActivity(
            event: 'deleted',
            title: 'تم حذف الفريق',
            description: 'تم حذف فريق: ' . $team->name,
            oldValues: $team->toArray()
        );

        $team->delete();

        if ($role) {
            $role->delete();
        }

        return redirect()
            ->route('admin.teams.index')
            ->with('success', 'تم حذف الفريق بنجاح');
    }
    public function syncMembers(Team $team)
    {
        abort_unless(auth()->user()->can('teams.permissions'), 403);

        $team->syncMembersTeamRole();

        $team->logActivity(
            event: 'permissions_synced',
            title: 'تمت مزامنة صلاحيات الفريق',
            description: 'تم تطبيق Role الفريق على كل الأعضاء المرتبطين بحساب دخول.',
            newValues: [
                'team_id' => $team->id,
                'role_name' => $team->role_name,
            ]
        );

        return redirect()
            ->route('admin.teams.show', $team)
            ->with('success', 'تمت مزامنة صلاحيات الفريق على الأعضاء بنجاح');
    }
    private function validateTeam(Request $request, ?Team $team = null): array
    {
        $teamId = $team?->id ?? 'NULL';

        return $request->validate([
            'manager_member_id' => ['nullable', 'exists:members,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9_-]+$/',
                'unique:teams,code,' . $teamId,
            ],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ], [
            'name.required' => 'اسم الفريق مطلوب',
            'code.required' => 'كود الفريق مطلوب',
            'code.regex' => 'كود الفريق يجب أن يكون حروف إنجليزية أو أرقام فقط بدون مسافات',
            'code.unique' => 'كود الفريق مستخدم من قبل',
        ]);
    }
}
