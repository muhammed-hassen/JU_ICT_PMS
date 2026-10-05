<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Phase;
use App\Models\PhaseStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_and_project_changes_are_written_to_the_activity_log(): void
    {
        $this->seed();
        $director = User::factory()->create();
        $director->assignRole('ICT Director');
        $member = User::factory()->create(['name' => 'Abebe Kebede']);
        $this->actingAs($director);

        $project = Project::create(['name' => 'Portal', 'status' => 'draft', 'created_by' => $director->id]);
        $project->update(['status' => 'active']);

        $phase = Phase::create(['project_id' => $project->id, 'phase_status_id' => PhaseStatus::first()->id, 'name' => 'Build', 'sort_order' => 1, 'created_by' => $director->id]);
        $task = Task::create(['phase_id' => $phase->id, 'task_status_id' => TaskStatus::where('name', 'Not Started')->value('id'), 'title' => 'Write API', 'created_by' => $director->id]);
        $task->assignTo($member);
        $task->transitionTo(TaskStatus::where('name', 'In Progress')->first());
        $task->update(['title' => 'Write the API']);

        $actions = ActivityLog::orderBy('id')->pluck('action')->all();
        $this->assertSame(['created', 'status_changed', 'created', 'assigned', 'status_changed', 'updated'], $actions);
        $this->assertDatabaseHas('activity_logs', ['description' => 'Write API assigned to Abebe Kebede', 'user_id' => $director->id]);
    }
}
