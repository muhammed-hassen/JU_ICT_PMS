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
        $guardName = config('rbac.guard_name', 'web');
        $allPermissionNames = Permission::query()->pluck('name')->all();

        foreach (config('rbac.roles', []) as $roleName => $roleConfig) {
            $role = Role::query()->updateOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => $guardName,
                ],
                [
                    'description' => $roleConfig['description'] ?? null,
                ]
            );

            $permissions = $roleConfig['permissions'] === '*'
                ? $allPermissionNames
                : $roleConfig['permissions'];

            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
