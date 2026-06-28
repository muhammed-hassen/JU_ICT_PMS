<?php

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
    public function index(): View
    {
        $projects = Project::query()
            ->with(['template', 'creator', 'phases', 'teams'])
            ->withCount('phases')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.projects.index', compact('projects'));
    }

    public function create(): View
    {
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
            ->with('status', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $project->load(['phases.tasks', 'template', 'teams', 'members', 'creator']);

        $phaseStatuses = PhaseStatus::all();
        $taskStatuses = TaskStatus::all();
        $taskPriorities = TaskPriority::all();

        return view('admin.projects.show', compact('project', 'phaseStatuses', 'taskStatuses', 'taskPriorities'));
    }

    public function edit(Project $project): View
    {
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
            ->with('status', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('status', 'Project deleted successfully.');
    }

}
