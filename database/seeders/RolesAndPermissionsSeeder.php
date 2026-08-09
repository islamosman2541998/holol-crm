<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',

            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            'permissions.view',
            'permissions.assign',
            'teams.view',
            'teams.create',
            'teams.edit',
            'teams.delete',
            'teams.permissions',

            'members.view',
            'members.create',
            'members.edit',
            'members.delete',
            'settings.view',
            'settings.edit',
            'settings.design',
            'settings.login_page',

            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'clients.view_all',

            'leads.view',
            'leads.create',
            'leads.edit',
            'leads.delete',
            'leads.convert',
            'leads.view_all',

            'followups.view',
            'followups.create',
            'followups.edit',
            'followups.delete',

            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',
            'sales.view_all',

            'services.view',
            'services.create',
            'services.edit',
            'services.delete',

            'campaigns.view',
            'campaigns.create',
            'campaigns.edit',
            'campaigns.delete',

            'projects.view',
            'projects.create',
            'projects.edit',
            'projects.delete',
            'quotations.view',
            'quotations.create',
            'quotations.edit',
            'quotations.delete',
            'quotations.change_status',
            'quotations.convert_to_sale',
            'quotations.view_all',
            'payments.view',
            'payments.create',
            'payments.edit',
            'payments.delete',
            'payments.view_all',
            'projects.view',
            'projects.view_all',
            'projects.create',
            'projects.edit',
            'projects.delete',
            'projects.change_status',
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.delete',
            'tasks.view_all',
            'tasks.change_status',

            'reports.view',

        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $seoManagerRole = Role::firstOrCreate([
            'name' => 'SEO Manager',
            'guard_name' => 'web',
        ]);

        $seoManagerRole->syncPermissions($permissions);

        $admin = User::firstOrCreate(
            ['email' => 'seo@hololcrm.test'],
            [
                'name' => 'SEO Manager',
                'password' => Hash::make('password'),
            ]
        );

        $admin->assignRole($seoManagerRole);
    }
}
