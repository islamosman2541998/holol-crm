<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TeamsSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            [
                'name' => 'فريق البرمجة',
                'code' => 'programming',
                'description' => 'فريق مسؤول عن تطوير المواقع والأنظمة.',
            ],
            [
                'name' => 'فريق السيلز',
                'code' => 'sales',
                'description' => 'فريق مسؤول عن العملاء المحتملين والمبيعات.',
            ],
            [
                'name' => 'فريق SEO',
                'code' => 'seo',
                'description' => 'فريق مسؤول عن تحسين محركات البحث.',
            ],
            [
                'name' => 'فريق التصميم',
                'code' => 'design',
                'description' => 'فريق مسؤول عن التصميمات والجرافيك.',
            ],
            [
                'name' => 'فريق السوشيال ميديا',
                'code' => 'social_media',
                'description' => 'فريق مسؤول عن المحتوى وإدارة السوشيال.',
            ],
        ];

        foreach ($teams as $teamData) {
            $code = Str::slug($teamData['code'], '_');

            $team = Team::query()->updateOrCreate(
                [
                    'code' => $code,
                ],
                [
                    'name' => $teamData['name'],
                    'role_name' => 'team_' . $code,
                    'description' => $teamData['description'],
                    'status' => true,
                ]
            );

            $team->ensureRole();
        }
    }
}