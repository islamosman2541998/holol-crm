<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CompanyUsersSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->ensurePermissions();
        $this->createRoles();
        $this->createTeamsAndUsers();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function ensurePermissions(): void
    {
        $permissions = [
            'dashboard.view',

            'clients.view',
            'clients.create',
            'clients.edit',

            'leads.view',
            'leads.create',
            'leads.edit',

            'followups.view',
            'followups.create',
            'followups.edit',

            'services.view',

            'projects.view',
            'projects.create',
            'projects.edit',
            'projects.change_status',

            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.change_status',

            'campaigns.view',
            'campaigns.create',
            'campaigns.edit',

            'teams.view',
            'members.view',

            'reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }

    private function createRoles(): void
    {
        $teamLeaderPermissions = [
            'dashboard.view',

            'clients.view',
            'leads.view',

            'followups.view',
            'followups.create',
            'followups.edit',

            'services.view',

            'projects.view',
            'projects.create',
            'projects.edit',
            'projects.change_status',

            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.change_status',

            'teams.view',
            'members.view',

            'reports.view',
        ];

        $teamMemberPermissions = [
            'dashboard.view',

            'clients.view',
            'leads.view',

            'services.view',

            'projects.view',

            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.change_status',
        ];

        $socialMediaPermissions = [
            'dashboard.view',

            'clients.view',
            'leads.view',

            'services.view',

            'projects.view',

            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.change_status',

            'campaigns.view',
            'campaigns.create',
            'campaigns.edit',
        ];

        Role::query()
            ->firstOrCreate(['name' => 'Team Leader', 'guard_name' => 'web'])
            ->syncPermissions($teamLeaderPermissions);

        Role::query()
            ->firstOrCreate(['name' => 'Team Member', 'guard_name' => 'web'])
            ->syncPermissions($teamMemberPermissions);

        Role::query()
            ->firstOrCreate(['name' => 'Social Media', 'guard_name' => 'web'])
            ->syncPermissions($socialMediaPermissions);
    }

    private function createTeamsAndUsers(): void
    {
        $programmingTeam = $this->createTeam(
            name: 'فريق البرمجة',
            code: 'programming',
            permissions: [
                'dashboard.view',
                'clients.view',
                'leads.view',
                'services.view',
                'projects.view',
                'projects.create',
                'projects.edit',
                'projects.change_status',
                'tasks.view',
                'tasks.create',
                'tasks.edit',
                'tasks.change_status',
                'teams.view',
                'members.view',
            ]
        );

        $motionTeam = $this->createTeam(
            name: 'فريق الموشن جرافيك',
            code: 'motion_graphic',
            permissions: [
                'dashboard.view',
                'clients.view',
                'leads.view',
                'services.view',
                'projects.view',
                'projects.create',
                'projects.edit',
                'projects.change_status',
                'tasks.view',
                'tasks.create',
                'tasks.edit',
                'tasks.change_status',
                'teams.view',
                'members.view',
            ]
        );

        $graphicTeam = $this->createTeam(
            name: 'فريق الجرافيك ديزاين',
            code: 'graphic_design',
            permissions: [
                'dashboard.view',
                'clients.view',
                'leads.view',
                'services.view',
                'projects.view',
                'tasks.view',
                'tasks.create',
                'tasks.edit',
                'tasks.change_status',
            ]
        );

        $socialTeam = $this->createTeam(
            name: 'فريق السوشيال ميديا',
            code: 'social_media',
            permissions: [
                'dashboard.view',
                'clients.view',
                'leads.view',
                'services.view',
                'projects.view',
                'tasks.view',
                'tasks.create',
                'tasks.edit',
                'tasks.change_status',
                'campaigns.view',
                'campaigns.create',
                'campaigns.edit',
            ]
        );

        $islam = $this->createUserMember(
            name: 'إسلام',
            email: 'islam@hololcrm.test',
            team: $programmingTeam,
            jobTitle: 'Team Leader - Programming',
            isManager: true,
            roleName: 'Team Leader'
        );

        $maya = $this->createUserMember(
            name: 'مايا',
            email: 'maya@hololcrm.test',
            team: $programmingTeam,
            jobTitle: 'Front End Developer',
            isManager: false,
            roleName: 'Team Member',
            manager: $islam
        );

        $rana = $this->createUserMember(
            name: 'رانا',
            email: 'rana@hololcrm.test',
            team: $motionTeam,
            jobTitle: 'Team Leader - Motion Graphic',
            isManager: true,
            roleName: 'Team Leader'
        );

        $sara = $this->createUserMember(
            name: 'سارة',
            email: 'sara@hololcrm.test',
            team: $motionTeam,
            jobTitle: 'Motion Graphic Designer',
            isManager: false,
            roleName: 'Team Member',
            manager: $rana
        );

        $ibrahim = $this->createUserMember(
            name: 'إبراهيم',
            email: 'ibrahim@hololcrm.test',
            team: $graphicTeam,
            jobTitle: 'Graphic Designer',
            isManager: false,
            roleName: 'Team Member'
        );

        $bassem = $this->createUserMember(
            name: 'باسم',
            email: 'bassem@hololcrm.test',
            team: $socialTeam,
            jobTitle: 'Social Media Specialist',
            isManager: false,
            roleName: 'Social Media'
        );

        $sohaila = $this->createUserMember(
            name: 'سهيلة',
            email: 'sohaila@hololcrm.test',
            team: $socialTeam,
            jobTitle: 'Social Media Specialist',
            isManager: false,
            roleName: 'Social Media'
        );

        $programmingTeam->update([
            'manager_member_id' => $islam->id,
        ]);

        $motionTeam->update([
            'manager_member_id' => $rana->id,
        ]);

        $graphicTeam->update([
            'manager_member_id' => $ibrahim->id,
        ]);

        $socialTeam->update([
            'manager_member_id' => $bassem->id,
        ]);

        Member::query()
            ->where('team_id', $socialTeam->id)
            ->whereNull('manager_id')
            ->where('id', '!=', $bassem->id)
            ->update([
                'manager_id' => $bassem->id,
            ]);
    }

    private function createTeam(string $name, string $code, array $permissions): Team
    {
        $team = Team::query()->updateOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'role_name' => 'team_' . $code,
                'description' => 'فريق داخل الشركة',
                'status' => true,
            ]
        );

        $team->ensureRole();
        $team->syncRolePermissions($permissions);

        return $team;
    }

    private function createUserMember(
        string $name,
        string $email,
        Team $team,
        string $jobTitle,
        bool $isManager,
        string $roleName,
        ?Member $manager = null
    ): Member {
        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'phone' => null,
                'status' => true,
                'email_verified_at' => now(),
            ]
        );

        $user->assignRole($roleName);

        $member = Member::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'team_id' => $team->id,
                'manager_id' => $manager?->id,
                'name' => $name,
                'job_title' => $jobTitle,
                'department' => $team->name,
                'email' => $email,
                'phone' => null,
                'mobile' => null,
                'hire_date' => now()->toDateString(),
                'is_manager' => $isManager,
                'status' => 'active',
                'notes' => 'حساب شركة فعلي للاختبار',
            ]
        );

        $member->load(['user', 'team']);
        $member->syncUserTeamRole();

        return $member;
    }
}