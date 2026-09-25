<?php

namespace App\Services;

use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;

class ProjectProgressService
{
    /**
     * Calculate overall project progress
     */
    public function calculateProjectProgress(Project $project): float
    {
        $phases = $project->phases()->with('tasks')->get();

        if ($phases->isEmpty()) {
            return 0;
        }

        $totalWeight = 0;
        $weightedProgress = 0;

        foreach ($phases as $phase) {
            $phaseProgress = $this->calculatePhaseProgress($phase);
            $phaseWeight = $phase->tasks->count() > 0 ? $phase->tasks->count() : 1;

            $totalWeight += $phaseWeight;
            $weightedProgress += ($phaseProgress * $phaseWeight);
        }

        return $totalWeight > 0 ? round($weightedProgress / $totalWeight, 2) : 0;
    }

    /**
     * Calculate phase progress
     */
    public function calculatePhaseProgress(Phase $phase): float
    {
        $tasks = $phase->tasks;

        if ($tasks->isEmpty()) {
            return 0;
        }

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->filter(function ($task) {
            return $task->status && in_array($task->status->name, ['Done', 'Completed']);
        })->count();

        return $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;
    }

    /**
     * Get project timeline data
     */
    public function getTimelineData(Project $project): array
    {
        $phases = $project->phases()->with(['tasks.assignedTo', 'tasks.status'])->orderBy('sort_order')->get();

        $timeline = [];
        $startDate = $project->start_date ?? now();
        $currentDate = now();

        foreach ($phases as $phase) {
            $phaseStart = clone $startDate;
            $phaseEnd = clone $phaseStart;

            // Calculate estimated duration based on tasks
            $totalHours = $phase->tasks->sum('estimated_hours') ?? 0;
            $daysToAdd = $totalHours > 0 ? ceil($totalHours / 8) : 5; // 8 hours per day default

            $phaseEnd->addDays($daysToAdd);

            $timeline[] = [
                'phase' => $phase->name,
                'start_date' => $phaseStart->toDateString(),
                'end_date' => $phaseEnd->toDateString(),
                'progress' => $this->calculatePhaseProgress($phase),
                'tasks' => $phase->tasks->map(function ($task) {
                    return [
                        'title' => $task->title,
                        'assigned_to' => $task->assignedTo?->name ?? 'Unassigned',
                        'status' => $task->status?->name ?? 'Unknown',
                        'priority' => $task->priority?->name ?? 'Normal',
                        'estimated_hours' => $task->estimated_hours,
                    ];
                })->toArray(),
            ];

            // Set next phase start date
            $startDate = clone $phaseEnd;
            $startDate->addDay(1);
        }

        return $timeline;
    }

    /**
     * Update all project progress percentages
     */
    public function updateAllProjectProgress(): void
    {
        Project::with('phases.tasks')->chunk(50, function ($projects) {
            foreach ($projects as $project) {
                $progress = $this->calculateProjectProgress($project);
                $project->update(['progress_percentage' => $progress]);
            }
        });
    }

    /**
     * Get project health status
     */
    public function getProjectHealth(Project $project): array
    {
        $progress = $this->calculateProjectProgress($project);

        // Check for overdue tasks
        $overdueTasks = Task::whereHas('phase', function ($q) use ($project) {
            $q->where('project_id', $project->id);
        })
            ->where('due_date', '<', now())
            ->whereDoesntHave('status', function ($q) {
                $q->whereIn('name', ['Done', 'Completed']);
            })
            ->count();

        // Check for unassigned tasks
        $unassignedTasks = Task::whereHas('phase', function ($q) use ($project) {
            $q->where('project_id', $project->id);
        })
            ->whereNull('assigned_to')
            ->count();

        // Determine health status
        if ($overdueTasks > 0) {
            $health = 'critical';
            $message = "{$overdueTasks} overdue tasks need immediate attention.";
        } elseif ($unassignedTasks > 0) {
            $health = 'warning';
            $message = "{$unassignedTasks} tasks are unassigned.";
        } elseif ($progress >= 90) {
            $health = 'good';
            $message = 'Project is on track for completion.';
        } elseif ($progress >= 50) {
            $health = 'good';
            $message = 'Project is progressing well.';
        } else {
            $health = 'attention';
            $message = 'Project progress is below 50%. Consider accelerating work.';
        }

        return [
            'health' => $health,
            'message' => $message,
            'progress' => $progress,
            'overdue_tasks' => $overdueTasks,
            'unassigned_tasks' => $unassignedTasks,
            'total_tasks' => Task::whereHas('phase', function ($q) use ($project) {
                $q->where('project_id', $project->id);
            })->count(),
        ];
    }
}
