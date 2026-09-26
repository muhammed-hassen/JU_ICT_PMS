<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use App\Models\PhaseStatus;
use App\Models\Project;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PhaseController extends Controller
{
    protected ActivityLogService $activityLog;

    public function __construct(ActivityLogService $activityLog)
    {
        $this->middleware(['auth']);
        $this->middleware('permission:view-phases')->only(['index', 'show']);
        $this->middleware('permission:create-phase')->only(['create', 'store']);
        $this->middleware('permission:edit-phase')->only(['edit', 'update', 'updateStatus']);
        $this->middleware('permission:delete-phase')->only(['destroy', 'forceDelete']);
        $this->middleware('permission:reorder-phases')->only(['reorder']);

        $this->activityLog = $activityLog;
    }

    // ============================================================
    // ========== STANDALONE PHASE ROUTES ==========
    // ============================================================

    /**
     * Display all phases across all projects (Standalone view)
     * URL: /admin/phases
     */
    public function index(): View
    {
        $user = auth()->user();

        $query = Phase::query()
            ->with(['project', 'status', 'tasks'])
            ->orderBy('created_at', 'desc');

        // If not director, only show phases from visible projects
        if (! $user->isDirector()) {
            $projectIds = $user->getVisibleProjectIds();
            if (empty($projectIds)) {
                $phases = Phase::query()->whereRaw('1 = 0')->paginate(15);

                return view('admin.phases.index', compact('phases'));
            }
            $query->whereIn('project_id', $projectIds);
        }

        $phases = $query->paginate(15);

        return view('admin.phases.index', compact('phases'));
    }

    /**
     * Show form to create a new phase (Standalone)
     * URL: /admin/phases/create
     */
    public function create() //
    {
        $user = auth()->user();

        // Get all projects the user can see
        $projects = $user->getVisibleProjects();

        // Work goes Project, then Phase, then Task: no project, no phase.
        if ($projects->isEmpty()) {
            return view('admin.needs-first', [
                'title' => 'Create Phase',
                'icon' => 'folder-kanban',
                'heading' => 'Create a project first',
                'message' => 'A phase belongs to a project, and you have no projects yet. Create the project, then add its phases.',
                'actionLabel' => 'Create project',
                'actionRoute' => route('admin.projects.create'),
            ]);
        }

        $statuses = PhaseStatus::all();
        $maxOrder = 1;

        return view('admin.phases.create', [
            'projects' => $projects,
            'statuses' => $statuses,
            'maxOrder' => $maxOrder,
        ]);
    }

    /**
     * Store a newly created phase (Standalone)
     * URL: /admin/phases (POST)
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'phase_status_id' => 'required|exists:phase_statuses,id',
            'sort_order' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Check if user can create phase in this project
        if (! $user->isDirector()) {
            $visibleProjectIds = $user->getVisibleProjectIds();
            if (! in_array($validated['project_id'], $visibleProjectIds)) {
                abort(403, 'You do not have permission to create phases in this project.');
            }
        }

        $project = Project::find($validated['project_id']);
        $validated['sort_order'] = $this->freeSortOrder($project, $validated['sort_order'] ?? null);

        $phase = $project->phases()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'phase_status_id' => $validated['phase_status_id'],
            'sort_order' => $validated['sort_order'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'progress_percentage' => 0,
            'created_by' => $request->user()->id,
            'updated_by' => null,
        ]);

        $this->activityLog->log(
            $phase,
            'created',
            'Phase created',
            [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'phase_name' => $phase->name,
            ]
        );

        return redirect()
            ->route('admin.phases.index')
            ->with('success', "Phase '{$phase->name}' created successfully.");
    }

    // ============================================================
    // ========== NESTED PHASE ROUTES (Under Project) ==========
    // ============================================================

    /**
     * Display all phases for a specific project (Nested view)
     * URL: /admin/projects/{project}/phases
     */
    public function indexNested(Project $project): View
    {
        $user = auth()->user();

        // Check if user can view this project's phases
        if (! $user->isDirector()) {
            $visibleProjectIds = $user->getVisibleProjectIds();
            if (! in_array($project->id, $visibleProjectIds)) {
                abort(403, 'You do not have permission to view phases for this project.');
            }
        }

        $phases = $project->phases()
            ->with(['status', 'tasks.status', 'tasks.priority', 'tasks.assignee'])
            ->orderBy('sort_order')
            ->get();

        // Filter tasks based on user permissions
        if (! $user->isDirector()) {
            $visibleTaskIds = $user->getVisibleTaskIds();
            foreach ($phases as $phase) {
                $phase->tasks = $phase->tasks->filter(function ($task) use ($visibleTaskIds) {
                    return in_array($task->id, $visibleTaskIds);
                });
            }
        }

        $phases->each(function ($phase) {
            $phase->task_stats = $phase->getTaskStatsAttribute();
        });

        return view('admin.projects.phases.index', compact('project', 'phases'));
    }

    /**
     * Show form to create a new phase under a specific project (Nested)
     * URL: /admin/projects/{project}/phases/create
     */
    public function createNested(Project $project): View
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            $visibleProjectIds = $user->getVisibleProjectIds();
            if (! in_array($project->id, $visibleProjectIds)) {
                abort(403, 'You do not have permission to create phases in this project.');
            }
        }

        $statuses = PhaseStatus::all();
        $maxOrder = $project->phases()->withTrashed()->max('sort_order') ?? 0;

        return view('admin.projects.phases.create', [
            'project' => $project,
            'phase' => new Phase,
            'statuses' => $statuses,
            'maxOrder' => $maxOrder + 1,
        ]);
    }

    /**
     * Store a newly created phase under a specific project (Nested)
     * URL: /admin/projects/{project}/phases (POST)
     */
    public function storeNested(Request $request, Project $project): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            $visibleProjectIds = $user->getVisibleProjectIds();
            if (! in_array($project->id, $visibleProjectIds)) {
                abort(403, 'You do not have permission to create phases in this project.');
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'phase_status_id' => 'required|exists:phase_statuses,id',
            'sort_order' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['sort_order'] = $this->freeSortOrder($project, $validated['sort_order'] ?? null);

        $phase = $project->phases()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'phase_status_id' => $validated['phase_status_id'],
            'sort_order' => $validated['sort_order'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'progress_percentage' => 0,
            'created_by' => $request->user()->id,
            'updated_by' => null,
        ]);

        $this->activityLog->log(
            $phase,
            'created',
            'Phase created',
            [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'phase_name' => $phase->name,
            ]
        );

        return redirect()
            ->route('admin.projects.phases.index', $project)
            ->with('success', "Phase '{$phase->name}' created successfully.");
    }

    // ============================================================
    // ========== INDIVIDUAL PHASE OPERATIONS ==========
    // ============================================================

    /**
     * Display a specific phase
     * URL: /admin/phases/{phase}
     */
    /**
     * Display a specific phase
     */
    public function show(Phase $phase): View
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            $visiblePhaseIds = $user->getVisiblePhaseIds();
            if (! in_array($phase->id, $visiblePhaseIds)) {
                abort(403, 'You do not have permission to view this phase.');
            }
        }

        $phase->load([
            'project',
            'status',
            'tasks' => function ($query) {
                $query->orderBy('sort_order');
            },
            'tasks.status',
            'tasks.priority',
            'tasks.assignee',
            'tasks.creator',
            'creator',
            'milestones',
        ]);

        if (! $user->isDirector()) {
            $visibleTaskIds = $user->getVisibleTaskIds();
            $phase->tasks = $phase->tasks->filter(function ($task) use ($visibleTaskIds) {
                return in_array($task->id, $visibleTaskIds);
            });
        }

        $phase->task_stats = $phase->getTaskStatsAttribute();

        return view('admin.projects.phases.show', compact('phase'));
    }

    /**
     * Show form to edit a phase
     * URL: /admin/phases/{phase}/edit
     */
    public function edit(Phase $phase): View
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            $visiblePhaseIds = $user->getVisiblePhaseIds();
            if (! in_array($phase->id, $visiblePhaseIds)) {
                abort(403, 'You do not have permission to edit this phase.');
            }
        }

        $statuses = PhaseStatus::all();
        $project = $phase->project;

        return view('admin.phases.edit', [
            'phase' => $phase,
            'project' => $project,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Update the specified phase
     * URL: /admin/phases/{phase} (PUT)
     */
    public function update(Request $request, Phase $phase): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            $visiblePhaseIds = $user->getVisiblePhaseIds();
            if (! in_array($phase->id, $visiblePhaseIds)) {
                abort(403, 'You do not have permission to edit this phase.');
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'phase_status_id' => 'required|exists:phase_statuses,id',
            'sort_order' => 'required|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        DB::transaction(function () use ($request, $phase, $validated) {
            $oldData = $phase->toArray();

            $phase->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'phase_status_id' => $validated['phase_status_id'],
                'sort_order' => $validated['sort_order'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'updated_by' => $request->user()->id,
            ]);

            $this->activityLog->log(
                $phase,
                'updated',
                'Phase updated',
                [
                    'old' => $oldData,
                    'new' => $phase->toArray(),
                    'changes' => array_diff_assoc($phase->toArray(), $oldData),
                ]
            );
        });

        return redirect()
            ->route('admin.phases.show', $phase)
            ->with('success', "Phase '{$validated['name']}' updated successfully.");
    }

    /**
     * Delete the specified phase
     * URL: /admin/phases/{phase} (DELETE)
     */
    public function destroy(Phase $phase): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            abort(403, 'You do not have permission to delete phases.');
        }

        if ($phase->tasks()->exists()) {
            return back()->with('error', 'Cannot delete phase with existing tasks. Please delete or move tasks first.');
        }

        $phaseName = $phase->name;
        $projectId = $phase->project_id;

        DB::transaction(function () use ($phase, $phaseName, $projectId) {
            $this->activityLog->log(
                $phase,
                'deleted',
                'Phase deleted',
                [
                    'phase_name' => $phaseName,
                    'project_id' => $projectId,
                ]
            );

            $phase->delete();
        });

        return redirect()
            ->route('admin.projects.phases.index', $projectId)
            ->with('success', "Phase '{$phaseName}' deleted successfully.");
    }

    /**
     * Reorder phases (drag and drop) - Nested
     * URL: /admin/projects/{project}/phases/reorder (POST)
     */
    public function reorder(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            $visibleProjectIds = $user->getVisibleProjectIds();
            if (! in_array($project->id, $visibleProjectIds)) {
                abort(403, 'You do not have permission to reorder phases in this project.');
            }
        }

        $validated = $request->validate([
            'phases' => 'required|array',
            'phases.*' => 'required|integer|exists:phases,id',
        ]);

        DB::transaction(function () use ($project, $validated) {
            foreach ($validated['phases'] as $index => $phaseId) {
                $phase = Phase::find($phaseId);
                if ($phase && $phase->project_id === $project->id) {
                    $phase->update(['sort_order' => $index + 1]);
                }
            }

            $this->activityLog->log(
                $project,
                'updated',
                'Phases reordered',
                ['new_order' => $validated['phases']]
            );
        });

        // The drag-to-reorder script posts with fetch and expects JSON back.
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('admin.projects.phases.index', $project)
            ->with('success', 'Phase order updated successfully.');
    }

    /**
     * Update phase status (quick action)
     * URL: /admin/phases/{phase}/status (PATCH)
     */
    public function updateStatus(Request $request, Phase $phase): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            $visiblePhaseIds = $user->getVisiblePhaseIds();
            if (! in_array($phase->id, $visiblePhaseIds)) {
                abort(403, 'You do not have permission to update this phase status.');
            }
        }

        $validated = $request->validate([
            'phase_status_id' => 'required|exists:phase_statuses,id',
        ]);

        $oldStatus = $phase->status->name;
        $newStatus = PhaseStatus::find($validated['phase_status_id']);

        $phase->update([
            'phase_status_id' => $validated['phase_status_id'],
            'updated_by' => $request->user()->id,
        ]);

        $this->activityLog->log(
            $phase,
            'status_changed',
            'Phase status changed',
            [
                'old_status' => $oldStatus,
                'new_status' => $newStatus->name,
            ]
        );

        return redirect()
            ->route('admin.phases.show', $phase)
            ->with('success', "Phase status updated to '{$newStatus->name}'.");
    }

    /**
     * Restore a soft-deleted phase
     * URL: /admin/phases/{phase}/restore (POST)
     */
    public function restore($id): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            abort(403, 'You do not have permission to restore phases.');
        }

        $phase = Phase::withTrashed()->findOrFail($id);
        $phase->restore();

        $this->activityLog->log(
            $phase,
            'restored',
            'Phase restored',
            ['phase_name' => $phase->name]
        );

        return back()->with('success', "Phase '{$phase->name}' restored successfully.");
    }

    /**
     * Permanently delete a phase
     * URL: /admin/phases/{phase}/force (DELETE)
     */
    public function forceDelete($id): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            abort(403, 'You do not have permission to permanently delete phases.');
        }

        $phase = Phase::withTrashed()->findOrFail($id);

        if ($phase->tasks()->withTrashed()->exists()) {
            return back()->with('error', 'Cannot permanently delete phase with existing tasks.');
        }

        $phaseName = $phase->name;
        $projectId = $phase->project_id;

        $phase->forceDelete();

        return redirect()
            ->route('admin.projects.phases.index', $projectId)
            ->with('success', "Phase '{$phaseName}' permanently deleted.");
    }

    public function updateProgress(Phase $phase): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            $visiblePhaseIds = $user->getVisiblePhaseIds();
            if (! in_array($phase->id, $visiblePhaseIds)) {
                abort(403, 'You do not have permission to update this phase.');
            }
        }

        $phase->update([
            'progress_percentage' => $phase->calculateProgress(),
        ]);

        // Update project progress with history
        if ($phase->project) {
            $phase->project->updateProgressWithHistory();
        }

        return back()->with('success', 'Progress updated successfully.');
    }

    /**
     * Manually update project progress (for admin)
     */
    public function updateProjectProgress(Project $project): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            abort(403, 'You do not have permission to update project progress.');
        }

        // Update all phases progress first
        foreach ($project->phases as $phase) {
            $phase->update([
                'progress_percentage' => $phase->calculateProgress(),
            ]);
        }

        // Update project progress with history
        $project->updateProgressWithHistory();

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Project progress updated successfully.');
    }

    /**
     * The position a new phase takes. Deleted phases keep their slot until they
     * are removed for good (the database allows one phase per position), so
     * they count too. A position already taken is a form error, not a crash.
     */
    private function freeSortOrder(Project $project, ?int $requested): int
    {
        if ($requested === null) {
            return ($project->phases()->withTrashed()->max('sort_order') ?? 0) + 1;
        }

        if ($project->phases()->withTrashed()->where('sort_order', $requested)->exists()) {
            throw ValidationException::withMessages([
                'sort_order' => "Position {$requested} is already taken in this project. Leave it empty to add the phase at the end.",
            ]);
        }

        return $requested;
    }
}
