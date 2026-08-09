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
        $projects = Project::query()
            ->withCount('phases')
            ->with(['phases' => function ($query) {
                $query->withCount('tasks');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.phases.dashboard', compact('projects'));
    }

    /**
     * Get project phase statistics
     */
    public function stats(Request $request)
    {
        $projectId = $request->input('project_id');

        if ($projectId) {
            $project = Project::with(['phases.tasks'])->findOrFail($projectId);
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

        return response()->json([
            'total_projects' => Project::count(),
            'total_phases' => Phase::count(),
            'total_tasks' => Task::count(),
        ]);
    }
}
