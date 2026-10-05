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

        // Groups of status names that should appear as one merged column.
        // The first name in each group is the canonical drop-target (used when
        // dragging a card in, and shown as the column's data-status).
        $mergeGroups = [
            'Completed' => ['Completed', 'Done'],
            'Blocked'   => ['Blocked', 'Cancelled'],
        ];

        // Build a flat map: statusName → canonical column key
        $canonicalMap = [];
        foreach ($mergeGroups as $canonical => $members) {
            foreach ($members as $member) {
                $canonicalMap[$member] = $canonical;
            }
        }

        // Column labels for merged groups
        $mergedLabels = [
            'Completed' => 'Completed / Done',
            'Blocked'   => 'Blocked / Cancelled',
        ];

        $board = [];

        foreach ($statuses as $status) {
            $canonical = $canonicalMap[$status->name] ?? $status->name;

            // Skip statuses that are aliases — they're already handled by their canonical entry
            if ($canonical !== $status->name) {
                continue;
            }

            $label = $mergedLabels[$canonical] ?? $canonical;

            if (! isset($board[$label])) {
                $board[$label] = [
                    'status'         => $status,       // canonical TaskStatus object
                    'canonical_name' => $canonical,    // real DB status name for drop target
                    'tasks'          => collect(),
                    'count'          => 0,
                ];
            }
        }

        // Distribute every task into its column (merging aliases into canonical)
        foreach ($tasks as $task) {
            $taskStatusName = $task->status->name ?? '';
            $canonical      = $canonicalMap[$taskStatusName] ?? $taskStatusName;
            $label          = $mergedLabels[$canonical] ?? $canonical;

            if (isset($board[$label])) {
                $board[$label]['tasks']->push($task);
                $board[$label]['count']++;
            } else {
                // Status exists in DB but wasn't in our ordered list — add it
                $board[$label] = [
                    'status'         => $task->status,
                    'canonical_name' => $canonical,
                    'tasks'          => collect([$task]),
                    'count'          => 1,
                ];
            }
        }

        return $board;
    }

    public function getStatusStats(Collection $tasks): array
    {
        $stats = [];
        $total = $tasks->count();

        $mergeGroups = [
            'Completed / Done'       => ['Completed', 'Done'],
            'Blocked / Cancelled'    => ['Blocked', 'Cancelled'],
        ];

        // Build reverse map: statusName → merged label
        $labelMap = [];
        foreach ($mergeGroups as $label => $members) {
            foreach ($members as $member) {
                $labelMap[$member] = $label;
            }
        }

        foreach (TaskStatus::all() as $status) {
            $label = $labelMap[$status->name] ?? $status->name;

            if (! isset($stats[$label])) {
                $stats[$label] = ['count' => 0, 'percentage' => 0, 'color' => $this->getStatusColor($status->name)];
            }

            $count = $tasks->where('task_status_id', $status->id)->count();
            $stats[$label]['count'] += $count;
            $stats[$label]['percentage'] = $total > 0
                ? round(($stats[$label]['count'] / $total) * 100, 1)
                : 0;
        }

        return $stats;
    }
}
