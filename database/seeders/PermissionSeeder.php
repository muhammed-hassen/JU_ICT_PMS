<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Seed the application's RBAC permissions.
     */
    public function run(): void
    {
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
            Permission::query()->updateOrCreate(
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

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('✅ '.Permission::count().' permissions seeded');
    }
}
