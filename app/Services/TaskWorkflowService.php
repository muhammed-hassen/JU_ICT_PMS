<?php

// app/Services/TaskWorkflowService.php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskStatus;
use Illuminate\Support\Collection;

class TaskWorkflowService
{
    protected array $validTransitions = [
        'Not Started' => ['In Progress'],
        'In Progress' => ['Under Review', 'Blocked'],
        'Under Review' => ['Completed', 'In Progress', 'Blocked'],
        'Blocked' => ['In Progress'],
        'Completed' => ['Under Review'],
    ];

    protected array $statusColors = [
        'Not Started' => 'secondary',
        'In Progress' => 'primary',
        'Under Review' => 'warning',
        'Completed' => 'success',
        'Blocked' => 'danger',
        'Cancelled' => 'dark',
    ];

    public function getValidTransitions(string $statusName): array
    {
        return $this->validTransitions[$statusName] ?? [];
    }

    public function getStatusColor(string $statusName): string
    {
        return $this->statusColors[$statusName] ?? 'secondary';
    }

    public function canTransition(Task $task, string $toStatus): bool
    {
        return $task->canTransitionTo($toStatus);
    }

    public function transition(Task $task, string $toStatus, ?string $note = null): bool
    {
        $newStatus = TaskStatus::where('name', $toStatus)->first();
        if (! $newStatus) {
            return false;
        }

        return $task->transitionTo($newStatus, $note);
    }

    public function getStatusHistory(Task $task): Collection
    {
        return $task->statusHistory()->with(['fromStatus', 'toStatus', 'changedBy'])->get();
    }

    public function getTaskBoardData(Collection $tasks): array
    {
        $statuses = TaskStatus::orderBy('sort_order')->get();
        $board = [];

        foreach ($statuses as $status) {
            $board[$status->name] = [
                'status' => $status,
                'tasks' => $tasks->where('task_status_id', $status->id)->values(),
                'count' => $tasks->where('task_status_id', $status->id)->count(),
            ];
        }

        return $board;
    }

    public function getStatusStats(Collection $tasks): array
    {
        $stats = [];
        $total = $tasks->count();

        foreach (TaskStatus::all() as $status) {
            $count = $tasks->where('task_status_id', $status->id)->count();
            $stats[$status->name] = [
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
                'color' => $this->getStatusColor($status->name),
            ];
        }

        return $stats;
    }
}
