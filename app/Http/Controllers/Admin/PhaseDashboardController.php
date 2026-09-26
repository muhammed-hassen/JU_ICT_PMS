<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PhaseDashboardController extends Controller
{
    /**
     * Display a list of projects with phase counts
     */
    public function index(): View
    {
        $projects = $this->visibleProjects()
            ->withCount('phases')
            ->with(['phases' => function ($query) {
                $query->withCount('tasks');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Stat cards count only what this user can see; they used to count the whole system.
        $projectIds = $this->visibleProjects()->pluck('id');
        $totals = [
            'projects' => $projectIds->count(),
            'phases' => Phase::whereIn('project_id', $projectIds)->count(),
            'tasks' => Task::whereHas('phase', fn ($q) => $q->whereIn('project_id', $projectIds))->count(),
            'byStatus' => $this->visibleProjects()->get(['status'])->countBy('status')->all(),
            'completedPhases' => Phase::whereIn('project_id', $projectIds)->whereHas('status', fn ($q) => $q->where('name', 'Completed'))->count(),
        ];

        return view('admin.phases.dashboard', compact('projects', 'totals'));
    }

    /**
     * Get project phase statistics
     */
    public function stats(Request $request)
    {
        $projectId = $request->input('project_id');

        if ($projectId) {
            $project = $this->visibleProjects()->with(['phases.tasks'])->findOrFail($projectId);
            $phases = $project->phases;

            return response()->json([
                'project' => $project->name,
                'total_phases' => $phases->count(),
                'completed_phases' => $phases->where('phase_status_id', 3)->count(),
                'total_tasks' => $phases->sum(fn ($p) => $p->tasks->count()),
                'completed_tasks' => $phases->sum(fn ($p) => $p->tasks->where('task_status_id', 4)->count()),
                'phases' => $phases->map(function ($phase) {
                    return [
                        'id' => $phase->id,
                        'name' => $phase->name,
                        'status' => $phase->status?->name ?? 'Not Started',
                        'task_count' => $phase->tasks->count(),
                        'progress' => $phase->progress_percentage,
                    ];
                }),
            ]);
        }

        $projectIds = $this->visibleProjects()->pluck('id');

        return response()->json([
            'total_projects' => $projectIds->count(),
            'total_phases' => Phase::whereIn('project_id', $projectIds)->count(),
            'total_tasks' => Task::whereHas('phase', fn ($q) => $q->whereIn('project_id', $projectIds))->count(),
        ]);
    }

    /**
     * Same scoping as the projects list: the Director sees everything, everyone
     * else only the projects they can open.
     */
    private function visibleProjects()
    {
        $user = auth()->user();
        $query = Project::query();

        if (! $user->isDirector()) {
            $query->whereIn('id', $user->getVisibleProjectIds() ?: [0]);
        }

        return $query;
    }
}
