<?php

namespace Tests\Feature\Rbac;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_rbac_seeders_create_expected_roles_and_permissions(): void
    {
        $this->seed();

        $this->assertDatabaseHas('roles', ['name' => 'System Administrator']);
        $this->assertDatabaseHas('roles', ['name' => 'ICT Director']);
        $this->assertDatabaseHas('permissions', ['name' => 'view-all-projects', 'module' => 'project']);
        $this->assertDatabaseHas('permissions', ['name' => 'configure-system', 'module' => 'system']);

        $adminRole = Role::findByName('System Administrator');
        $directorRole = Role::findByName('ICT Director');
        $teamMemberRole = Role::findByName('Team Member');

        $this->assertSame(Permission::count(), $adminRole->permissions()->count());
        $this->assertSame(Permission::count(), $directorRole->permissions()->count());
        $this->assertTrue($directorRole->hasPermissionTo('configure-system'));
        $this->assertTrue($directorRole->hasPermissionTo('assign-role'));
        $this->assertTrue($teamMemberRole->hasPermissionTo('view-own-tasks'));
        $this->assertFalse($teamMemberRole->hasPermissionTo('view-all-tasks'));
    }
}
