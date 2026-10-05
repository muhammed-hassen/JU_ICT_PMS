<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\User;
use App\Services\TaskWorkflowService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    protected TaskWorkflowService $workflowService;

    public function __construct(TaskWorkflowService $workflowService)
    {
        $this->middleware(['auth']);
        $this->middleware('permission:view-tasks')->only(['index', 'show', 'myTasks', 'kanban']);
        $this->middleware('permission:create-task')->only(['choosePhase', 'create', 'store']);
        $this->middleware('permission:edit-task')->only(['edit', 'update']);
        // Members work on their own tasks; authorizeTaskWork() checks which task.
        $this->middleware('permission:edit-task|edit-own-task|complete-task|update-task-progress')->only(['updateStatus', 'updateProgress', 'transition']);
        $this->middleware('permission:delete-task')->only(['destroy']);
        $this->middleware('permission:assign-task')->only(['assign', 'unassign', 'assignMultiple']);

        $this->workflowService = $workflowService;
    }

    // ============================================================
    // LIST VIEW
    // ============================================================

    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = Task::query()
            ->with(['phase.project', 'status', 'priority', 'assignee', 'creator', 'assignedUsers'])
            ->withCount(['subtasks', 'subtasks as subtasks_done_count' => fn ($q) => $q->where('is_done', true)]);

        $this->limitToVisibleTasks($query, $user);

        if ($request->filled('status')) {
            $query->where('task_status_id', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('task_priority_id', $request->priority);
        }

        if ($request->filled('assignee')) {
            $query->where('assigned_to', $request->assignee);
        }

        if ($request->filled('phase')) {
            $query->where('phase_id', $request->phase);
        }

        if ($request->filled('project')) {
            $query->whereHas('phase', function ($q) use ($request) {
                $q->where('project_id', $request->project);
            });
        }

        if ($request->filled('overdue') && $request->overdue == 1) {
            $query->overdue();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tasks = $query->orderBy('deadline')->paginate(15);

        $statuses = TaskStatus::orderBy('sort_order')->get();
        $priorities = TaskPriority::orderBy('level_order')->get();
        [$projects, $users] = $this->filterOptions($user);

        return view('admin.tasks.index', compact('tasks', 'statuses', 'priorities', 'users', 'projects'));
    }

    // ============================================================
    // KANBAN BOARD VIEW
    // ============================================================

    public function kanban(Request $request): View
    {
        $user = auth()->user();

        $query = Task::query()
            ->with(['phase.project', 'status', 'priority', 'assignee', 'creator'])
            ->withCount(['subtasks', 'subtasks as subtasks_done_count' => fn ($q) => $q->where('is_done', true)]);

        $this->limitToVisibleTasks($query, $user);

        if ($request->filled('project')) {
            $query->whereHas('phase', function ($q) use ($request) {
                $q->where('project_id', $request->project);
            });
        }

        if ($request->filled('assignee')) {
            $query->where('assigned_to', $request->assignee);
        }

        if ($request->filled('priority')) {
            $query->where('task_priority_id', $request->priority);
        }

        $tasks = $query->get();
        $boardData = $this->workflowService->getTaskBoardData($tasks);
        $statusStats = $this->workflowService->getStatusStats($tasks);

        [$projects, $users] = $this->filterOptions($user);
        $priorities = TaskPriority::orderBy('level_order')->get();

        return view('admin.tasks.kanban', compact(
            'boardData',
            'statusStats',
            'projects',
            'users',
            'priorities'
        ));
    }

    // ============================================================
    // MY TASKS
    // ============================================================

    public function myTasks(Request $request): View
    {
        $user = auth()->user();

        $query = Task::query()
            ->with(['phase.project', 'status', 'priority', 'assignee', 'creator', 'assignedUsers'])
            ->withCount(['subtasks', 'subtasks as subtasks_done_count' => fn ($q) => $q->where('is_done', true)]);

        if ($user->isTeamLeader() && ! $user->isDirector()) {
            $teamIds = $user->getTeamIds();
            $teamMemberIds = User::whereHas('teams', function ($q) use ($teamIds) {
                $q->whereIn('teams.id', $teamIds);
            })->pluck('id')->toArray();
            $teamMemberIds[] = $user->id;
            $teamMemberIds = array_unique($teamMemberIds);

            $query->where(function ($q) use ($teamMemberIds, $user) {
                $q->whereIn('assigned_to', $teamMemberIds)
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('assignedUsers', function ($sub) use ($teamMemberIds) {
                        $sub->whereIn('user_id', $teamMemberIds);
                    });
            });
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id)
                    ->orWhereHas('assignedUsers', function ($sub) use ($user) {
                        $sub->where('user_id', $user->id);
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('task_status_id', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('task_priority_id', $request->priority);
        }

        if ($request->filled('overdue') && $request->overdue == 1) {
            $query->overdue();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%");
        }

        $tasks = $query->orderBy('deadline')->paginate(15);

        $stats = [
            'total' => (clone $query)->count(),
            'completed' => (clone $query)->where('progress_percentage', 100)->count(),
            'in_progress' => (clone $query)->whereBetween('progress_percentage', [1, 99])->count(),
            'not_started' => (clone $query)->where('progress_percentage', 0)->count(),
            'overdue' => (clone $query)->overdue()->count(),
        ];

        $statuses = TaskStatus::orderBy('sort_order')->get();
        $priorities = TaskPriority::orderBy('level_order')->get();

        return view('admin.tasks.my-tasks', compact('tasks', 'stats', 'statuses', 'priorities'));
    }

    // ============================================================
    // CRUD OPERATIONS
    // ============================================================

    /**
     * "New task" without a phase yet: pick the phase first. If there is no
     * project, or no phase in any project, say which to create first.
     */
    public function choosePhase(): View
    {
        $projects = auth()->user()->getVisibleProjects()
            ->load(['phases' => fn ($q) => $q->orderBy('sort_order')])
            ->sortBy('name');

        if ($projects->isEmpty()) {
            return view('admin.needs-first', [
                'title' => 'Create Task',
                'icon' => 'folder-kanban',
                'heading' => 'Create a project first',
                'message' => 'Tasks live inside a phase, and phases inside a project. You have no projects yet, so start there.',
                'actionLabel' => 'Create project',
                'actionRoute' => route('admin.projects.create'),
            ]);
        }

        if ($projects->every(fn ($project) => $project->phases->isEmpty())) {
            $first = $projects->first();

            return view('admin.needs-first', [
                'title' => 'Create Task',
                'icon' => 'layers',
                'heading' => 'Add a phase first',
                'message' => 'Tasks live inside a phase, and none of your projects has a phase yet. Add one to '.$first->name.' to get started.',
                'actionLabel' => 'Add phase',
                'actionRoute' => route('admin.projects.phases.create', $first),
            ]);
        }

        return view('admin.tasks.choose-phase', ['projects' => $projects]);
    }

    public function create(Phase $phase): View
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            $visibleProjectIds = $user->getVisibleProjectIds();
            if (! in_array($phase->project_id, $visibleProjectIds)) {
                abort(403, 'You do not have permission to create tasks in this phase.');
            }
        }

        $statuses = TaskStatus::orderBy('sort_order')->get();
        $priorities = TaskPriority::orderBy('level_order')->get();
        $users = $this->getAvailableUsers($phase);
        $maxOrder = $phase->tasks()->max('sort_order') ?? 0;

        return view('admin.tasks.create', [
            'phase' => $phase,
            'task' => new Task,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'users' => $users,
            'maxOrder' => $maxOrder + 1,
        ]);
    }

    public function store(Request $request, Phase $phase): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            $visibleProjectIds = $user->getVisibleProjectIds();
            if (! in_array($phase->project_id, $visibleProjectIds)) {
                abort(403, 'You do not have permission to create tasks in this phase.');
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'task_status_id' => 'required|exists:task_statuses,id',
            'task_priority_id' => 'nullable|exists:task_priorities,id',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'resources' => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $phase, $validated) {
            $maxOrder = $phase->tasks()->max('sort_order') ?? 0;

            $task = $phase->tasks()->create([
                'task_status_id' => $validated['task_status_id'],
                'task_priority_id' => $validated['task_priority_id'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'estimated_hours' => $validated['estimated_hours'] ?? null,
                'estimated_cost' => $validated['estimated_cost'] ?? null,
                'actual_cost' => $validated['actual_cost'] ?? null,
                'resources' => $validated['resources'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'deadline' => $validated['deadline'] ?? null,
                'sort_order' => $validated['sort_order'] ?? $maxOrder + 1,
                'progress_percentage' => 0,
                'priority_score' => $this->calculatePriorityScoreFromData($validated),
                'created_by' => $request->user()->id,
                'updated_by' => null,
            ]);

            if ($validated['assigned_to'] ?? false) {
                $task->assignTo(User::find($validated['assigned_to']));
            }

            $phase->updateProgress();
            if ($phase->project) {
                $phase->project->update(['progress_percentage' => $phase->project->getProgressAttribute()]);
            }
        });

        return redirect()
            ->route('admin.projects.show', $phase->project_id)
            ->with('success', 'Task created successfully.');
    }

    public function show(Task $task): View
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            $visibleTaskIds = $user->getVisibleTaskIds();
            if (! in_array($task->id, $visibleTaskIds)) {
                abort(403, 'You do not have permission to view this task.');
            }
        }

        $task->load([
            'phase.project',
            'status',
            'priority',
            'assignee',
            'creator',
            'reviewer',
            'assignedUsers',
            'statusHistory',
        ]);

        $availableAssignees = $this->getAvailableUsers($task->phase);
        $statuses = TaskStatus::orderBy('sort_order')->get();
        $availableTransitions = $task->getAvailableTransitions();

        return view('admin.tasks.show', [
            'task' => $task,
            'availableAssignees' => $availableAssignees,
            'statuses' => $statuses,
            'availableTransitions' => $availableTransitions,
        ]);
    }

    public function edit(Task $task): View
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            if (! $user->hasPermissionTo('edit-task') && ! $user->hasPermissionTo('edit-own-task')) {
                abort(403, 'You do not have permission to edit this task.');
            }

            if ($user->hasPermissionTo('edit-own-task') && ! $user->hasPermissionTo('edit-task')) {
                if ($task->created_by !== $user->id && $task->assigned_to !== $user->id) {
                    abort(403, 'You can only edit tasks you created or are assigned to.');
                }
            }
        }

        $statuses = TaskStatus::orderBy('sort_order')->get();
        $priorities = TaskPriority::orderBy('level_order')->get();
        $users = $this->getAvailableUsers($task->phase);

        return view('admin.tasks.edit', [
            'task' => $task,
            'statuses' => $statuses,
            'priorities' => $priorities,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            if (! $user->hasPermissionTo('edit-task') && ! $user->hasPermissionTo('edit-own-task')) {
                abort(403, 'You do not have permission to edit this task.');
            }

            if ($user->hasPermissionTo('edit-own-task') && ! $user->hasPermissionTo('edit-task')) {
                if ($task->created_by !== $user->id && $task->assigned_to !== $user->id) {
                    abort(403, 'You can only edit tasks you created or are assigned to.');
                }
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'task_status_id' => 'required|exists:task_statuses,id',
            'task_priority_id' => 'nullable|exists:task_priorities,id',
            'assigned_to' => 'nullable|exists:users,id',
            'estimated_hours' => 'nullable|numeric|min:0',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'resources' => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'progress_percentage' => 'nullable|numeric|min:0|max:100',
            'sort_order' => 'nullable|integer|min:1',
        ]);

        // The edit form also carries status; hold it to the same rule as the status buttons.
        if ($task->task_status_id != $validated['task_status_id']) {
            $this->authorizeTaskWork($task, TaskStatus::find($validated['task_status_id'])->name);
        }

        DB::transaction(function () use ($request, $task, $validated) {
            $oldAssignee = $task->assigned_to;
            $oldStatusId = $task->task_status_id;

            $task->update([
                'task_status_id' => $validated['task_status_id'],
                'task_priority_id' => $validated['task_priority_id'] ?? null,
                'assigned_to' => $validated['assigned_to'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'estimated_hours' => $validated['estimated_hours'] ?? null,
                'estimated_cost' => $validated['estimated_cost'] ?? null,
                'actual_cost' => $validated['actual_cost'] ?? null,
                'resources' => $validated['resources'] ?? null,
                'start_date' => $validated['start_date'] ?? null,
                'deadline' => $validated['deadline'] ?? null,
                'sort_order' => $validated['sort_order'] ?? $task->sort_order,
                'progress_percentage' => $validated['progress_percentage'] ?? $task->progress_percentage,
                'priority_score' => $this->calculatePriorityScoreFromData($validated),
                'updated_by' => $request->user()->id,
            ]);

            if ($oldStatusId != $validated['task_status_id']) {
                $newStatus = TaskStatus::find($validated['task_status_id']);
                $task->transitionTo($newStatus);
            }

            if ($validated['assigned_to'] ?? false) {
                if ($oldAssignee != $validated['assigned_to']) {
                    $task->assignTo(User::find($validated['assigned_to']));
                }
            }

            if ($task->progress_percentage >= 100 && ! $task->completed_at) {
                $task->update([
                    'completed_at' => now(),
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ]);
            }

            $task->phase->updateProgress();
            if ($task->phase->project) {
                $task->phase->project->update(['progress_percentage' => $task->phase->project->getProgressAttribute()]);
            }
        });

        return redirect()
            ->route('admin.tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            if (! $user->hasPermissionTo('delete-task')) {
                abort(403, 'You do not have permission to delete this task.');
            }
        }

        $taskName = $task->title;
        $projectId = $task->phase->project_id;

        DB::transaction(function () use ($task) {
            $task->assignedUsers()->detach();
            $task->statusHistory()->delete();
            $task->delete();
            $task->phase->updateProgress();
            if ($task->phase->project) {
                $task->phase->project->update(['progress_percentage' => $task->phase->project->getProgressAttribute()]);
            }
        });

        return redirect()
            ->route('admin.projects.show', $projectId)
            ->with('success', "Task '{$taskName}' deleted successfully.");
    }

    // ============================================================
    // TASK ACTIONS
    // ============================================================

    public function assign(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector() && ! $user->hasPermissionTo('assign-task')) {
            abort(403, 'You do not have permission to assign tasks.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'note' => 'nullable|string',
        ]);

        $assignee = User::find($validated['user_id']);

        if (! $user->isDirector()) {
            $teamIds = $user->getTeamIds();
            $isInTeam = $assignee->teams()->whereIn('teams.id', $teamIds)->exists();
            if (! $isInTeam) {
                abort(403, 'You can only assign tasks to members of your team.');
            }
        }

        DB::transaction(function () use ($task, $assignee, $validated) {
            $task->assignTo($assignee, $validated['note'] ?? null);

            if (! $task->assigned_to) {
                $task->update(['assigned_to' => $assignee->id]);
            }
        });

        return redirect()
            ->route('admin.tasks.show', $task)
            ->with('success', "Task assigned to {$assignee->name} successfully.");
    }

    public function unassign(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector() && ! $user->hasPermissionTo('assign-task')) {
            abort(403, 'You do not have permission to unassign tasks.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $assignee = User::find($validated['user_id']);

        DB::transaction(function () use ($task, $assignee) {
            $task->unassignFrom($assignee);
        });

        return redirect()
            ->route('admin.tasks.show', $task)
            ->with('success', "Task unassigned from {$assignee->name} successfully.");
    }

    public function assignMultiple(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector() && ! $user->hasPermissionTo('assign-task')) {
            abort(403, 'You do not have permission to assign tasks.');
        }

        $validated = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'note' => 'nullable|string',
        ]);

        $assignee = User::find($validated['user_id']);

        if (! $user->isDirector()) {
            $teamIds = $user->getTeamIds();
            $isInTeam = $assignee->teams()->whereIn('teams.id', $teamIds)->exists();
            if (! $isInTeam) {
                abort(403, 'You can only assign tasks to members of your team.');
            }
        }

        $assignedCount = 0;

        DB::transaction(function () use ($validated, $assignee, &$assignedCount) {
            foreach ($validated['task_ids'] as $taskId) {
                $task = Task::find($taskId);
                if ($task && ! $task->isAssignedTo($assignee)) {
                    $task->assignTo($assignee, $validated['note'] ?? null);
                    $assignedCount++;
                }
            }
        });

        return redirect()
            ->route('admin.tasks.index')
            ->with('success', "Assigned {$assignedCount} tasks to {$assignee->name}.");
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'task_status_id' => 'required|exists:task_statuses,id',
            'note' => 'nullable|string',
        ]);

        $newStatus = TaskStatus::find($validated['task_status_id']);
        $this->authorizeTaskWork($task, $newStatus->name);

        if (! $task->canTransitionTo($newStatus->name)) {
            return back()->with('error', 'Invalid status transition.');
        }

        $task->transitionTo($newStatus, $validated['note'] ?? null);

        return redirect()
            ->route('admin.tasks.show', $task)
            ->with('success', 'Task status updated successfully.');
    }

    public function updateProgress(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        $this->authorizeTaskWork($task);

        $validated = $request->validate([
            'progress_percentage' => 'required|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($task, $validated) {
            $task->update([
                'progress_percentage' => $validated['progress_percentage'],
                'updated_by' => auth()->id(),
            ]);

            if ($validated['progress_percentage'] >= 100 && ! $task->completed_at) {
                $task->update([
                    'completed_at' => now(),
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                ]);
            }

            $task->phase->updateProgress();
            if ($task->phase->project) {
                $task->phase->project->update(['progress_percentage' => $task->phase->project->getProgressAttribute()]);
            }
        });

        return redirect()
            ->route('admin.tasks.show', $task)
            ->with('success', 'Progress updated successfully.');
    }

    // ============================================================
    // STATUS TRANSITION
    // ============================================================

    public function transition(Request $request, Task $task): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'status' => 'required|string|exists:task_statuses,name',
            'note' => 'nullable|string|max:500',
        ]);

        $this->authorizeTaskWork($task, $validated['status']);

        if (! $task->canTransitionTo($validated['status'])) {
            return back()->with('error', 'Invalid status transition.');
        }

        $newStatus = TaskStatus::where('name', $validated['status'])->first();
        $success = $task->transitionTo($newStatus, $validated['note'] ?? null);

        if (! $success) {
            return back()->with('error', 'Failed to update task status.');
        }

        return redirect()
            ->back()
            ->with('success', "Task status updated to '{$validated['status']}'.");
    }

    // ============================================================
    // KANBAN REORDER (AJAX)
    // ============================================================

    public function kanbanReorder(Request $request): JsonResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'status' => 'required|string|exists:task_statuses,name',
            'position' => 'nullable|integer|min:0',
        ]);

        $task = Task::find($validated['task_id']);
        $this->authorizeTaskWork($task, $validated['status']);

        if (! $task->canTransitionTo($validated['status'])) {
            return response()->json(['error' => 'Invalid status transition'], 422);
        }

        $newStatus = TaskStatus::where('name', $validated['status'])->first();
        $success = $task->transitionTo($newStatus);

        if (! $success) {
            return response()->json(['error' => 'Failed to update task'], 500);
        }

        return response()->json(['success' => true]);
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    /**
     * Who may move a task along. Editors (Team Leaders, the Director) may
     * change any task they can see. Everyone else only the tasks assigned to
     * them, and completing a task that is under review stays with a reviewer.
     */
    protected function authorizeTaskWork(Task $task, ?string $toStatus = null): void
    {
        $user = auth()->user();

        if ($user->isDirector()) {
            return;
        }

        if ($user->can('edit-task')) {
            abort_unless(in_array($task->id, $user->getVisibleTaskIds()), 403, 'This task is outside your teams.');

            return;
        }

        abort_unless(
            $task->isAssignedTo($user) && $user->hasAnyPermission(['edit-own-task', 'complete-task', 'update-task-progress']),
            403,
            'You can only update tasks assigned to you.'
        );

        abort_if(
            $toStatus === 'Completed' && ! $user->can('review-task'),
            403,
            'Your team leader marks a task completed after reviewing it. Move it to Under Review instead.'
        );
    }

    /**
     * Only the tasks this user may see. The OR conditions sit inside one
     * where() group; left loose, they swallowed every filter after them
     * ("A or B and project = 3"), which is why the board filters did nothing.
     */
    protected function limitToVisibleTasks($query, User $user): void
    {
        if ($user->isDirector()) {
            return;
        }

        if ($user->isTeamLeader()) {
            $teamIds = $user->getTeamIds();
            $teamMemberIds = User::whereHas('teams', function ($q) use ($teamIds) {
                $q->whereIn('teams.id', $teamIds);
            })->pluck('id')->push($user->id)->unique()->all();

            $query->where(function ($q) use ($teamMemberIds, $user) {
                $q->whereIn('assigned_to', $teamMemberIds)
                    ->orWhere('created_by', $user->id);
            });

            return;
        }

        $query->where(function ($q) use ($user) {
            $q->where('assigned_to', $user->id)
                ->orWhere('created_by', $user->id)
                ->orWhereHas('assignedUsers', function ($sub) use ($user) {
                    $sub->where('user_id', $user->id);
                });
        });
    }

    /** Projects and people for the filter dropdowns, limited to what this user can see. */
    protected function filterOptions(User $user): array
    {
        if ($user->isDirector()) {
            return [Project::orderBy('name')->get(), User::orderBy('name')->get()];
        }

        $teamIds = $user->getTeamIds();
        $users = User::where('id', $user->id)
            ->orWhereHas('teams', fn ($q) => $q->whereIn('teams.id', $teamIds))
            ->orderBy('name')
            ->get();

        return [$user->getVisibleProjects()->sortBy('name')->values(), $users];
    }

    protected function getAvailableUsers(Phase $phase): Collection
    {
        $user = auth()->user();
        $project = $phase->project;

        if ($user->isDirector()) {
            return User::orderBy('name')->get();
        }

        if ($user->isTeamLeader()) {
            $teamIds = $user->getTeamIds();

            return User::whereHas('teams', function ($query) use ($teamIds) {
                $query->whereIn('teams.id', $teamIds);
            })->orderBy('name')->get();
        }

        return User::where('id', $user->id)->orderBy('name')->get();
    }

    public function getAssignableUsers(Request $request): JsonResponse
    {
        $user = auth()->user();
        $search = $request->get('q', '');

        $query = User::query();

        if (! $user->isDirector()) {
            if ($user->isTeamLeader()) {
                $teamIds = $user->getTeamIds();
                $query->whereHas('teams', function ($q) use ($teamIds) {
                    $q->whereIn('teams.id', $teamIds);
                });
            } else {
                $query->where('id', $user->id);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->limit(20)->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'text' => $user->name.' ('.$user->email.')',
            ];
        });

        return response()->json($users);
    }

    protected function calculatePriorityScoreFromData(array $data): int
    {
        $score = 0;

        $priorityWeights = [
            'Low' => 10,
            'Medium' => 20,
            'High' => 30,
            'Critical' => 40,
        ];

        if (isset($data['task_priority_id']) && $data['task_priority_id']) {
            $priority = TaskPriority::find($data['task_priority_id']);
            $score += $priorityWeights[$priority?->name] ?? 0;
        }

        if (isset($data['deadline']) && $data['deadline']) {
            $deadline = Carbon::parse($data['deadline']);
            $daysUntilDeadline = now()->diffInDays($deadline, false);
            if ($daysUntilDeadline < 0) {
                $score += 20;
            } elseif ($daysUntilDeadline < 3) {
                $score += 10;
            } elseif ($daysUntilDeadline < 7) {
                $score += 5;
            }
        }

        return $score;
    }
}
