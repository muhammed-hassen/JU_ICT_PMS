<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Gives Team Leaders and Team Members what messaging, their own dashboard and
 * (for leaders) report export need. config/rbac.php has the same lists for
 * fresh seeds; this brings existing databases in line without reseeding.
 */
return new class extends Migration
{
    private array $grants = [
        'Team Leader' => [
            'view-own-conversations', 'create-conversation', 'send-message', 'edit-own-message', 'delete-own-message',
            'view-team-leader-dashboard', 'view-team-reports', 'export-reports',
        ],
        'Team Member' => [
            'view-own-conversations', 'create-conversation', 'send-message', 'edit-own-message', 'delete-own-message',
            'view-member-dashboard',
        ],
    ];

    public function up(): void
    {
        foreach ($this->grants as $roleName => $permissions) {
            foreach ($permissions as $name) {
                Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            }
            Role::where('name', $roleName)->where('guard_name', 'web')->first()?->givePermissionTo($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        foreach ($this->grants as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            foreach ($permissions as $name) {
                $role?->revokePermissionTo($name);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
