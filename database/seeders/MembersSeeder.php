<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class MembersSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->get();
        $teams = Team::query()->get();

        if ($teams->isEmpty()) {
            $this->command->warn('No teams found. Please run TeamsSeeder first.');
            return;
        }

        $programmingTeam = Team::query()->where('code', 'programming')->first();
        $salesTeam = Team::query()->where('code', 'sales')->first();
        $seoTeam = Team::query()->where('code', 'seo')->first();

        $members = [
            [
                'name' => 'إسلام',
                'job_title' => 'Programming Manager',
                'department' => 'Programming',
                'team_id' => $programmingTeam?->id,
                'is_manager' => true,
                'status' => 'active',
            ],
            [
                'name' => 'مايا',
                'job_title' => 'SEO Specialist',
                'department' => 'SEO',
                'team_id' => $seoTeam?->id,
                'is_manager' => false,
                'status' => 'active',
            ],
            [
                'name' => 'أحمد',
                'job_title' => 'Sales Executive',
                'department' => 'Sales',
                'team_id' => $salesTeam?->id,
                'is_manager' => false,
                'status' => 'active',
            ],
        ];

        foreach ($members as $index => $memberData) {
            $user = $users[$index] ?? null;

            $member = Member::query()->updateOrCreate(
                [
                    'name' => $memberData['name'],
                ],
                [
                    ...$memberData,
                    'user_id' => $user?->id,
                    'email' => $user?->email,
                    'mobile' => $user?->phone,
                    'hire_date' => now()->subMonths(3)->toDateString(),
                ]
            );

            $member->load(['user', 'team']);
            $member->syncUserTeamRole();
        }

        $programmingManager = Member::query()
            ->where('name', 'إسلام')
            ->where('is_manager', true)
            ->first();

        if ($programmingManager && $programmingTeam) {
            $programmingTeam->update([
                'manager_member_id' => $programmingManager->id,
            ]);
        }
    }
}