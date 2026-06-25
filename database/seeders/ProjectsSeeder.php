<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Member;
use App\Models\Project;
use App\Models\Service;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProjectsSeeder extends Seeder
{
    public function run(): void
    {
        $clients = Client::query()->get();
        $services = Service::query()->get();
        $teams = Team::query()->get();
        $members = Member::query()->where('status', 'active')->get();
        $users = User::query()->get();

        if ($clients->isEmpty()) {
            $this->command->warn('No clients found. Please run ClientsSeeder first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->warn('No users found.');
            return;
        }

        $projects = [
            [
                'name' => 'SEO Monthly Retainer',
                'description' => 'إدارة وتحسين ظهور العميل في محركات البحث بشكل شهري.',
                'priority' => 'high',
                'status' => 'in_progress',
                'start_date' => now()->subDays(10)->toDateString(),
                'due_date' => now()->addDays(20)->toDateString(),
                'budget' => 15000,
                'notes' => 'متابعة أسبوعية مع العميل.',
            ],
            [
                'name' => 'Website Development',
                'description' => 'تصميم وبرمجة موقع تعريفي احترافي للعميل.',
                'priority' => 'medium',
                'status' => 'planning',
                'start_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'budget' => 25000,
                'notes' => null,
            ],
            [
                'name' => 'Social Media Management',
                'description' => 'إدارة محتوى السوشيال ميديا وجدولة النشر.',
                'priority' => 'medium',
                'status' => 'new',
                'start_date' => now()->addDays(2)->toDateString(),
                'due_date' => now()->addDays(32)->toDateString(),
                'budget' => 12000,
                'notes' => 'تجهيز Content Calendar أول أسبوع.',
            ],
            [
                'name' => 'Branding Package',
                'description' => 'تنفيذ باكدج هوية بصرية للعميل.',
                'priority' => 'urgent',
                'status' => 'in_progress',
                'start_date' => now()->subDays(20)->toDateString(),
                'due_date' => now()->subDay()->toDateString(),
                'budget' => 18000,
                'notes' => 'مشروع متأخر ويحتاج متابعة.',
            ],
        ];

        foreach ($projects as $index => $projectData) {
            $client = $clients[$index % $clients->count()];
            $service = $services->isNotEmpty() ? $services[$index % $services->count()] : null;
            $team = $teams->isNotEmpty() ? $teams[$index % $teams->count()] : null;

            $manager = $members->isNotEmpty()
                ? $members->where('team_id', $team?->id)->first() ?? $members[$index % $members->count()]
                : null;

            $user = $users[$index % $users->count()];

            $code = Str::upper(Str::slug($projectData['name']));

            Project::query()->updateOrCreate(
                [
                    'name' => $projectData['name'],
                    'client_id' => $client->id,
                ],
                [
                    ...$projectData,
                    'code' => $code ?: 'PROJECT-' . ($index + 1),
                    'service_id' => $service?->id,
                    'team_id' => $team?->id,
                    'manager_member_id' => $manager?->id,
                    'created_by' => $user->id,
                    'completed_date' => $projectData['status'] === 'completed'
                        ? now()->toDateString()
                        : null,
                ]
            );
        }
    }
}