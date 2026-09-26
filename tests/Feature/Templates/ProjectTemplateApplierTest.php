<?php

namespace Tests\Feature\Templates;

use App\Models\User;
use App\Services\ProjectTemplateApplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProjectTemplateApplierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_template_applier_creates_project_phases_and_tasks_in_the_correct_structure(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('System Administrator');

        $templateId = DB::table('project_templates')->insertGetId([
            'name' => 'Mobile App',
            'description' => 'Delivery template',
            'is_active' => true,
            'created_by' => $admin->id,
            'updated_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $planningTemplatePhaseId = DB::table('template_phases')->insertGetId([
            'project_template_id' => $templateId,
            'name' => 'Planning',
            'description' => 'Discovery work',
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $buildTemplatePhaseId = DB::table('template_phases')->insertGetId([
            'project_template_id' => $templateId,
            'name' => 'Build',
            'description' => 'Execution work',
            'sort_order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('template_tasks')->insert([
            [
                'template_phase_id' => $planningTemplatePhaseId,
                'task_priority_id' => null,
                'title' => 'Define goals',
                'description' => 'Clarify goals',
                'sort_order' => 1,
                'estimated_hours' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'template_phase_id' => $buildTemplatePhaseId,
                'task_priority_id' => null,
                'title' => 'Create API integration',
                'description' => 'Wire backend',
                'sort_order' => 1,
                'estimated_hours' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $projectId = DB::table('projects')->insertGetId([
            'name' => 'Student App',
            'template_id' => $templateId,
            'created_by' => $admin->id,
            'updated_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(ProjectTemplateApplier::class)->apply($projectId, $templateId, $admin->id);

        $this->assertDatabaseHas('phases', [
            'project_id' => $projectId,
            'name' => 'Planning',
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('phases', [
            'project_id' => $projectId,
            'name' => 'Build',
            'sort_order' => 2,
        ]);

        $planningPhaseId = (int) DB::table('phases')
            ->where('project_id', $projectId)
            ->where('name', 'Planning')
            ->value('id');

        $buildPhaseId = (int) DB::table('phases')
            ->where('project_id', $projectId)
            ->where('name', 'Build')
            ->value('id');

        $this->assertDatabaseHas('tasks', [
            'phase_id' => $planningPhaseId,
            'title' => 'Define goals',
            'estimated_hours' => 2,
        ]);

        $this->assertDatabaseHas('tasks', [
            'phase_id' => $buildPhaseId,
            'title' => 'Create API integration',
            'estimated_hours' => 6,
        ]);
    }
}
