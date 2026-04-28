<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ========== 1. CREATE ALL PERMISSIONS ==========
        $permissions = [
            'view users', 'create users', 'edit users', 'delete users',
            'view roles', 'create roles', 'edit roles', 'delete roles',
            'view permissions', 'assign permissions',
            'access dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // ========== 2. CREATE ROLES & ASSIGN PERMISSIONS ==========

        // SUPER ADMIN - gets ALL permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions);

        // ADMIN - gets limited permissions
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'view users', 'create users', 'edit users',
            'view roles', 'view permissions',
            'access dashboard',
        ]);

        // MANAGER - gets basic permissions
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'view users',
            'access dashboard',
        ]);

        // USER - gets only dashboard access
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->syncPermissions([
            'access dashboard',
        ]);

        // ========== 3. ASSIGN ROLE TO EXISTING USER ==========
        $firstUser = User::first();
        if ($firstUser) {
            $firstUser->assignRole('super-admin');
            $this->command->info('Assigned super-admin role to: '.$firstUser->email);
        } else {
            $this->command->warn('No user found. Create a user first, then run this seeder again.');
        }

        $this->command->info('✅ Permissions and roles seeded successfully!');
    }
}
