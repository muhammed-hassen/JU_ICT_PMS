<?php

namespace Tests\Feature\Organization;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_team_seeder_assigns_director_and_team_leader_roles(): void
    {
        $this->assertTrue(User::where('email', 'director@ict.ju.edu.et')->first()->hasRole('ICT Director'));
        $this->assertTrue(User::where('email', 'dev.lead@ict.ju.edu.et')->first()->hasRole('Team Leader'));
    }

    public function test_authorized_user_can_create_team_with_leader_and_members(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('System Administrator');

        $leader = User::factory()->create();
        $leader->assignRole('Team Leader');

        $member = User::factory()->create();
        $member->assignRole('Team Member');

        $response = $this->actingAs($admin)->post(route('admin.organization.teams.store'), [
            'name' => 'Quality Assurance Team',
            'description' => 'Reviews and validates deliverables',
            'team_leader_id' => $leader->id,
            'parent_team_id' => Team::where('name', 'ICT Directorate')->value('id'),
            'member_ids' => [$member->id],
        ]);

        $team = Team::where('name', 'Quality Assurance Team')->first();

        $response->assertRedirect(route('admin.organization.teams.index'));
        $this->assertNotNull($team);
        $this->assertSame($leader->id, $team->team_leader_id);
        $this->assertDatabaseHas('team_members', [
            'team_id' => $team->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_authorized_user_can_open_organization_index_pages(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('System Administrator');

        $this->actingAs($admin)
            ->get(route('admin.organization.teams.index'))
            ->assertOk()
            ->assertSee('Team Catalog');

        $this->actingAs($admin)
            ->get(route('admin.organization.members.index'))
            ->assertOk()
            ->assertSee('Member Directory');

        $this->actingAs($admin)
            ->get(route('admin.organization.directors.index'))
            ->assertOk()
            ->assertSee('Director Directory');

        $this->actingAs($admin)
            ->get(route('admin.organization.team-leaders.index'))
            ->assertOk()
            ->assertSee('Team Leader Directory');
    }

    public function test_authorized_user_can_create_member_and_assign_team(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('System Administrator');

        $team = Team::where('name', 'Software Development Team')->first();

        $response = $this->actingAs($admin)->post(route('admin.organization.members.store'), [
            'name' => 'New ICT Member',
            'email' => 'new.member@ict.ju.edu.et',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'team_ids' => [$team->id],
        ]);

        $member = User::where('email', 'new.member@ict.ju.edu.et')->first();

        $response->assertRedirect(route('admin.organization.members.index'));
        $this->assertTrue($member->hasRole('Team Member'));
        $this->assertDatabaseHas('team_members', [
            'team_id' => $team->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_team_member_cannot_create_team(): void
    {
        $member = User::factory()->create();
        $member->assignRole('Team Member');

        $this->actingAs($member)
            ->post(route('admin.organization.teams.store'), [
                'name' => 'Unauthorized Team',
            ])
            ->assertForbidden();
    }

    public function test_project_index_uses_seeded_project_permissions(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('System Administrator');

        $this->actingAs($admin)
            ->get(route('admin.projects.index'))
            ->assertOk();
    }
}
