<?php

// app/Http/Controllers/Admin/ProjectController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhaseStatus;
use App\Models\Project;
use App\Models\ProjectTemplate;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\Team;
use App\Models\User;
use App\Services\ProjectTemplateApplier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('permission:view-projects')->only(['index', 'show']);
        $this->middleware('permission:create-project')->only(['create', 'store']);
        $this->middleware('permission:edit-project|edit-own-project')->only(['edit', 'update']);
        $this->middleware('permission:delete-project')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = Project::query()
            ->with(['template', 'creator', 'phases', 'teams'])
            ->withCount('phases');

        // Visibility scoping
        if (! $user->isDirector()) {
            $projectIds = $user->getVisibleProjectIds();
            $query->whereIn('id', empty($projectIds) ? [0] : $projectIds);
        }

        // ── Filters ──────────────────────────────────────────────
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                  ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($templateId = $request->input('template_id')) {
            $query->where('template_id', $templateId);
        }

        if ($teamId = $request->input('team_id')) {
            $query->whereHas('teams', fn ($q) => $q->where('teams.id', $teamId));
        }

        if ($creatorId = $request->input('creator_id')) {
            $query->where('created_by', $creatorId);
        }

        if ($request->input('overdue') === '1') {
            $query->where('end_date', '<', now())
                  ->where('status', '!=', 'completed');
        }

        if ($progressMin = $request->input('progress_min')) {
            $query->where('progress_percentage', '>=', (int) $progressMin);
        }

        if ($progressMax = $request->input('progress_max')) {
            $query->where('progress_percentage', '<=', (int) $progressMax);
        }

        // ── Sorting ───────────────────────────────────────────────
        $sortField = match($request->input('sort')) {
            'name'       => 'name',
            'progress'   => 'progress_percentage',
            'end_date'   => 'end_date',
            'start_date' => 'start_date',
            default      => 'created_at',
        };
        $sortDir = $request->input('dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDir);

        // ── Data for filter dropdowns ─────────────────────────────
        $templates = ProjectTemplate::orderBy('name')->get(['id', 'name']);
        $teams     = Team::orderBy('name')->get(['id', 'name']);
        $creators  = User::whereIn('id', Project::distinct()->pluck('created_by')->filter())
                         ->orderBy('name')->get(['id', 'name']);

        $projects  = $query->paginate(15)->withQueryString();

        // ── Summary stats (full scope, no filters) ────────────────
        $statsBase = Project::query();
        if (! $user->isDirector()) {
            $visibleIds = $user->getVisibleProjectIds();
            $statsBase->whereIn('id', empty($visibleIds) ? [0] : $visibleIds);
        }
        $allProjects = $statsBase->with('phases')->get();

        $summary = [
            'total'       => $allProjects->count(),
            'active'      => $allProjects->where('status', 'active')->count(),
            'completed'   => $allProjects->where('status', 'completed')->count(),
            'overdue'     => $allProjects->filter(fn ($p) =>
                                $p->end_date && $p->end_date->isPast() && $p->status !== 'completed'
                             )->count(),
            'avg_progress'=> $allProjects->count()
                                ? round($allProjects->avg('progress_percentage') ?? 0)
                                : 0,
            'on_track'    => $allProjects->filter(fn ($p) =>
                                $p->status === 'active'
                                && (! $p->end_date || ! $p->end_date->isPast())
                                && ($p->phases->isEmpty() || $p->phases->every(fn ($ph) => $ph->isOnTrack()))
                             )->count(),
        ];

        return view('admin.projects.index', compact(
            'projects', 'templates', 'teams', 'creators', 'summary'
        ));
    }

    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $user  = auth()->user();
        $query = trim($request->input('q', ''));

        if (strlen($query) < 1) {
            return response()->json([]);
        }

        $builder = Project::query()
            ->with(['template', 'creator'])
            ->withCount('phases')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%'.$query.'%')
                  ->orWhere('description', 'like', '%'.$query.'%');
            });

        if (! $user->isDirector()) {
            $projectIds = $user->getVisibleProjectIds();
            $builder->whereIn('id', empty($projectIds) ? [0] : $projectIds);
        }

        $results = $builder->orderBy('name')->limit(8)->get();

        return response()->json($results->map(fn ($p) => [
            'id'          => $p->id,
            'name'        => $p->name,
            'description' => $p->description ? \Str::limit($p->description, 60) : null,
            'status'      => $p->status,
            'progress'    => (int) round($p->progress_percentage ?? 0),
            'phases'      => $p->phases_count,
            'template'    => $p->template?->name,
            'url'         => route('admin.projects.show', $p),
        ]));
    }

    public function create(): View
    {
        $user = auth()->user();

        if (! $user->isDirector() && ! $user->isTeamLeader()) {
            abort(403, 'You do not have permission to create projects.');
        }

        $templates = ProjectTemplate::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $teams = Team::query()->orderBy('name')->get();
        $users = User::query()->orderBy('name')->get();

        return view('admin.projects.create', [
            'project' => new Project,
            'templates' => $templates,
            'teams' => $teams,
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector() && ! $user->isTeamLeader()) {
            abort(403, 'You do not have permission to create projects.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'objectives' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'template_id' => 'nullable|exists:project_templates,id',
            'team_ids' => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $project = Project::query()->create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'objectives' => $validated['objectives'] ?? null,
                'budget' => $validated['budget'] ?? null,
                'template_id' => $validated['template_id'] ?? null,
                'status' => 'draft',
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'progress_percentage' => 0,
                'created_by' => $request->user()->id,
                'updated_by' => null,
            ]);

            if (! empty($validated['team_ids'])) {
                $project->teams()->sync($validated['team_ids']);
            }

            if (! empty($validated['member_ids'])) {
                $project->members()->sync($validated['member_ids']);
            }

            if ($validated['template_id'] ?? false) {
                app(ProjectTemplateApplier::class)->apply($project->id, (int) $validated['template_id'], $request->user()->id);
            }
        });

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display a specific project with progress tracking
     */
    public function show(Project $project): View
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            $visibleProjectIds = $user->getVisibleProjectIds();
            if (! in_array($project->id, $visibleProjectIds)) {
                abort(403, 'You do not have permission to view this project.');
            }
        }

        // Load project with relationships
        $project->load([
            'phases' => function ($query) {
                $query->orderBy('sort_order');
            },
            'phases.tasks' => function ($query) {
                $query->orderBy('sort_order');
            },
            'phases.tasks.status',
            'phases.tasks.priority',
            'phases.tasks.assignee',
            'phases.status',
            'template',
            'teams',
            'members',
            'creator',
            'progressHistory' => function ($query) {
                $query->take(7);
            },
        ]);

        // Get all progress data
        $progressTrend = $project->getProgressTrendAttribute();
        $phaseBreakdown = $project->getPhaseProgressBreakdownAttribute();
        $timelineData = $project->getTimelineDataAttribute();
        $progressStats = $project->getProgressStatsAttribute();

        // Filter tasks based on user permissions
        if (! $user->isDirector()) {
            $visibleTaskIds = $user->getVisibleTaskIds();
            foreach ($project->phases as $phase) {
                $phase->tasks = $phase->tasks->filter(function ($task) use ($visibleTaskIds) {
                    return in_array($task->id, $visibleTaskIds);
                });
            }
        }

        $phaseStatuses = PhaseStatus::all();
        $taskStatuses = TaskStatus::all();
        $taskPriorities = TaskPriority::all();

        return view('admin.projects.show', compact(
            'project',
            'phaseStatuses',
            'taskStatuses',
            'taskPriorities',
            'progressTrend',
            'phaseBreakdown',
            'timelineData',
            'progressStats'
        ));
    }

    public function edit(Project $project): View
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            if ($user->isTeamLeader() && $project->created_by !== $user->id) {
                abort(403, 'You can only edit projects you created.');
            }
        }

        $templates = ProjectTemplate::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $teams = Team::query()->orderBy('name')->get();
        $users = User::query()->orderBy('name')->get();

        return view('admin.projects.edit', [
            'project' => $project->load(['teams', 'members']),
            'templates' => $templates,
            'teams' => $teams,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            if ($user->isTeamLeader() && $project->created_by !== $user->id) {
                abort(403, 'You can only edit projects you created.');
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'objectives' => 'nullable|string',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'required|in:draft,active,completed,archived',
            'team_ids' => 'nullable|array',
            'team_ids.*' => 'exists:teams,id',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        DB::transaction(function () use ($request, $project, $validated) {
            $project->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'objectives' => $validated['objectives'] ?? null,
                'budget' => $validated['budget'] ?? null,
                'status' => $validated['status'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'updated_by' => $request->user()->id,
            ]);

            if (isset($validated['team_ids'])) {
                $project->teams()->sync($validated['team_ids']);
            }

            if (isset($validated['member_ids'])) {
                $project->members()->sync($validated['member_ids']);
            }
        });

        return redirect()
            ->route('admin.projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Last step of the workflow: once every task is done, the Director or the
     * leader who owns the project closes it.
     */
    public function close(Project $project): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector() && $project->created_by !== $user->id) {
            abort(403, 'Only the Director or the project owner can close this project.');
        }

        $project->unsetRelation('phases');
        if ($project->getProgressAttribute() < 100) {
            return back()->with('error', 'Finish every task before closing the project.');
        }

        $project->update([
            'status' => 'completed',
            'progress_percentage' => 100,
            'actual_completion_date' => $project->actual_completion_date ?? now(),
            'updated_by' => $user->id,
        ]);

        return back()->with('success', 'Project closed.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isDirector()) {
            abort(403, 'You do not have permission to delete projects.');
        }

        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
