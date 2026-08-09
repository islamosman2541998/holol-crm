<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Team;
use App\Models\User;
use App\Traits\RestrictsPermissionGrants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    use RestrictsPermissionGrants;

    public function index()
    {
        return view('admin.members.index');
    }

    public function create()
    {
        $users = User::query()
            ->where('status', true)
            ->whereDoesntHave('member')
            ->orderBy('name')
            ->get();

        $teams = Team::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $managers = Member::query()
            ->where('is_manager', true)
            ->where('status', 'active')
            ->with('team')
            ->orderBy('name')
            ->get();

        return view('admin.members.create', compact(
            'users',
            'teams',
            'managers'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateMember($request);

        $this->guardTeamAssignment($data['team_id'] ?? null);

        if (! empty($data['user_id'])) {
            $this->fillMemberDataFromLinkedUser($data);
        }
        DB::transaction(function () use ($request, &$data) {
            if (
                empty($data['user_id']) &&
                $request->boolean('create_login_account')
            ) {
                $user = User::query()->create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['mobile'] ?? $data['phone'] ?? null,
                    'password' => Hash::make($data['login_password']),
                    'status' => true,
                ]);

                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();

                $data['user_id'] = $user->id;
            }

            unset(
                $data['create_login_account'],
                $data['login_password'],
                $data['login_password_confirmation']
            );

            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('members', 'public');
            }

            $member = Member::query()->create($data);

            $member->load(['user', 'team']);
            $member->syncUserTeamRole();

            $member->logActivity(
                event: 'created',
                title: 'تم إنشاء العضو',
                description: 'تم إضافة عضو جديد باسم: ' . $member->name,
                newValues: $member->only([
                    'user_id',
                    'team_id',
                    'manager_id',
                    'name',
                    'job_title',
                    'department',
                    'email',
                    'mobile',
                    'is_manager',
                    'status',
                ])
            );
        });

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'تم إضافة العضو بنجاح');
    }

    public function show(Member $member)
    {
        $member->load([
            'user.roles',
            'team.manager',
            'directManager',
            'managedMembers.user',
            'managedMembers.team',
            'managedTeams',
        ]);

        return view('admin.members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        $users = User::query()
            ->where(function ($query) use ($member) {
                $query->where(function ($query) {
                    $query->where('status', true)
                        ->whereDoesntHave('member');
                });

                if ($member->user_id) {
                    $query->orWhere('id', $member->user_id);
                }
            })
            ->orderBy('name')
            ->get();

        $teams = Team::query()
            ->where(function ($query) use ($member) {
                $query->where('status', true);

                if ($member->team_id) {
                    $query->orWhere('id', $member->team_id);
                }
            })
            ->orderBy('name')
            ->get();

        $managers = Member::query()
            ->where('is_manager', true)
            ->where('id', '!=', $member->id)
            ->where(function ($query) use ($member) {
                $query->where('status', 'active');

                if ($member->manager_id) {
                    $query->orWhere('id', $member->manager_id);
                }
            })
            ->with('team')
            ->orderBy('name')
            ->get();

        return view('admin.members.edit', compact(
            'member',
            'users',
            'teams',
            'managers'
        ));
    }

    public function update(Request $request, Member $member)
    {
        $data = $this->validateMember($request, $member);

        $this->guardTeamAssignment($data['team_id'] ?? null);

        if (! empty($data['user_id'])) {
            $this->fillMemberDataFromLinkedUser($data);
        }
        DB::transaction(function () use ($request, $member, &$data) {
            $oldTeamId = $member->team_id;
            $oldUser = $member->user;
            $oldTeam = $member->team;

            $oldValues = $member->only([
                'user_id',
                'team_id',
                'manager_id',
                'name',
                'job_title',
                'department',
                'email',
                'phone',
                'mobile',
                'hire_date',
                'is_manager',
                'status',
                'notes',
            ]);

            if (
                $member->managedTeams()->exists() &&
                ! (bool) ($data['is_manager'] ?? false)
            ) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'is_manager' => 'لا يمكن إلغاء صفة المدير لأن هذا العضو مدير لفريق',
                ]);
            }

            if (
                empty($data['user_id']) &&
                $request->boolean('create_login_account')
            ) {
                $user = User::query()->create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['mobile'] ?? $data['phone'] ?? null,
                    'password' => Hash::make($data['login_password']),
                    'status' => true,
                ]);

                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();

                $data['user_id'] = $user->id;
            }

            unset(
                $data['create_login_account'],
                $data['login_password'],
                $data['login_password_confirmation']
            );

            if ($request->hasFile('image')) {
                if ($member->image) {
                    Storage::disk('public')->delete($member->image);
                }

                $data['image'] = $request->file('image')->store('members', 'public');
            }

            $member->update($data);

            if (
                $oldUser &&
                $oldTeam &&
                $oldUser->id !== (int) ($data['user_id'] ?? 0) &&
                $oldUser->hasRole($oldTeam->role_name)
            ) {
                $oldUser->removeRole($oldTeam->role_name);
            }

            $member->load(['user', 'team']);
            $member->syncUserTeamRole($oldTeamId);

            $member->logActivity(
                event: 'updated',
                title: 'تم تحديث العضو',
                description: 'تم تحديث بيانات العضو: ' . $member->name,
                oldValues: $oldValues,
                newValues: $member->only([
                    'user_id',
                    'team_id',
                    'manager_id',
                    'name',
                    'job_title',
                    'department',
                    'email',
                    'phone',
                    'mobile',
                    'hire_date',
                    'is_manager',
                    'status',
                    'notes',
                ])
            );
        });

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'تم تحديث العضو بنجاح');
    }

    public function destroy(Member $member)
    {
        if ($member->managedMembers()->exists()) {
            return redirect()
                ->route('admin.members.index')
                ->with('error', 'لا يمكن حذف العضو لأنه مدير مباشر لأعضاء آخرين');
        }

        if ($member->managedTeams()->exists()) {
            return redirect()
                ->route('admin.members.index')
                ->with('error', 'لا يمكن حذف العضو لأنه مدير لفريق');
        }

        $member->removeTeamRole();

        if ($member->image) {
            Storage::disk('public')->delete($member->image);
        }

        $member->logActivity(
            event: 'deleted',
            title: 'تم حذف العضو',
            description: 'تم حذف العضو: ' . $member->name,
            oldValues: $member->toArray()
        );

        $member->delete();

        return redirect()
            ->route('admin.members.index')
            ->with('success', 'تم حذف العضو بنجاح');
    }
    private function fillMemberDataFromLinkedUser(array &$data): void
    {
        if (empty($data['user_id'])) {
            return;
        }

        $user = User::query()->find($data['user_id']);

        if (! $user) {
            return;
        }

        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $data['mobile'] = $user->phone ?: ($data['mobile'] ?? null);
    }
    private function validateMember(Request $request, ?Member $member = null): array
    {
        $creatingLoginAccount = $request->boolean('create_login_account')
            && ! $request->filled('user_id');

        return $request->validate([
            'user_id' => [
                'nullable',
                'exists:users,id',
                Rule::unique('members', 'user_id')->ignore($member?->id),
            ],

            'team_id' => [
                'nullable',
                'exists:teams,id',
            ],

            'manager_id' => [
                'nullable',
                'exists:members,id',
                function ($attribute, $value, $fail) use ($member) {
                    if ($member && (int) $value === (int) $member->id) {
                        $fail('لا يمكن أن يكون العضو مديرًا لنفسه');
                    }
                },
            ],

            'name' => [
                $request->filled('user_id') ? 'nullable' : 'required',
                'string',
                'max:255',
            ],

            'job_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'department' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                $creatingLoginAccount ? 'required' : 'nullable',
                'email',
                'max:255',
                $creatingLoginAccount
                    ? Rule::unique('users', 'email')->ignore($member?->user_id)
                    : null,
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:50',
            ],

            'image' => [
                'nullable',
                'image',
                'max:2048',
            ],

            'hire_date' => [
                'nullable',
                'date',
            ],

            'is_manager' => [
                'nullable',
                'boolean',
            ],

            'status' => [
                'required',
                'in:active,inactive,on_leave,left',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            'create_login_account' => [
                'nullable',
                'boolean',
            ],

            'login_password' => [
                $creatingLoginAccount ? 'required' : 'nullable',
                'confirmed',
                'min:8',
            ],
        ], [
            'user_id.unique' => 'حساب الدخول مرتبط بعضو آخر بالفعل',
            'name.required' => 'اسم العضو مطلوب',
            'email.required' => 'الإيميل مطلوب عند إنشاء حساب دخول',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique' => 'هذا الإيميل مستخدم بالفعل في حساب دخول آخر',
            'login_password.required' => 'الباسورد مطلوب عند إنشاء حساب دخول',
            'login_password.confirmed' => 'تأكيد الباسورد غير مطابق',
            'login_password.min' => 'الباسورد يجب ألا يقل عن 8 حروف',
            'image.image' => 'الملف يجب أن يكون صورة',
            'image.max' => 'حجم الصورة لا يزيد عن 2MB',
            'status.required' => 'حالة العضو مطلوبة',
        ]);
    }
}
