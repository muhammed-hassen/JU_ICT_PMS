<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            // 1. Reference data
            ProjectTemplateReferenceSeeder::class,

            // 2. Permissions
            PermissionSeeder::class,

            // 3. Roles
            RoleSeeder::class,

            // 4. Users
            RbacUserSeeder::class,

            // 5. Templates
            ProjectTemplateExampleSeeder::class,

            // 6. Teams
            TeamSeeder::class,

            // 7. 🔥 ASSIGN PERMISSIONS TO ALL USERS
            UserPermissionsSeeder::class,
        ]);
    }
}
