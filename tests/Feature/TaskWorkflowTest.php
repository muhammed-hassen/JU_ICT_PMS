<?php

namespace Tests\Feature;

use App\Models\Phase;
use App\Models\PhaseStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The core workflow: a member works their task, the team leader reviews and completes it. */
class TaskWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $leader;

    private User $member;

    private User $otherMember;

    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->leader = User::factory()->create();
        $this->leader->assignRole('Team Leader');
        $this->member = User::factory()->create();
        $this->member->assignRole('Team Member');
        $this->otherMember = User::factory()->create();
        $this->otherMember->assignRole('Team Member');

        $team = Team::create(['name' => 'Dev', 'team_leader_id' => $this->leader->id]);
        $team->members()->attach([$this->leader->id, $this->member->id, $this->otherMember->id]);

        $project = Project::create(['name' => 'Portal', 'status' => 'active', 'created_by' => $this->leader->id]);
        $project->teams()->attach($team);
        $phase = Phase::create(['project_id' => $project->id, 'phase_status_id' => PhaseStatus::first()->id, 'name' => 'Build', 'sort_order' => 1, 'created_by' => $this->leader->id]);

        $this->task = Task::create([
            'phase_id' => $phase->id,
            'task_status_id' => $this->statusId('Not Started'),
            'title' => 'Configure servers',
            'assigned_to' => $this->member->id,
            'created_by' => $this->leader->id,
        ]);
    }

    private function statusId(string $name): int
    {
        return TaskStatus::where('name', $name)->value('id');
    }

    public function test_member_moves_their_task_to_review_and_the_leader_completes_it(): void
    {
        $this->actingAs($this->member)->patch(route('admin.tasks.update-status', $this->task), ['task_status_id' => $this->statusId('In Progress')])->assertRedirect();
        $this->actingAs($this->member)->patch(route('admin.tasks.update-status', $this->task), ['task_status_id' => $this->statusId('Under Review')])->assertRedirect();

        // Completing is the reviewer's call.
        $this->actingAs($this->member)->patch(route('admin.tasks.update-status', $this->task), ['task_status_id' => $this->statusId('Completed')])->assertForbidden();

        $this->actingAs($this->leader)->patch(route('admin.tasks.update-status', $this->task), ['task_status_id' => $this->statusId('Completed')])->assertRedirect();
        $this->assertSame('Completed', $this->task->fresh()->status->name);
        $this->assertNotNull($this->task->fresh()->completed_at);
    }

    public function test_member_cannot_change_a_task_assigned_to_someone_else(): void
    {
        $this->actingAs($this->otherMember)
            ->patch(route('admin.tasks.update-status', $this->task), ['task_status_id' => $this->statusId('In Progress')])
            ->assertForbidden();
    }

    public function test_member_only_sees_allowed_next_statuses(): void
    {
        $options = $this->task->statusOptionsFor($this->member)->pluck('name')->all();

        $this->assertSame(['Not Started', 'In Progress'], $options);
    }
}
