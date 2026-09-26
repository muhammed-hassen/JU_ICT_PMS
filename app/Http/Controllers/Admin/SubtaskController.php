<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subtask;
use App\Models\Task;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * A task's checklist. Anyone who can edit the task manages the list; the
 * assignee can also tick items off, since doing the work is their job.
 */
class SubtaskController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function store(Request $request, Task $task): RedirectResponse
    {
        $this->authorizeTask($task, manage: true);

        $validated = $request->validate([
            'title' => 'required|string|max:200',
        ]);

        $subtask = $task->subtasks()->create([
            'title' => $validated['title'],
            'sort_order' => ($task->subtasks()->max('sort_order') ?? 0) + 1,
            'created_by' => $request->user()->id,
        ]);

        $this->activityLog->log($task, 'updated', "Subtask added: {$subtask->title}", ['subtask_id' => $subtask->id]);

        return back()->with('success', 'Subtask added.');
    }

    public function toggle(Request $request, Subtask $subtask): RedirectResponse
    {
        $this->authorizeTask($subtask->task, manage: false);

        $done = ! $subtask->is_done;
        $subtask->update([
            'is_done' => $done,
            'completed_at' => $done ? now() : null,
            'completed_by' => $done ? $request->user()->id : null,
        ]);

        $this->activityLog->log(
            $subtask->task,
            'updated',
            ($done ? 'Subtask completed: ' : 'Subtask reopened: ').$subtask->title,
            ['subtask_id' => $subtask->id]
        );

        return back();
    }

    public function destroy(Subtask $subtask): RedirectResponse
    {
        $this->authorizeTask($subtask->task, manage: true);

        $task = $subtask->task;
        $title = $subtask->title;
        $subtask->delete();

        $this->activityLog->log($task, 'updated', "Subtask removed: {$title}");

        return back()->with('success', 'Subtask removed.');
    }

    private function authorizeTask(Task $task, bool $manage): void
    {
        $user = auth()->user();

        // Same visibility rule as the task page: no touching tasks you can't see.
        if (! $user->isDirector() && ! in_array($task->id, $user->getVisibleTaskIds())) {
            abort(403, 'You do not have permission to view this task.');
        }

        if ($user->can('edit-task')) {
            return;
        }

        if (! $manage && $task->isAssignedTo($user) && $user->hasAnyPermission(['edit-own-task', 'complete-task', 'update-task-progress'])) {
            return;
        }

        abort(403, 'You do not have permission to change this task\'s checklist.');
    }
}
