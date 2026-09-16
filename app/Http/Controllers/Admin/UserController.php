<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\RestrictsPermissionGrants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use RestrictsPermissionGrants;

    public function index()
    {
        $users = User::query()
            ->with('roles')
            ->latest()
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = $this->grantableRoles();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'exists:roles,name'],
            'status' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'اسم المستخدم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique' => 'هذا البريد مستخدم بالفعل',
            'password.required' => 'كلمة المرور مطلوبة',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق',
            'role.required' => 'يجب اختيار دور للمستخدم',
        ]);

        $this->guardRoleAssignment($data['role']);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => $request->boolean('status'),
        ]);

        $user->assignRole($data['role']);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    public function edit(User $user)
    {
        $currentRole = $this->baseRoleName($user);
        $roles = $this->grantableRoles($currentRole);

        return view('admin.users.edit', compact('user', 'roles', 'currentRole'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->hasRole('SEO Manager') && ! auth()->user()->hasRole('SEO Manager')) {
            return back()->with('error', 'لا يمكنك تعديل حساب المدير SEO');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'exists:roles,name'],
            'status' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'اسم المستخدم مطلوب',
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'email.unique' => 'هذا البريد مستخدم بالفعل',
            'password.confirmed' => 'تأكيد كلمة المرور غير مطابق',
            'role.required' => 'يجب اختيار دور للمستخدم',
        ]);

        $currentRole = $this->baseRoleName($user);

        if (auth()->id() === $user->id && $data['role'] !== $currentRole) {
            return back()
                ->withErrors(['role' => 'لا يمكنك تغيير دور حسابك الحالي'])
                ->withInput();
        }

        $this->guardRoleAssignment($data['role'], $currentRole);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => $request->boolean('status'),
        ]);

        if (! empty($data['password'])) {
            $user->update([
                'password' => Hash::make($data['password']),
            ]);
        }

        $teamRoles = $user->getRoleNames()
            ->filter(fn (string $roleName) => str_starts_with($roleName, 'team_'))
            ->all();

        $user->syncRoles([...$teamRoles, $data['role']]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'تم تحديث المستخدم بنجاح');
    }

    public function destroy(User $user)
    {
        if ($user->hasRole('SEO Manager')) {
            return back()->with('error', 'لا يمكن حذف المدير SEO');
        }

        if (auth()->id() === $user->id) {
            return back()->with('error', 'لا يمكنك حذف حسابك الحالي');
        }

        $user->update(['status' => false]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'تم تعطيل المستخدم مع الاحتفاظ بسجل أعماله بنجاح');
    }

    private function baseRoleName(User $user): ?string
    {
        return $user->getRoleNames()
            ->first(fn (string $roleName) => ! str_starts_with($roleName, 'team_'));
    }
}
