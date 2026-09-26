<?php

use App\Models\User;
use Database\Seeders\ProjectTemplateExampleSeeder;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Brings existing databases in line with the seeders:
 * - assign-role (changing a user's role) for the Director and System Administrator
 * - the four extra example project templates (mobile, desktop, analytics, e-commerce)
 */
return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => 'assign-role', 'guard_name' => 'web'],
            ['module' => 'user', 'description' => 'Can assign roles to users']
        );

        foreach (['ICT Director', 'System Administrator'] as $roleName) {
            Role::where('name', $roleName)->where('guard_name', 'web')->first()?->givePermissionTo($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // The seeder uses updateOrCreate by name, so running it again is safe.
        if (User::query()->exists()) {
            (new ProjectTemplateExampleSeeder)->run();
        }
    }

    public function down(): void
    {
        Permission::where('name', 'assign-role')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
