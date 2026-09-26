<?php

namespace Tests\Feature\Rbac;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Need-to-know: each role reaches its own pages and is refused everything else. */
class RoleAccessMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public static function matrix(): array
    {
        return [
            'System Administrator' => ['System Administrator',
                ['/users', '/admin/roles', '/admin/permissions', '/templates', '/templates/create', '/admin/activity', '/messages'],
                ['/projects', '/projects/create', '/tasks', '/phases', '/analytics', '/admin/organization/teams', '/admin/organization/chart'],
            ],
            'ICT Director' => ['ICT Director',
                ['/projects', '/projects/create', '/tasks', '/tasks/kanban', '/phases', '/analytics', '/admin/organization/chart',
                    '/admin/organization/teams', '/admin/organization/directors', '/templates', '/admin/activity'],
                ['/users', '/admin/roles', '/admin/permissions', '/templates/create'],
            ],
            'Team Leader' => ['Team Leader',
                ['/projects', '/projects/create', '/tasks', '/tasks/kanban', '/phases', '/analytics', '/admin/organization/teams', '/messages'],
                ['/users', '/admin/roles', '/templates', '/admin/activity', '/admin/organization/chart', '/admin/organization/directors',
                    '/admin/organization/team-leaders', '/admin/organization/members'],
            ],
            'Team Member' => ['Team Member',
                ['/home', '/tasks/my', '/projects', '/messages', '/notifications'],
                ['/tasks', '/tasks/kanban', '/phases', '/phase-dashboard', '/projects/create', '/analytics', '/templates',
                    '/admin/organization/teams', '/admin/organization/chart', '/admin/activity', '/users'],
            ],
        ];
    }

    #[DataProvider('matrix')]
    public function test_role_reaches_only_its_own_pages(string $role, array $allowed, array $refused): void
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        foreach ($allowed as $url) {
            $this->actingAs($user)->get($url)->assertOk();
        }
        foreach ($refused as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }
    }

    public function test_permissions_come_from_roles_only(): void
    {
        $this->assertSame(0, User::query()->whereHas('permissions')->count());
    }

    public function test_system_administrator_is_not_treated_as_the_director(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('System Administrator');

        $this->assertFalse($admin->isDirector());
        $this->assertSame([], $admin->getVisibleProjectIds());
    }
}
