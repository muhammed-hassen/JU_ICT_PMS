<?php

namespace Tests\Feature\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_system_administrator_can_open_role_management(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('System Administrator');

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('Role Catalog');
    }

    public function test_team_member_cannot_open_role_management(): void
    {
        $member = User::factory()->create();
        $member->assignRole('Team Member');

        $this->actingAs($member)
            ->get(route('admin.roles.index'))
            ->assertForbidden();
    }

    public function test_role_store_assigns_permissions_to_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('System Administrator');

        $permissions = Permission::query()
            ->whereIn('name', ['view-dashboard', 'view-own-tasks'])
            ->pluck('id')
            ->all();

        $response = $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => 'Quality Reviewer',
            'description' => 'Reviews and tracks assigned work',
            'permissions' => $permissions,
        ]);

        $response->assertRedirect(route('admin.roles.index'));

        $role = Role::findByName('Quality Reviewer');

        $this->assertTrue($role->hasPermissionTo('view-dashboard'));
        $this->assertTrue($role->hasPermissionTo('view-own-tasks'));
    }
}
