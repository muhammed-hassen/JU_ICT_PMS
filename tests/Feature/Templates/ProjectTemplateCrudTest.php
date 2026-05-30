<?php

namespace Tests\Feature\Templates;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTemplateCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_team_member_cannot_open_template_management(): void
    {
        $member = User::factory()->create();
        $member->assignRole('Team Member');

        $this->actingAs($member)
            ->get(route('admin.templates.index'))
            ->assertForbidden();
    }

    public function test_authorized_user_can_create_template_with_phases_and_tasks(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('System Administrator');

        $response = $this->actingAs($admin)->post(route('admin.templates.store'), [
            'name' => 'Website Revamp',
            'description' => 'Standard delivery flow',
            'is_active' => '1',
            'phases' => [
                [
                    'name' => 'Planning',
                    'description' => 'Requirement gathering',
                    'sort_order' => 1,
                    'tasks' => [
                        [
                            'title' => 'Collect requirements',
                            'description' => 'Interview stakeholders',
                            'sort_order' => 1,
                            'estimated_hours' => 4,
                        ],
                        [
                            'title' => 'Define scope',
                            'description' => 'Document scope',
                            'sort_order' => 2,
                            'estimated_hours' => 2,
                        ],
                    ],
                ],
                [
                    'name' => 'Implementation',
                    'description' => 'Build features',
                    'sort_order' => 2,
                    'tasks' => [
                        [
                            'title' => 'Create UI',
                            'description' => 'Build screens',
                            'sort_order' => 1,
                            'estimated_hours' => 8,
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.templates.index'));

        $this->assertDatabaseHas('project_templates', [
            'name' => 'Website Revamp',
            'description' => 'Standard delivery flow',
        ]);

        $templateId = (int) \DB::table('project_templates')
            ->where('name', 'Website Revamp')
            ->value('id');

        $this->assertDatabaseHas('template_phases', [
            'project_template_id' => $templateId,
            'name' => 'Planning',
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('template_phases', [
            'project_template_id' => $templateId,
            'name' => 'Implementation',
            'sort_order' => 2,
        ]);

        $planningPhaseId = (int) \DB::table('template_phases')
            ->where('project_template_id', $templateId)
            ->where('name', 'Planning')
            ->value('id');

        $implementationPhaseId = (int) \DB::table('template_phases')
            ->where('project_template_id', $templateId)
            ->where('name', 'Implementation')
            ->value('id');

        $this->assertDatabaseHas('template_tasks', [
            'template_phase_id' => $planningPhaseId,
            'title' => 'Collect requirements',
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('template_tasks', [
            'template_phase_id' => $planningPhaseId,
            'title' => 'Define scope',
            'sort_order' => 2,
        ]);

        $this->assertDatabaseHas('template_tasks', [
            'template_phase_id' => $implementationPhaseId,
            'title' => 'Create UI',
            'sort_order' => 1,
        ]);
    }

    public function test_authorized_user_can_open_template_create_page(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('System Administrator');

        $this->actingAs($admin)
            ->get(route('admin.templates.create'))
            ->assertOk()
            ->assertSee('Create Template')
            ->assertSee('Build the default phase and task structure')
            ->assertSee('card-elevated', false)
            ->assertSee('bg-primary-gradient', false)
            ->assertSee('bg-secondary-gradient', false)
            ->assertSee('This template is active and available for use')
            ->assertSee('Phases')
            ->assertSee('Phase Details')
            ->assertSee('Add Phase')
            ->assertSee('Previous Task')
            ->assertSee('Next Task')
            ->assertSee('task-pagination-summary', false)
            ->assertSee('Task pagination')
            ->assertDontSee('Phase List');
    }

    public function test_authorized_user_can_open_template_index_page_with_enhanced_catalog_ui(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('System Administrator');

        $this->actingAs($admin)
            ->get(route('admin.templates.index'))
            ->assertOk()
            ->assertSee('Project Templates')
            ->assertSee('Template Catalog')
            ->assertSee('card-elevated', false)
            ->assertSee('template-count-label', false)
            ->assertSee('template-index-table', false)
            ->assertSee('Create Template')
            ->assertDontSee('No description');
    }

    public function test_template_index_is_paginated_to_keep_the_catalog_short(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('System Administrator');

        for ($i = 1; $i <= 11; $i++) {
            DB::table('project_templates')->insert([
                'name' => sprintf('A Template %02d', $i),
                'description' => 'Sample template',
                'is_active' => true,
                'created_by' => $admin->id,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->actingAs($admin)
            ->get(route('admin.templates.index'))
            ->assertOk()
            ->assertSee('A Template 01')
            ->assertSee('A Template 08')
            ->assertDontSee('A Template 09')
            ->assertSee('Showing 1 to 8 of 16 templates');

        $this->actingAs($admin)
            ->get(route('admin.templates.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('A Template 09')
            ->assertSee('A Template 11')
            ->assertSee('Full-Stack Web Application')
            ->assertSee('Mobile Application')
            ->assertSee('Desktop Application')
            ->assertSee('Data Analytics Dashboard')
            ->assertSee('E-Commerce Platform');
    }
}
