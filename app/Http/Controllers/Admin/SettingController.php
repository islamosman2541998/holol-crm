<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit');
    }

    public function update(Request $request)
    {
        $request->validate([
            'system_name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],

            'login_title' => ['nullable', 'string', 'max:255'],
            'login_subtitle' => ['nullable', 'string', 'max:255'],
            'overlay_color' => ['nullable', 'string', 'max:20'],
            'overlay_opacity' => ['nullable', 'numeric', 'min:0', 'max:1'],

            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'sidebar_bg' => ['nullable', 'string', 'max:20'],
            'sidebar_text' => ['nullable', 'string', 'max:20'],
            'sidebar_active_bg' => ['nullable', 'string', 'max:20'],
            'sidebar_active_text' => ['nullable', 'string', 'max:20'],
            'topbar_bg' => ['nullable', 'string', 'max:20'],
            'button_radius' => ['nullable', 'string', 'max:20'],
            'card_radius' => ['nullable', 'string', 'max:20'],

            'system_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:1024'],
            'login_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'login_background' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'card_bg' => ['nullable', 'string', 'max:20'],
            'card_opacity' => ['nullable', 'numeric', 'min:0', 'max:1'],
        ]);

        $this->saveTextSettings($request);
        $this->saveImageSettings($request);

        Setting::clearCache();

        return back()->with('success', 'تم حفظ الإعدادات بنجاح');
    }

    private function saveTextSettings(Request $request): void
    {
        $textSettings = [
            'general' => [
                'system_name',
                'company_name',
                'email',
                'phone',
            ],

            'login' => [
                'login_title',
                'login_subtitle',
                'overlay_color',
                'overlay_opacity',
                'card_bg',
                'card_opacity',
            ],

            'appearance' => [
                'primary_color',
                'secondary_color',
                'sidebar_bg',
                'sidebar_text',
                'sidebar_active_bg',
                'sidebar_active_text',
                'topbar_bg',
                'button_radius',
                'card_radius',
            ],
        ];

        foreach ($textSettings as $group => $keys) {
            foreach ($keys as $key) {
                if ($request->has($key)) {
                    Setting::set($group, $key, $request->input($key));
                }
            }
        }
    }

    private function saveImageSettings(Request $request): void
    {
        $imageSettings = [
            'branding' => [
                'system_logo',
                'favicon',
            ],

            'login' => [
                'login_logo',
                'login_background',
            ],
        ];

        foreach ($imageSettings as $group => $keys) {
            foreach ($keys as $key) {
                if (! $request->hasFile($key)) {
                    continue;
                }

                $oldPath = setting($group . '.' . $key);

                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }

                $path = $request->file($key)->store('settings', 'public');

                Setting::set($group, $key, $path, 'image');
            }
        }
    }
}
