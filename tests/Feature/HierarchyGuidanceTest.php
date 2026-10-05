<?php

namespace Tests\Feature;

use App\Models\Phase;
use App\Models\PhaseStatus;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Work goes Project, then Phase, then Task. Skipping a level explains what to create first. */
class HierarchyGuidanceTest extends TestCase
{
    use RefreshDatabase;

    private User $leader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->leader = User::factory()->create();
        $this->leader->assignRole('Team Leader');
    }

    private function project(): Project
    {
        $team = Team::create(['name' => 'Infra', 'team_leader_id' => $this->leader->id]);
        $project = Project::create(['name' => 'Data centre', 'status' => 'active', 'created_by' => $this->leader->id]);
        $project->teams()->attach($team);

        return $project;
    }

    private function phaseData(array $overrides = []): array
    {
        return $overrides + ['name' => 'Execution', 'phase_status_id' => PhaseStatus::first()->id];
    }

    public function test_without_a_project_create_phase_and_create_task_say_to_create_a_project(): void
    {
        $this->actingAs($this->leader)->get(route('admin.phases.create'))
            ->assertOk()->assertSee('Create a project first')->assertSee(route('admin.projects.create'));

        $this->actingAs($this->leader)->get(route('admin.tasks.create'))
            ->assertOk()->assertSee('Create a project first');
    }

    public function test_project_without_phases_says_to_add_a_phase_before_tasks(): void
    {
        $project = $this->project();

        $this->actingAs($this->leader)->get(route('admin.tasks.create'))
            ->assertOk()->assertSee('Add a phase first')->assertSee(route('admin.projects.phases.create', $project));
    }

    public function test_with_a_phase_the_user_picks_where_the_task_goes(): void
    {
        $project = $this->project();
        $this->actingAs($this->leader)->post(route('admin.projects.phases.store', $project), $this->phaseData());
        $phase = $project->phases()->first();

        $this->actingAs($this->leader)->get(route('admin.tasks.create'))
            ->assertOk()->assertSee('Execution')->assertSee(route('admin.phases.tasks.create', $phase));
    }

    public function test_a_new_phase_after_a_deleted_one_does_not_crash(): void
    {
        $project = $this->project();
        $this->actingAs($this->leader)->post(route('admin.projects.phases.store', $project), $this->phaseData());
        $project->phases()->first()->delete();

        $this->actingAs($this->leader)->post(route('admin.projects.phases.store', $project), $this->phaseData(['name' => 'Closure']))
            ->assertRedirect();
        $this->assertSame(2, Phase::where('name', 'Closure')->value('sort_order'));
    }

    public function test_a_taken_position_is_a_form_error_and_success_messages_show(): void
    {
        $project = $this->project();
        $this->actingAs($this->leader)->post(route('admin.projects.phases.store', $project), $this->phaseData())
            ->assertRedirect();

        $this->actingAs($this->leader)
            ->post(route('admin.projects.phases.store', $project), $this->phaseData(['name' => 'Again', 'sort_order' => 1]))
            ->assertSessionHasErrors('sort_order');

        // The success message is rendered by the layout on whatever page the redirect lands on.
        $this->actingAs($this->leader)->followingRedirects()
            ->post(route('admin.projects.phases.store', $project), $this->phaseData(['name' => 'Monitoring']))
            ->assertSee("Phase 'Monitoring' created successfully.");
    }
}
