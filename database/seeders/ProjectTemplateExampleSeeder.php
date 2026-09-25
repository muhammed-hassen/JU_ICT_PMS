<?php

namespace Database\Seeders;

use App\Models\ProjectTemplate;
use App\Models\TaskPriority;
use App\Models\TemplatePhase;
use App\Models\TemplateTask;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectTemplateExampleSeeder extends Seeder
{
    public function run(): void
    {
        $systemAdmin = User::query()->where('email', 'admin@ict.ju.edu.et')->first();

        if (! $systemAdmin) {
            $systemAdmin = User::query()->first();
        }

        if (! $systemAdmin) {
            $this->command->error('No user found to create templates!');

            return;
        }

        $highPriorityId = TaskPriority::query()->where('name', 'High')->value('id');
        $mediumPriorityId = TaskPriority::query()->where('name', 'Medium')->value('id');
        $lowPriorityId = TaskPriority::query()->where('name', 'Low')->value('id');

        $templateDefinitions = [
            [
                'name' => 'Full-Stack Web Application',
                'description' => 'A delivery template for building, testing, and launching a web application with frontend, backend, and deployment work.',
                'is_active' => true,
                'phases' => [
                    [
                        'name' => 'Planning & Discovery',
                        'description' => 'Clarify scope, stakeholders, and delivery expectations before design begins.',
                        'tasks' => [
                            ['title' => 'Define scope and success criteria', 'description' => 'Document project goals, core features, and acceptance criteria.', 'estimated_hours' => 4, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Identify users and key stakeholders', 'description' => 'List primary users, approvers, and communication channels.', 'estimated_hours' => 2, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Prepare milestone timeline', 'description' => 'Break the work into major checkpoints and target dates.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Design',
                        'description' => 'Prepare the technical and UI design decisions needed for implementation.',
                        'tasks' => [
                            ['title' => 'Design application architecture', 'description' => 'Define major modules, data flow, and integration points.', 'estimated_hours' => 6, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Design database schema', 'description' => 'Model core entities, relationships, and constraints.', 'estimated_hours' => 5, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Create UI wireframes', 'description' => 'Draft the main pages, navigation, and interaction flow.', 'estimated_hours' => 6, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Implementation',
                        'description' => 'Build the application features across frontend, backend, and integration layers.',
                        'tasks' => [
                            ['title' => 'Set up project structure and environment', 'description' => 'Prepare the base application, dependencies, and environment configuration.', 'estimated_hours' => 4, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Build frontend screens and interactions', 'description' => 'Implement the main pages, forms, validation, and UI flows.', 'estimated_hours' => 16, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Develop backend APIs and business logic', 'description' => 'Implement controllers, services, validation, and domain rules.', 'estimated_hours' => 18, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Integrate frontend with backend', 'description' => 'Connect UI actions to backend endpoints and persistence flows.', 'estimated_hours' => 8, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Testing & QA',
                        'description' => 'Validate the application behavior and resolve issues before release.',
                        'tasks' => [
                            ['title' => 'Prepare test scenarios', 'description' => 'List critical flows, edge cases, and expected outcomes.', 'estimated_hours' => 4, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Run functional and regression tests', 'description' => 'Verify that the application works end to end and does not regress.', 'estimated_hours' => 8, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Fix defects from QA review', 'description' => 'Address confirmed issues and retest the affected flows.', 'estimated_hours' => 10, 'task_priority_id' => $highPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Deployment & Handover',
                        'description' => 'Release the application and complete the final project handover activities.',
                        'tasks' => [
                            ['title' => 'Prepare deployment checklist', 'description' => 'Confirm configs, backups, release notes, and rollback steps.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Deploy application and verify production environment', 'description' => 'Release the application and confirm the live environment is working correctly.', 'estimated_hours' => 5, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Complete project handover documentation', 'description' => 'Provide usage notes, support details, and technical handover materials.', 'estimated_hours' => 4, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                ],
            ],
        ];

        DB::transaction(function () use ($systemAdmin, $templateDefinitions): void {
            foreach ($templateDefinitions as $templateDefinition) {
                $template = ProjectTemplate::query()->updateOrCreate(
                    ['name' => $templateDefinition['name']],
                    [
                        'description' => $templateDefinition['description'],
                        'is_active' => $templateDefinition['is_active'],
                        'created_by' => $systemAdmin->id,
                        'updated_by' => null,
                    ]
                );

                TemplateTask::query()
                    ->whereIn('template_phase_id', $template->phases()->pluck('id'))
                    ->delete();

                TemplatePhase::query()
                    ->where('project_template_id', $template->id)
                    ->delete();

                foreach ($templateDefinition['phases'] as $phaseIndex => $phaseDefinition) {
                    $phase = TemplatePhase::query()->create([
                        'project_template_id' => $template->id,
                        'name' => $phaseDefinition['name'],
                        'description' => $phaseDefinition['description'],
                        'sort_order' => $phaseIndex + 1,
                    ]);

                    foreach ($phaseDefinition['tasks'] as $taskIndex => $taskDefinition) {
                        TemplateTask::query()->create([
                            'template_phase_id' => $phase->id,
                            'task_priority_id' => $taskDefinition['task_priority_id'],
                            'title' => $taskDefinition['title'],
                            'description' => $taskDefinition['description'],
                            'sort_order' => $taskIndex + 1,
                            'estimated_hours' => $taskDefinition['estimated_hours'],
                        ]);
                    }
                }
            }
        });

        $this->command->info('✅ Project templates seeded successfully!');
    }
}
