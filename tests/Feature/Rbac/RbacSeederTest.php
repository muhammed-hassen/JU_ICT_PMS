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

        // Accounts and the permission system belong to the System Administrator;
        // project work and the organization belong to the Director.
        $this->assertTrue($adminRole->hasPermissionTo('configure-system'));
        $this->assertTrue($adminRole->hasPermissionTo('assign-role'));
        $this->assertFalse($adminRole->hasPermissionTo('view-all-projects'));
        $this->assertFalse($directorRole->hasPermissionTo('configure-system'));
        $this->assertFalse($directorRole->hasPermissionTo('assign-role'));
        $this->assertTrue($directorRole->hasPermissionTo('view-all-projects'));
        $this->assertTrue($teamMemberRole->hasPermissionTo('view-own-tasks'));
        $this->assertFalse($teamMemberRole->hasPermissionTo('view-all-tasks'));
    }
}
