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
        $systemAdmin = User::query()->where('email', 'test@example.com')->first();

        if (! $systemAdmin) {
            return;
        }

        $highPriorityId = TaskPriority::query()->where('name', 'High')->value('id');
        $mediumPriorityId = TaskPriority::query()->where('name', 'Medium')->value('id');

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
            [
                'name' => 'Mobile Application',
                'description' => 'A delivery template for planning, designing, building, testing, and releasing a mobile app with backend integration.',
                'is_active' => true,
                'phases' => [
                    [
                        'name' => 'Discovery & Planning',
                        'description' => 'Define the mobile product vision, audience, and release scope.',
                        'tasks' => [
                            ['title' => 'Define product requirements and success metrics', 'description' => 'Document the main use cases, target audience, and measurable outcomes.', 'estimated_hours' => 4, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Prioritize core mobile features', 'description' => 'Choose the first-release features and exclude non-essential scope.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Prepare delivery timeline and risks', 'description' => 'Outline milestones, dependencies, and mobile-specific delivery risks.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'UI/UX Design',
                        'description' => 'Design the mobile experience, navigation, and interface behavior.',
                        'tasks' => [
                            ['title' => 'Design mobile user flows', 'description' => 'Map the core app journeys and navigation between screens.', 'estimated_hours' => 5, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Create mobile screen mockups', 'description' => 'Produce screen-level designs for onboarding, dashboard, and key actions.', 'estimated_hours' => 8, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Define design system components', 'description' => 'Prepare reusable mobile UI components, typography, and spacing rules.', 'estimated_hours' => 5, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Mobile Development',
                        'description' => 'Implement the mobile client, local state, and device-facing features.',
                        'tasks' => [
                            ['title' => 'Set up mobile project structure', 'description' => 'Configure the mobile app foundation, packages, environments, and navigation shell.', 'estimated_hours' => 4, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Build mobile screens and navigation flows', 'description' => 'Implement the designed screens, forms, and navigation behavior.', 'estimated_hours' => 16, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Implement device capabilities and local storage', 'description' => 'Support notifications, permissions, offline storage, or other required device features.', 'estimated_hours' => 10, 'task_priority_id' => $highPriorityId],
                        ],
                    ],
                    [
                        'name' => 'API Integration',
                        'description' => 'Connect the mobile client to backend services and secure data flows.',
                        'tasks' => [
                            ['title' => 'Develop backend endpoints for the mobile app', 'description' => 'Provide the APIs, validation, and response contracts needed by the app.', 'estimated_hours' => 12, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Integrate mobile app with backend services', 'description' => 'Wire authentication, data sync, and business flows to the backend APIs.', 'estimated_hours' => 10, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Handle mobile error states and loading flows', 'description' => 'Implement resilient network handling, retries, and user feedback states.', 'estimated_hours' => 5, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Testing & Release',
                        'description' => 'Validate the mobile app and prepare distribution to end users.',
                        'tasks' => [
                            ['title' => 'Run device and usability testing', 'description' => 'Test the app on representative devices and validate user experience flows.', 'estimated_hours' => 8, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Fix release-blocking bugs', 'description' => 'Resolve confirmed issues that affect stability, performance, or app store approval.', 'estimated_hours' => 10, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Prepare store release assets and submission checklist', 'description' => 'Assemble screenshots, descriptions, policies, and final submission requirements.', 'estimated_hours' => 4, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Desktop Application',
                'description' => 'A delivery template for building and releasing a desktop application with installation, local persistence, and support flows.',
                'is_active' => true,
                'phases' => [
                    [
                        'name' => 'Requirements & Scope',
                        'description' => 'Clarify business goals, desktop environment constraints, and release scope.',
                        'tasks' => [
                            ['title' => 'Gather desktop workflow requirements', 'description' => 'Document the main user workflows, constraints, and productivity needs.', 'estimated_hours' => 4, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Define supported operating environments', 'description' => 'Identify target operating systems, versions, and distribution needs.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Prepare scope and release milestones', 'description' => 'Break the work into deliverable milestones for the desktop release.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Desktop UX & Architecture',
                        'description' => 'Define the app structure, window flows, and local data strategy.',
                        'tasks' => [
                            ['title' => 'Design desktop navigation and window flows', 'description' => 'Map menus, dialogs, and screen transitions for desktop users.', 'estimated_hours' => 5, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Design local storage and sync strategy', 'description' => 'Define how the app stores data locally and synchronizes where needed.', 'estimated_hours' => 5, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Prepare installation and update approach', 'description' => 'Choose packaging, update delivery, and installation constraints.', 'estimated_hours' => 4, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Desktop Development',
                        'description' => 'Implement the core desktop experience, local persistence, and integrations.',
                        'tasks' => [
                            ['title' => 'Set up desktop application shell', 'description' => 'Prepare the project structure, windows, menus, and environment configuration.', 'estimated_hours' => 4, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Implement desktop workflows and local persistence', 'description' => 'Build the key screens, local storage, and user productivity flows.', 'estimated_hours' => 14, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Integrate external services or hardware dependencies', 'description' => 'Wire APIs, local devices, printers, or file system integrations as needed.', 'estimated_hours' => 10, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Quality Assurance',
                        'description' => 'Validate app stability, packaging, and installation behavior.',
                        'tasks' => [
                            ['title' => 'Test desktop installation and upgrade paths', 'description' => 'Verify setup, upgrade, uninstall, and rollback behaviors.', 'estimated_hours' => 5, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Run functional and compatibility tests', 'description' => 'Validate application behavior across target operating environments.', 'estimated_hours' => 8, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Fix high-priority release issues', 'description' => 'Resolve blocking bugs affecting stability or packaging.', 'estimated_hours' => 8, 'task_priority_id' => $highPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Packaging & Release',
                        'description' => 'Finalize packaging, documentation, and deployment to users.',
                        'tasks' => [
                            ['title' => 'Create release package and installer assets', 'description' => 'Build distributables, icons, signing artifacts, and setup resources.', 'estimated_hours' => 4, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Publish release and user documentation', 'description' => 'Deliver the package, user guide, and release notes.', 'estimated_hours' => 4, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Prepare support handover and maintenance notes', 'description' => 'Document operational support needs and post-release follow-up items.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Data Analytics Dashboard',
                'description' => 'A delivery template for building a reporting or analytics dashboard with data ingestion, modeling, and visualization work.',
                'is_active' => true,
                'phases' => [
                    [
                        'name' => 'Requirements & KPI Definition',
                        'description' => 'Identify reporting goals, stakeholders, and dashboard success metrics.',
                        'tasks' => [
                            ['title' => 'Define business questions and KPIs', 'description' => 'Capture the decisions the dashboard must support and the metrics it should expose.', 'estimated_hours' => 4, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Identify data sources and owners', 'description' => 'List source systems, contacts, and data access constraints.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Prioritize dashboard views', 'description' => 'Agree on the initial reports, filters, and drilldowns for the first release.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Data Modeling & Preparation',
                        'description' => 'Prepare the dataset structure and transformation logic for the dashboard.',
                        'tasks' => [
                            ['title' => 'Map source fields to reporting model', 'description' => 'Define dimensions, measures, and joins required for analytics.', 'estimated_hours' => 5, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Clean and transform source data', 'description' => 'Implement the transformation logic needed to standardize and enrich the dataset.', 'estimated_hours' => 8, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Validate data quality and refresh rules', 'description' => 'Check data completeness, calculation integrity, and update frequency.', 'estimated_hours' => 5, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Dashboard Development',
                        'description' => 'Build the dashboard interface, charts, and filtering interactions.',
                        'tasks' => [
                            ['title' => 'Design dashboard layout and navigation', 'description' => 'Prepare the structure for overview and detailed analysis views.', 'estimated_hours' => 5, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Implement charts, tables, and filters', 'description' => 'Build the visualizations and data exploration controls.', 'estimated_hours' => 12, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Optimize query and rendering performance', 'description' => 'Reduce slow data loads and improve dashboard responsiveness.', 'estimated_hours' => 6, 'task_priority_id' => $highPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Validation & UAT',
                        'description' => 'Confirm reporting accuracy and user acceptance before release.',
                        'tasks' => [
                            ['title' => 'Reconcile dashboard metrics with source reports', 'description' => 'Verify the dashboard outputs match trusted baseline numbers.', 'estimated_hours' => 6, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Run stakeholder review sessions', 'description' => 'Validate the dashboard with business users and collect feedback.', 'estimated_hours' => 4, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Address acceptance and data issues', 'description' => 'Fix confirmed issues from review and retest the corrected views.', 'estimated_hours' => 6, 'task_priority_id' => $highPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Release & Enablement',
                        'description' => 'Publish the dashboard and prepare users to adopt it.',
                        'tasks' => [
                            ['title' => 'Deploy dashboard to production workspace', 'description' => 'Publish the final dashboard and configure access controls.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Prepare usage guide and KPI definitions', 'description' => 'Document how to use the dashboard and interpret its metrics.', 'estimated_hours' => 4, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Train end users and owners', 'description' => 'Provide walkthroughs for business users and the support team.', 'estimated_hours' => 4, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'E-Commerce Platform',
                'description' => 'A delivery template for launching an online store with catalog, checkout, payments, and operational flows.',
                'is_active' => true,
                'phases' => [
                    [
                        'name' => 'Business Setup & Scope',
                        'description' => 'Define the storefront goals, target customers, and commercial requirements.',
                        'tasks' => [
                            ['title' => 'Define product, pricing, and customer goals', 'description' => 'Capture the catalog scope, target market, and conversion goals.', 'estimated_hours' => 4, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Identify fulfillment and support workflows', 'description' => 'Document order handling, delivery, returns, and customer support needs.', 'estimated_hours' => 4, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Prepare launch milestone plan', 'description' => 'Break the storefront release into delivery checkpoints.', 'estimated_hours' => 3, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Storefront Design',
                        'description' => 'Design the shopping experience, browsing flows, and conversion points.',
                        'tasks' => [
                            ['title' => 'Design catalog and product detail pages', 'description' => 'Prepare listing, search, and product page experiences.', 'estimated_hours' => 6, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Design cart and checkout experience', 'description' => 'Create the checkout flow with clear trust and conversion cues.', 'estimated_hours' => 6, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Define promotion and content blocks', 'description' => 'Plan homepage sections, banners, and promotional placements.', 'estimated_hours' => 4, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Catalog & Storefront Development',
                        'description' => 'Implement the customer-facing storefront and catalog capabilities.',
                        'tasks' => [
                            ['title' => 'Implement product catalog and search', 'description' => 'Build browsing, filtering, and product detail views.', 'estimated_hours' => 12, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Build customer account and cart flows', 'description' => 'Implement authentication, wishlist/cart, and order tracking basics.', 'estimated_hours' => 10, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Configure promotions and content management', 'description' => 'Set up discounts, banners, and editable promotional sections.', 'estimated_hours' => 6, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Checkout, Payments & Operations',
                        'description' => 'Implement the commercial backbone for orders, payments, and fulfillment.',
                        'tasks' => [
                            ['title' => 'Implement checkout and payment workflows', 'description' => 'Build address capture, shipping methods, payment integration, and order confirmation.', 'estimated_hours' => 12, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Set up order management and fulfillment flow', 'description' => 'Support order statuses, packing, shipping, and return handling.', 'estimated_hours' => 8, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Configure notifications and transactional messages', 'description' => 'Prepare order, shipping, and support communication messages.', 'estimated_hours' => 5, 'task_priority_id' => $mediumPriorityId],
                        ],
                    ],
                    [
                        'name' => 'Launch & Optimization',
                        'description' => 'Validate the store and prepare it for live customers.',
                        'tasks' => [
                            ['title' => 'Run end-to-end purchase testing', 'description' => 'Validate the storefront through checkout, payment, and post-purchase flows.', 'estimated_hours' => 6, 'task_priority_id' => $highPriorityId],
                            ['title' => 'Prepare analytics and conversion tracking', 'description' => 'Set up monitoring for traffic, funnel conversion, and sales events.', 'estimated_hours' => 5, 'task_priority_id' => $mediumPriorityId],
                            ['title' => 'Go live and monitor launch stability', 'description' => 'Release the store and watch for payment, inventory, or fulfillment issues.', 'estimated_hours' => 6, 'task_priority_id' => $highPriorityId],
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
    }
}
