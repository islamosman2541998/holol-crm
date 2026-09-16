<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\TaskReminders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'password.required' => 'كلمة المرور مطلوبة',
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors([
                    'email' => 'بيانات الدخول غير صحيحة',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user->status) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors([
                    'email' => 'هذا الحساب غير نشط، برجاء التواصل مع المدير',
                ])
                ->onlyInput('email');
        }

        $user->update([
            'last_login_at' => now(),
        ]);

        if (TaskReminders::notifyIfDue($user)) {
            $request->session()->flash('show_tasks_popup', true);
        }

        $request->session()->forget('url.intended');

        return redirect()->route($this->landingRouteFor($user));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function landingRouteFor(User $user): string
    {
        $destinations = [
            'dashboard.view' => 'admin.dashboard',
            'tasks.view' => 'admin.tasks.index',
            'projects.view' => 'admin.projects.index',
            'clients.view' => 'admin.clients.index',
            'leads.view' => 'admin.leads.index',
            'quotations.view' => 'admin.quotations.index',
            'sales.view' => 'admin.sales.index',
            'payments.view' => 'admin.payments.index',
            'followups.view' => 'admin.followups.index',
            'reports.view' => 'admin.reports.clients',
        ];

        foreach ($destinations as $permission => $route) {
            if ($user->can($permission)) {
                return $route;
            }
        }

        return 'admin.profile.edit';
    }
}
