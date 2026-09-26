<?php

namespace App\Services;

use App\Models\Phase;
use App\Models\PhaseStatus;
use App\Models\Project;
use App\Models\ProjectTemplate;
use App\Models\Task;
use App\Models\TaskStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProjectTemplateApplier
{
    /**
     * Apply a template to a project
     */
    public function apply(int $projectId, int $templateId, int $userId): Project
    {
        $project = Project::findOrFail($projectId);
        $template = ProjectTemplate::with(['phases.tasks'])->findOrFail($templateId);

        // Ensure the tasks table has sort_order column
        if (! Schema::hasColumn('tasks', 'sort_order')) {
            // Add it temporarily or use default
            \Log::warning('tasks.sort_order column missing, using default ordering');
        }

        // Get default statuses
        $defaultPhaseStatus = PhaseStatus::first();
        if (! $defaultPhaseStatus) {
            $defaultPhaseStatus = PhaseStatus::create([
                'name' => 'Not Started',
                'description' => 'Phase has not started yet',
            ]);
        }

        $defaultTaskStatus = TaskStatus::first();
        if (! $defaultTaskStatus) {
            $defaultTaskStatus = TaskStatus::create([
                'name' => 'To Do',
                'description' => 'Task is ready to be worked on',
            ]);
        }

        DB::transaction(function () use ($project, $template, $userId, $defaultPhaseStatus, $defaultTaskStatus) {
            foreach ($template->phases as $index => $templatePhase) {
                // Create the phase
                $phase = Phase::create([
                    'project_id' => $project->id,
                    'phase_status_id' => $defaultPhaseStatus->id,
                    'name' => $templatePhase->name,
                    'description' => $templatePhase->description,
                    'sort_order' => $templatePhase->sort_order ?? ($index + 1),
                    'progress_percentage' => 0,
                    'created_by' => $userId,
                    'updated_by' => null,
                ]);

                // Create tasks for this phase
                foreach ($templatePhase->tasks as $taskIndex => $templateTask) {
                    Task::create([
                        'phase_id' => $phase->id,
                        'task_status_id' => $defaultTaskStatus->id,
                        'task_priority_id' => $templateTask->task_priority_id ?? null,
                        'assigned_to' => null,
                        'title' => $templateTask->title,
                        'description' => $templateTask->description ?? null,
                        'sort_order' => $templateTask->sort_order ?? ($taskIndex + 1),
                        'estimated_hours' => $templateTask->estimated_hours ?? null,
                        'progress_percentage' => 0,
                        'created_by' => $userId,
                        'updated_by' => null,
                    ]);
                }
            }

            // Update project status to active if it was draft
            if ($project->status === 'draft') {
                $project->update(['status' => 'active']);
            }
        });

        // Load the project with phases and tasks
        return $project->fresh(['phases.tasks']);
    }

    /**
     * Preview a template before applying
     */
    public function preview(int $templateId): array
    {
        $template = ProjectTemplate::with(['phases.tasks'])->findOrFail($templateId);

        return [
            'name' => $template->name,
            'description' => $template->description,
            'phases_count' => $template->phases->count(),
            'tasks_count' => $template->phases->sum(fn ($phase) => $phase->tasks->count()),
            'estimated_hours' => $template->phases->sum(fn ($phase) => $phase->tasks->sum('estimated_hours')),
            'phases' => $template->phases->map(function ($phase) {
                return [
                    'name' => $phase->name,
                    'tasks' => $phase->tasks->map(function ($task) {
                        return [
                            'title' => $task->title,
                            'estimated_hours' => $task->estimated_hours,
                            'priority' => $task->priority?->name ?? 'Normal',
                        ];
                    }),
                ];
            }),
        ];
    }
}
