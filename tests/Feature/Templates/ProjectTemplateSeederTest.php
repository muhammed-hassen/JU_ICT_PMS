<?php

namespace Tests\Feature\Templates;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTemplateSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeders_create_the_example_project_templates(): void
    {
        $this->seed();

        $this->assertSame(5, \DB::table('project_templates')->count());

        $this->assertDatabaseHas('project_templates', [
            'name' => 'Full-Stack Web Application',
            'is_active' => true,
        ]);

        $templateId = (int) \DB::table('project_templates')
            ->where('name', 'Full-Stack Web Application')
            ->value('id');

        $this->assertSame(5, \DB::table('template_phases')
            ->where('project_template_id', $templateId)
            ->count());

        $planningPhaseId = (int) \DB::table('template_phases')
            ->where('project_template_id', $templateId)
            ->where('name', 'Planning & Discovery')
            ->value('id');

        $implementationPhaseId = (int) \DB::table('template_phases')
            ->where('project_template_id', $templateId)
            ->where('name', 'Implementation')
            ->value('id');

        $deploymentPhaseId = (int) \DB::table('template_phases')
            ->where('project_template_id', $templateId)
            ->where('name', 'Deployment & Handover')
            ->value('id');

        $this->assertDatabaseHas('template_tasks', [
            'template_phase_id' => $planningPhaseId,
            'title' => 'Define scope and success criteria',
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('template_tasks', [
            'template_phase_id' => $implementationPhaseId,
            'title' => 'Build frontend screens and interactions',
        ]);

        $this->assertDatabaseHas('template_tasks', [
            'template_phase_id' => $implementationPhaseId,
            'title' => 'Develop backend APIs and business logic',
        ]);

        $this->assertDatabaseHas('template_tasks', [
            'template_phase_id' => $deploymentPhaseId,
            'title' => 'Deploy application and verify production environment',
        ]);

        $this->assertDatabaseHas('project_templates', [
            'name' => 'Mobile Application',
            'is_active' => true,
        ]);

        $mobileTemplateId = (int) \DB::table('project_templates')
            ->where('name', 'Mobile Application')
            ->value('id');

        $this->assertSame(5, \DB::table('template_phases')
            ->where('project_template_id', $mobileTemplateId)
            ->count());

        $mobileDiscoveryPhaseId = (int) \DB::table('template_phases')
            ->where('project_template_id', $mobileTemplateId)
            ->where('name', 'Discovery & Planning')
            ->value('id');

        $mobileBuildPhaseId = (int) \DB::table('template_phases')
            ->where('project_template_id', $mobileTemplateId)
            ->where('name', 'Mobile Development')
            ->value('id');

        $mobileReleasePhaseId = (int) \DB::table('template_phases')
            ->where('project_template_id', $mobileTemplateId)
            ->where('name', 'Testing & Release')
            ->value('id');

        $this->assertDatabaseHas('template_tasks', [
            'template_phase_id' => $mobileDiscoveryPhaseId,
            'title' => 'Define product requirements and success metrics',
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('template_tasks', [
            'template_phase_id' => $mobileBuildPhaseId,
            'title' => 'Build mobile screens and navigation flows',
        ]);

        $this->assertDatabaseHas('template_tasks', [
            'template_phase_id' => $mobileBuildPhaseId,
            'title' => 'Implement device capabilities and local storage',
        ]);

        $this->assertDatabaseHas('template_tasks', [
            'template_phase_id' => $mobileReleasePhaseId,
            'title' => 'Prepare store release assets and submission checklist',
        ]);

        $this->assertDatabaseHas('project_templates', [
            'name' => 'Desktop Application',
            'is_active' => true,
        ]);

        $desktopTemplateId = (int) \DB::table('project_templates')
            ->where('name', 'Desktop Application')
            ->value('id');

        $desktopBuildPhaseId = (int) \DB::table('template_phases')
            ->where('project_template_id', $desktopTemplateId)
            ->where('name', 'Desktop Development')
            ->value('id');

        $this->assertDatabaseHas('template_tasks', [
            'template_phase_id' => $desktopBuildPhaseId,
            'title' => 'Implement desktop workflows and local persistence',
        ]);

        $this->assertDatabaseHas('project_templates', [
            'name' => 'Data Analytics Dashboard',
            'is_active' => true,
        ]);

        $analyticsTemplateId = (int) \DB::table('project_templates')
            ->where('name', 'Data Analytics Dashboard')
            ->value('id');

        $analyticsModelingPhaseId = (int) \DB::table('template_phases')
            ->where('project_template_id', $analyticsTemplateId)
            ->where('name', 'Data Modeling & Preparation')
            ->value('id');

        $this->assertDatabaseHas('template_tasks', [
            'template_phase_id' => $analyticsModelingPhaseId,
            'title' => 'Clean and transform source data',
        ]);

        $this->assertDatabaseHas('project_templates', [
            'name' => 'E-Commerce Platform',
            'is_active' => true,
        ]);

        $commerceTemplateId = (int) \DB::table('project_templates')
            ->where('name', 'E-Commerce Platform')
            ->value('id');

        $commerceOperationsPhaseId = (int) \DB::table('template_phases')
            ->where('project_template_id', $commerceTemplateId)
            ->where('name', 'Checkout, Payments & Operations')
            ->value('id');

        $this->assertDatabaseHas('template_tasks', [
            'template_phase_id' => $commerceOperationsPhaseId,
            'title' => 'Implement checkout and payment workflows',
        ]);
    }
}
