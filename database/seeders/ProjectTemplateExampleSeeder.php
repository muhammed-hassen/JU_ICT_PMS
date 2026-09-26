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
            $this->command?->error('No user found to create templates!');

            return;
        }

        $highPriorityId = TaskPriority::query()->where('name', 'High')->value('id');
        $mediumPriorityId = TaskPriority::query()->where('name', 'Medium')->value('id');
        $lowPriorityId = TaskPriority::query()->where('name', 'Low')->value('id');

        $templateDefinitions = [
            [
                // The five lifecycle phases named in the SRS (section 2.2 and 5.5).
                'name' => 'Standard ICT Project Lifecycle',
                'description' => 'The directorate lifecycle: Initiation, Planning, Execution, Monitoring and Closure. Fits any ICT project.',
                'is_active' => true,
                'phases' => [
                    [
                        'name' => 'Initiation',
                        'description' => 'Agree why the project exists and who it serves.',
                        'tasks' => [
                            ['title' => 'Write the project charter', 'description' => 'State the goal, objectives, sponsor and rough budget.', 'estimated_hours' => 4, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Identify stakeholders', 'description' => 'List who is affected, who approves and how to reach them.', 'estimated_hours' => 2, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Planning',
                        'description' => 'Turn the charter into a schedule, budget and resource plan.',
                        'tasks' => [
                            ['title' => 'Define scope and deliverables', 'description' => 'Write down what will be delivered and what is out of scope.', 'estimated_hours' => 4, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Prepare schedule and milestones', 'description' => 'Set target dates for each phase and its milestones.', 'estimated_hours' => 3, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Estimate budget and resources', 'description' => 'Estimate cost, people and equipment per task.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Execution',
                        'description' => 'Do the work the plan describes.',
                        'tasks' => [
                            ['title' => 'Set up the environment', 'description' => 'Prepare servers, accounts and tools the team needs.', 'estimated_hours' => 6, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Build or configure the solution', 'description' => 'Carry out the main technical work.', 'estimated_hours' => 24, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Train users', 'description' => 'Show the people who will use the result how it works.', 'estimated_hours' => 4, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Monitoring',
                        'description' => 'Check progress, cost and quality against the plan.',
                        'tasks' => [
                            ['title' => 'Review progress against the schedule', 'description' => 'Compare planned and actual dates and act on slippage.', 'estimated_hours' => 2, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Test and accept deliverables', 'description' => 'Confirm each deliverable meets its acceptance criteria.', 'estimated_hours' => 6, 'task_priority_id' => $highPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Closure',
                        'description' => 'Hand over, record lessons and close the project.',
                        'tasks' => [
                            ['title' => 'Hand over to operations', 'description' => 'Pass documentation and support duties to the owning team.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Write the closure report', 'description' => 'Summarise results, final cost and lessons learned.', 'estimated_hours' => 3, 'task_priority_id' => $lowPriorityId],
                        ],
                    ],
                ],
            ],
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
            [
                'name' => 'Mobile Application',
                'description' => 'A delivery template for designing, building and releasing a mobile app for Android and iOS.',
                'is_active' => true,
                'phases' => [
                    [
                        'name' => 'Discovery & Planning',
                        'description' => 'Agree what the app must do, for whom, and how success is measured.',
                        'tasks' => [
                            ['title' => 'Define product requirements and success metrics', 'description' => 'List the core user journeys, target devices and measurable goals.', 'estimated_hours' => 5, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Choose platforms and technology', 'description' => 'Decide on native or cross-platform tooling and minimum OS versions.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Plan release milestones', 'description' => 'Set dates for design sign-off, beta and store release.', 'estimated_hours' => 2, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'UX & Interface Design',
                        'description' => 'Design the screens and flows before development starts.',
                        'tasks' => [
                            ['title' => 'Design user flows and wireframes', 'description' => 'Sketch every main screen and how users move between them.', 'estimated_hours' => 8, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Produce the visual design', 'description' => 'Apply branding, typography and components to the wireframes.', 'estimated_hours' => 8, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Mobile Development',
                        'description' => 'Build the app screens and device features.',
                        'tasks' => [
                            ['title' => 'Build mobile screens and navigation flows', 'description' => 'Implement the designed screens, navigation and form validation.', 'estimated_hours' => 20, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Implement device capabilities and local storage', 'description' => 'Add camera, location, notifications and offline storage as required.', 'estimated_hours' => 12, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Backend Integration',
                        'description' => 'Connect the app to the server and handle authentication.',
                        'tasks' => [
                            ['title' => 'Integrate the app with backend APIs', 'description' => 'Connect screens to the server endpoints and handle errors and loading states.', 'estimated_hours' => 12, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Implement sign-in and session handling', 'description' => 'Add login, token storage and logout.', 'estimated_hours' => 6, 'task_priority_id' => $highPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Testing & Release',
                        'description' => 'Test on real devices and publish to the app stores.',
                        'tasks' => [
                            ['title' => 'Test on target devices', 'description' => 'Run the main flows on a range of phones and screen sizes.', 'estimated_hours' => 8, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Prepare store release assets and submission checklist', 'description' => 'Prepare icons, screenshots, descriptions and store listings.', 'estimated_hours' => 4, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Desktop Application',
                'description' => 'A delivery template for an installable desktop application used inside the university.',
                'is_active' => true,
                'phases' => [
                    [
                        'name' => 'Requirements & Planning',
                        'description' => 'Agree the workflows the application must support.',
                        'tasks' => [
                            ['title' => 'Gather workflow requirements from users', 'description' => 'Interview the staff who will use the application.', 'estimated_hours' => 6, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Define supported operating systems', 'description' => 'List target Windows, macOS or Linux versions.', 'estimated_hours' => 2, 'task_priority_id' => $lowPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Desktop Development',
                        'description' => 'Build the application windows, workflows and storage.',
                        'tasks' => [
                            ['title' => 'Build the main application windows', 'description' => 'Implement the main window, menus and dialogs.', 'estimated_hours' => 16, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Implement desktop workflows and local persistence', 'description' => 'Implement the core workflows and save data locally.', 'estimated_hours' => 16, 'task_priority_id' => $highPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Packaging & Deployment',
                        'description' => 'Package the application and roll it out to user machines.',
                        'tasks' => [
                            ['title' => 'Create the installer package', 'description' => 'Build a signed installer for each supported platform.', 'estimated_hours' => 4, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Roll out to user machines', 'description' => 'Install on staff machines and confirm it runs.', 'estimated_hours' => 6, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Data Analytics Dashboard',
                'description' => 'A delivery template for turning existing data into a reporting dashboard.',
                'is_active' => true,
                'phases' => [
                    [
                        'name' => 'Requirements & Data Sources',
                        'description' => 'Agree the questions the dashboard answers and where the data lives.',
                        'tasks' => [
                            ['title' => 'Define the key questions and metrics', 'description' => 'List the decisions the dashboard must support.', 'estimated_hours' => 4, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Identify and get access to data sources', 'description' => 'Find the databases and files holding the data and request access.', 'estimated_hours' => 4, 'task_priority_id' => $highPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Data Modeling & Preparation',
                        'description' => 'Clean the data and shape it for reporting.',
                        'tasks' => [
                            ['title' => 'Clean and transform source data', 'description' => 'Remove duplicates, fix formats and join the sources.', 'estimated_hours' => 12, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Build the reporting data model', 'description' => 'Create the tables or views the dashboard will query.', 'estimated_hours' => 8, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Dashboard Build & Review',
                        'description' => 'Build the charts and review them with the people who will use them.',
                        'tasks' => [
                            ['title' => 'Build dashboard charts and filters', 'description' => 'Create the visualisations and the filters users need.', 'estimated_hours' => 10, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Review figures with stakeholders', 'description' => 'Check the numbers against known totals with the data owners.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'E-Commerce Platform',
                'description' => 'A delivery template for an online store with a catalog, checkout and order handling.',
                'is_active' => true,
                'phases' => [
                    [
                        'name' => 'Planning & Catalog Design',
                        'description' => 'Agree what is sold and how the catalog is organised.',
                        'tasks' => [
                            ['title' => 'Define product catalog structure', 'description' => 'Decide categories, product attributes and pricing rules.', 'estimated_hours' => 5, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Design storefront pages', 'description' => 'Wireframe the home, listing, product and cart pages.', 'estimated_hours' => 8, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Storefront Development',
                        'description' => 'Build the customer-facing store.',
                        'tasks' => [
                            ['title' => 'Build product listing and detail pages', 'description' => 'Implement browsing, search and product pages.', 'estimated_hours' => 14, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Build the shopping cart', 'description' => 'Add, update and remove items and show totals.', 'estimated_hours' => 8, 'task_priority_id' => $highPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Checkout, Payments & Operations',
                        'description' => 'Take payment and handle orders after purchase.',
                        'tasks' => [
                            ['title' => 'Implement checkout and payment workflows', 'description' => 'Collect delivery details, take payment and confirm the order.', 'estimated_hours' => 14, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Build order management for staff', 'description' => 'Let staff view, fulfil and refund orders.', 'estimated_hours' => 10, 'task_priority_id' => $mediumPriorityId],
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

        $this->command?->info('✅ Project templates seeded successfully!');
    }
}
