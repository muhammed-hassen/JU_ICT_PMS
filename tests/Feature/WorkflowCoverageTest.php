<?php

namespace Tests\Feature;

use App\Models\Phase;
use App\Models\PhaseStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\Team;
use App\Models\User;
use App\Notifications\DeadlineAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The workflow document's steps that were added on top of the task flow. */
class WorkflowCoverageTest extends TestCase
{
    use RefreshDatabase;

    private User $leader;

    private User $member;

    private Project $project;

    private Phase $phase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->leader = User::factory()->create();
        $this->leader->assignRole('Team Leader');
        $this->member = User::factory()->create();
        $this->member->assignRole('Team Member');

        $team = Team::create(['name' => 'Infra', 'team_leader_id' => $this->leader->id]);
        $team->members()->attach([$this->leader->id, $this->member->id]);

        $this->project = Project::create(['name' => 'Data centre', 'status' => 'active', 'created_by' => $this->leader->id, 'budget' => 1000]);
        $this->project->teams()->attach($team);
        $this->phase = Phase::create(['project_id' => $this->project->id, 'phase_status_id' => PhaseStatus::where('name', 'Not Started')->value('id'), 'name' => 'Execution', 'sort_order' => 1, 'created_by' => $this->leader->id]);
    }

    private function task(array $attributes = []): Task
    {
        return Task::create($attributes + [
            'phase_id' => $this->phase->id,
            'task_status_id' => TaskStatus::where('name', 'Not Started')->value('id'),
            'title' => 'Configure servers',
            'assigned_to' => $this->member->id,
            'created_by' => $this->leader->id,
        ]);
    }

    public function test_leader_records_task_cost_and_the_project_budget_adds_it_up(): void
    {
        $this->actingAs($this->leader)->post(route('admin.phases.tasks.store', $this->phase), [
            'title' => 'Buy switches',
            'task_status_id' => TaskStatus::where('name', 'Not Started')->value('id'),
            'estimated_cost' => 600,
            'actual_cost' => 1200,
            'resources' => '2 switches',
        ])->assertRedirect();

        $summary = $this->project->fresh()->budget_summary;
        $this->assertSame(600.0, $summary['planned']);
        $this->assertSame(1200.0, $summary['spent']);
        $this->assertSame(-200.0, $summary['remaining']);

        $this->actingAs($this->leader)->get(route('admin.projects.show', $this->project))->assertOk()->assertSee('Budget and resources');
    }

    public function test_leader_adds_and_reaches_a_milestone_but_a_member_cannot(): void
    {
        $this->actingAs($this->leader)->post(route('admin.phases.milestones.store', $this->phase), [
            'title' => 'Servers racked', 'deliverable' => 'Rack diagram', 'due_date' => now()->addWeek()->toDateString(),
        ])->assertRedirect();

        $milestone = $this->phase->milestones()->first();
        $this->actingAs($this->leader)->patch(route('admin.milestones.toggle', $milestone))->assertRedirect();
        $this->assertNotNull($milestone->fresh()->completed_at);

        $this->actingAs($this->member)->post(route('admin.phases.milestones.store', $this->phase), ['title' => 'Nope'])->assertForbidden();
        // Members work from their own tasks; phase pages are for leaders and the Director.
        $this->actingAs($this->member)->get(route('admin.phases.show', $this->phase))->assertForbidden();
        $this->actingAs($this->leader)->get(route('admin.phases.show', $this->phase))->assertOk()->assertSee('Servers racked');
    }

    public function test_completing_the_last_task_closes_the_phase_and_lets_the_owner_close_the_project(): void
    {
        $task = $this->task(['task_status_id' => TaskStatus::where('name', 'Under Review')->value('id')]);

        $this->actingAs($this->leader)->post(route('admin.projects.close', $this->project))->assertRedirect();
        $this->assertSame('active', $this->project->fresh()->status);

        $this->actingAs($this->leader)->patch(route('admin.tasks.update-status', $task), [
            'task_status_id' => TaskStatus::where('name', 'Completed')->value('id'),
        ])->assertRedirect();

        $this->assertSame('Completed', $this->phase->fresh()->status->name);

        $this->actingAs($this->leader)->post(route('admin.projects.close', $this->project))->assertRedirect();
        $this->assertSame('completed', $this->project->fresh()->status);
    }

    public function test_member_cannot_complete_a_task_through_the_edit_form(): void
    {
        $task = $this->task(['task_status_id' => TaskStatus::where('name', 'Under Review')->value('id')]);

        $this->actingAs($this->member)->put(route('admin.tasks.update', $task), [
            'title' => $task->title,
            'task_status_id' => TaskStatus::where('name', 'Completed')->value('id'),
        ])->assertForbidden();
    }

    public function test_deadline_alerts_go_out_once_per_day(): void
    {
        $this->task(['title' => 'Late one', 'deadline' => now()->subDays(2)->toDateString()]);
        $this->task(['title' => 'Due soon', 'deadline' => now()->addDay()->toDateString()]);

        $this->artisan('pms:deadline-alerts')->assertSuccessful();
        $this->artisan('pms:deadline-alerts')->assertSuccessful();

        $titles = $this->member->notifications()->where('type', DeadlineAlert::class)->get()->pluck('data.title');
        $this->assertEqualsCanonicalizing(['Overdue: Late one', 'Due tomorrow: Due soon'], $titles->all());
        // The leader hears about overdue work only.
        $this->assertSame(['Overdue: Late one'], $this->leader->notifications()->get()->pluck('data.title')->all());
    }

    public function test_there_is_no_self_service_profile_page(): void
    {
        // Name, email and password are changed by an administrator in User Management only.
        $this->actingAs($this->member)->get('/profile')->assertNotFound();
        $this->actingAs($this->member)->patch('/profile', ['email' => 'new@ju.edu.et'])->assertNotFound();
        $this->assertNotSame('new@ju.edu.et', $this->member->fresh()->email);
    }

    public function test_org_chart_and_reports_render(): void
    {
        $director = tap(User::factory()->create())->assignRole('ICT Director');

        $this->actingAs($director)->get(route('admin.organization.chart'))->assertOk()->assertSee('Infra');
        $this->actingAs($director)->get(route('admin.analytics.index'))->assertOk()->assertSee('Spent / budget');
        $this->actingAs($director)->get(route('admin.analytics.export.excel'))->assertOk();
    }
}
