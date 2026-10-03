<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's RBAC roles and assignments.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guardName = config('rbac.guard_name', 'web');

        // Get all permission names
        $allPermissionNames = Permission::query()->pluck('name')->all();

        // Get roles from config
        $roles = config('rbac.roles', []);

        foreach ($roles as $roleName => $roleConfig) {
            $role = Role::query()->updateOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => $guardName,
                ],
                [
                    'description' => $roleConfig['description'] ?? null,
                ]
            );

            // Assign permissions
            $permissions = $roleConfig['permissions'] ?? [];

            // If permissions is '*', give ALL permissions
            if ($permissions === '*') {
                $role->syncPermissions($allPermissionNames);
            } else {
                $role->syncPermissions($permissions);
            }
        }

        // Clear cache again
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
