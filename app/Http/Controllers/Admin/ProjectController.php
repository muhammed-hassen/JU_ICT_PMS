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
        $this->middleware('permission:edit-project')->only(['edit', 'update']);
        $this->middleware('permission:delete-project')->only(['destroy']);
    }

    public function index(): View
    {
        $user = auth()->user();

        $query = Project::query()
            ->with(['template', 'creator', 'phases', 'teams'])
            ->withCount('phases');

        if (! $user->isDirector()) {
            $projectIds = $user->getVisibleProjectIds();
            if (empty($projectIds)) {
                $projectIds = [0];
            }
            $query->whereIn('id', $projectIds);
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.projects.index', compact('projects'));
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
