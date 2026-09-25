<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================================
        // 1. CREATE ALL PERMISSIONS FROM CONFIG
        // ============================================================
        $guardName = config('rbac.guard_name', 'web');
        $permissions = collect(config('rbac.permissions', []))
            ->flatMap(fn (array $items, string $module) => collect($items)->map(
                fn (array $permission) => [
                    'name' => $permission['name'],
                    'guard_name' => $guardName,
                    'module' => $module,
                    'description' => $permission['description'] ?? null,
                ]
            ));

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ],
                [
                    'module' => $permission['module'],
                    'description' => $permission['description'],
                ]
            );
        }

        $this->command->info('✅ '.Permission::count().' permissions created from config');

        // ============================================================
        // 2. GET ALL PERMISSION NAMES
        // ============================================================
        $allPermissionNames = Permission::all()->pluck('name')->toArray();

        // ============================================================
        // 3. CREATE ROLES WITH PERMISSIONS FROM CONFIG
        // ============================================================
        $roles = config('rbac.roles', []);

        foreach ($roles as $roleName => $roleConfig) {
            $role = Role::updateOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => $guardName,
                ],
                [
                    'description' => $roleConfig['description'] ?? null,
                ]
            );

            // If permissions is '*', give ALL permissions
            if ($roleConfig['permissions'] === '*') {
                $role->syncPermissions($allPermissionNames);
                $this->command->info("✅ {$roleName} got ALL ".count($allPermissionNames).' permissions');
            } else {
                $role->syncPermissions($roleConfig['permissions'] ?? []);
                $this->command->info("✅ {$roleName} got ".count($roleConfig['permissions'] ?? []).' permissions');
            }
        }

        // ============================================================
        // 4. CREATE USERS FROM CONFIG
        // ============================================================
        $users = config('rbac.seed_users', []);

        // Default users if config is empty
        if (empty($users)) {
            $users = [
                [
                    'name' => 'ICT Director',
                    'email' => 'director@ict.ju.edu.et',
                    'password' => 'Director@123',
                    'role' => 'ICT Director',
                ],
                [
                    'name' => 'System Administrator',
                    'email' => 'admin@ict.ju.edu.et',
                    'password' => 'Admin@123',
                    'role' => 'System Administrator',
                ],
                [
                    'name' => 'Team Leader',
                    'email' => 'teamleader@ict.ju.edu.et',
                    'password' => 'Leader@123',
                    'role' => 'Team Leader',
                ],
                [
                    'name' => 'Team Member',
                    'email' => 'member@ict.ju.edu.et',
                    'password' => 'Member@123',
                    'role' => 'Team Member',
                ],
            ];
        }

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => now(),
                ]
            );

            if (isset($userData['role'])) {
                $user->syncRoles([$userData['role']]);

                // If role is ICT Director or System Administrator, give ALL permissions directly
                if (in_array($userData['role'], ['ICT Director', 'System Administrator', 'Super Admin'])) {
                    $user->givePermissionTo($allPermissionNames);
                }

                $this->command->info("✅ {$userData['name']} created with role: {$userData['role']}");
            }
        }

        // ============================================================
        // 5. VERIFY ICT DIRECTOR
        // ============================================================
        $director = User::where('email', 'director@ict.ju.edu.et')->first();
        if ($director) {
            $permCount = $director->getAllPermissions()->count();
            $hasViewPhases = $director->hasPermissionTo('view-phases');
            $hasAccessAdmin = $director->hasPermissionTo('access-admin');

            $this->command->info('========================================');
            $this->command->info('✅ ICT DIRECTOR VERIFIED!');
            $this->command->info('   Email: director@ict.ju.edu.et');
            $this->command->info('   Password: Director@123');
            $this->command->info("   Permissions: {$permCount}");
            $this->command->info('   Can view phases: '.($hasViewPhases ? 'YES ✅' : 'NO ❌'));
            $this->command->info('   Can access admin: '.($hasAccessAdmin ? 'YES ✅' : 'NO ❌'));
            $this->command->info('========================================');
        }

        // ============================================================
        // 6. CLEAR CACHE
        // ============================================================
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('✅ All roles and permissions seeded successfully!');
    }
}
