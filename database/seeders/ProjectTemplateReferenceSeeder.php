<?php

namespace Database\Seeders;

use App\Models\PhaseStatus;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use Illuminate\Database\Seeder;

class ProjectTemplateReferenceSeeder extends Seeder
{
    public function run(): void
    {
        // ===== TASK PRIORITIES =====
        $priorities = [
            ['name' => 'Low', 'level_order' => 1],
            ['name' => 'Medium', 'level_order' => 2],
            ['name' => 'High', 'level_order' => 3],
            ['name' => 'Critical', 'level_order' => 4],
        ];

        foreach ($priorities as $priority) {
            TaskPriority::query()->updateOrCreate(
                ['name' => $priority['name']],
                ['level_order' => $priority['level_order']]
            );
        }

        // ===== PHASE STATUSES =====
        $phaseStatuses = [
            ['name' => 'Not Started', 'description' => 'Phase has not started yet'],
            ['name' => 'In Progress', 'description' => 'Phase is currently in progress'],
            ['name' => 'Under Review', 'description' => 'Phase is under review'],
            ['name' => 'Completed', 'description' => 'Phase has been completed'],
            ['name' => 'Blocked', 'description' => 'Phase is blocked'],
            ['name' => 'Cancelled', 'description' => 'Phase has been cancelled'],
        ];

        foreach ($phaseStatuses as $status) {
            PhaseStatus::query()->updateOrCreate(
                ['name' => $status['name']],
                ['description' => $status['description']]
            );
        }

        // ===== TASK STATUSES =====
        $taskStatuses = [
            ['name' => 'Not Started', 'description' => 'Task has not started yet'],
            ['name' => 'In Progress', 'description' => 'Task is currently in progress'],
            ['name' => 'Under Review', 'description' => 'Task is under review'],
            ['name' => 'Completed', 'description' => 'Task has been completed'],
            ['name' => 'Done', 'description' => 'Task is done'],
            ['name' => 'Blocked', 'description' => 'Task is blocked'],
            ['name' => 'Cancelled', 'description' => 'Task has been cancelled'],
        ];

        foreach ($taskStatuses as $status) {
            TaskStatus::query()->updateOrCreate(
                ['name' => $status['name']],
                ['description' => $status['description']]
            );
        }

        $this->command->info('✅ Reference data seeded:');
        $this->command->info('   - '.TaskPriority::count().' priorities');
        $this->command->info('   - '.PhaseStatus::count().' phase statuses');
        $this->command->info('   - '.TaskStatus::count().' task statuses');
    }
}
