<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Team Leaders and Team Members get project files and their own notifications.
 * config/rbac.php has the same list for fresh seeds; this brings existing
 * databases in line without reseeding.
 */
return new class extends Migration
{
    private array $permissions = [
        'view-project-files',
        'upload-project-files',
        'download-files',
        'delete-own-files',
        'view-own-notifications',
        'mark-notifications-read',
    ];

    public function up(): void
    {
        foreach ($this->permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        foreach (['Team Leader', 'Team Member'] as $roleName) {
            Role::where('name', $roleName)->where('guard_name', 'web')->first()?->givePermissionTo($this->permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        foreach (['Team Leader', 'Team Member'] as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            foreach ($this->permissions as $name) {
                $role?->revokePermissionTo($name);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
