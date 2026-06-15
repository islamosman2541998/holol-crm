<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['general', 'system_name', 'Holol CRM', 'text'],
            ['general', 'company_name', 'Holol', 'text'],
            ['general', 'email', 'info@hololcrm.test', 'text'],
            ['general', 'phone', '01000000000', 'text'],

            ['branding', 'system_logo', null, 'image'],
            ['branding', 'favicon', null, 'image'],

            ['login', 'login_title', 'Holol CRM', 'text'],
            ['login', 'login_subtitle', 'تسجيل الدخول إلى لوحة التحكم', 'text'],
            ['login', 'login_logo', null, 'image'],
            ['login', 'login_background', null, 'image'],
            ['login', 'overlay_color', '#111827', 'color'],
            ['login', 'overlay_opacity', '0.75', 'text'],

            ['appearance', 'primary_color', '#0d6efd', 'color'],
            ['appearance', 'secondary_color', '#6c757d', 'color'],
            ['appearance', 'sidebar_bg', '#111827', 'color'],
            ['appearance', 'sidebar_text', '#ffffff', 'color'],
            ['appearance', 'sidebar_active_bg', '#0d6efd', 'color'],
            ['appearance', 'sidebar_active_text', '#ffffff', 'color'],
            ['appearance', 'topbar_bg', '#ffffff', 'color'],
            ['appearance', 'button_radius', '8px', 'text'],
            ['appearance', 'card_radius', '14px', 'text'],
            ['login', 'card_bg', '#ffffff', 'color'],
            ['login', 'card_opacity', '1', 'text'],
        ];

        foreach ($settings as [$group, $key, $value, $type]) {
            Setting::query()->updateOrCreate(
                [
                    'group' => $group,
                    'key' => $key,
                ],
                [
                    'value' => $value,
                    'type' => $type,
                ]
            );
        }

        Setting::clearCache();
    }
}
