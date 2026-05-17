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
        foreach ([
            ['name' => 'Low', 'level_order' => 1],
            ['name' => 'Medium', 'level_order' => 2],
            ['name' => 'High', 'level_order' => 3],
            ['name' => 'Critical', 'level_order' => 4],
        ] as $priority) {
            TaskPriority::query()->updateOrCreate(
                ['name' => $priority['name']],
                ['level_order' => $priority['level_order']]
            );
        }

        foreach ([
            ['name' => 'Not Started', 'description' => 'Work has not started'],
            ['name' => 'In Progress', 'description' => 'Work is underway'],
            ['name' => 'Completed', 'description' => 'Work is finished'],
            ['name' => 'Blocked', 'description' => 'Work is blocked'],
        ] as $status) {
            PhaseStatus::query()->updateOrCreate(
                ['name' => $status['name']],
                ['description' => $status['description']]
            );
        }

        foreach ([
            ['name' => 'Not Started', 'description' => 'Work has not started'],
            ['name' => 'In Progress', 'description' => 'Work is underway'],
            ['name' => 'Under Review', 'description' => 'Work is under review'],
            ['name' => 'Completed', 'description' => 'Work is finished'],
            ['name' => 'Blocked', 'description' => 'Work is blocked'],
            ['name' => 'Cancelled', 'description' => 'Work is cancelled'],
        ] as $status) {
            TaskStatus::query()->updateOrCreate(
                ['name' => $status['name']],
                ['description' => $status['description']]
            );
        }
    }
}
