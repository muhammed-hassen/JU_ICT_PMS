<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Applies the need-to-know role table in config/rbac.php to existing databases:
 * - removes permissions that were granted to individual users (the old
 *   UserPermissionsSeeder gave the Director, Admin and leaders extra ones),
 *   so a person can do exactly what their role allows and nothing more
 * - gives each role exactly its listed permissions
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table(config('permission.table_names.model_has_permissions'))
            ->where('model_type', (new User)->getMorphClass())
            ->delete();

        foreach (config('rbac.roles') as $name => $definition) {
            Role::where('name', $name)->where('guard_name', 'web')->first()
                ?->syncPermissions($definition['permissions']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // The old grants were too broad on purpose to undo; nothing to restore.
    }
};
