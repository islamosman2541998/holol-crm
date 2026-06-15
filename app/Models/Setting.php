<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::rememberForever('system_settings', function () {
            return self::query()
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->group . '.' . $setting->key => $setting->value];
                })
                ->toArray();
        });

        return $settings[$key] ?? $default;
    }

    public static function set(string $group, string $key, mixed $value, string $type = 'text'): void
    {
        self::query()->updateOrCreate(
            [
                'group' => $group,
                'key' => $key,
            ],
            [
                'value' => $value,
                'type' => $type,
            ]
        );

        self::clearCache();
    }

    public static function clearCache(): void
    {
        Cache::forget('system_settings');
    }
}