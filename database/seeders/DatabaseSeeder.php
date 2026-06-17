<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            SettingsSeeder::class,
            ClientsSeeder::class,
            ServicesSeeder::class,
            SalesSeeder::class,
            LeadsSeeder::class,
        ]);
    }
}