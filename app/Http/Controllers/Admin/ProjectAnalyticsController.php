<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectAnalyticsController extends Controller
{
    public function index(): View
    {
        // Overall stats
        $totalProjects = Project::count();
        $activeProjects = Project::where('status', 'active')->count();
        $completedProjects = Project::where('status', 'completed')->count();
        $draftProjects = Project::where('status', 'draft')->count();
        $archivedProjects = Project::where('status', 'archived')->count();

        // Average project completion time (in days)
        $avgCompletionTime = Project::where('status', 'completed')
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->select(DB::raw('AVG(DATEDIFF(end_date, start_date)) as avg_days'))
            ->value('avg_days') ?? 0;

        // Tasks stats
        $totalTasks = Task::count();
        $completedTasks = Task::whereHas('status', function ($q) {
            $q->where('name', 'Done')->orWhere('name', 'Completed');
        })->count();

        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

        // Project progress distribution
        $progressDistribution = Project::select(
            DB::raw('CASE 
                WHEN progress_percentage >= 90 THEN "90-100%"
                WHEN progress_percentage >= 70 THEN "70-89%"
                WHEN progress_percentage >= 50 THEN "50-69%"
                WHEN progress_percentage >= 30 THEN "30-49%"
                ELSE "0-29%"
            END as progress_range'),
            DB::raw('count(*) as count')
        )
            ->groupBy('progress_range')
            ->get()
            ->pluck('count', 'progress_range')
            ->toArray();

        // Top performers (users with most completed tasks)
        $topPerformers = User::withCount(['tasks as total_tasks'])
            ->withCount(['tasks as completed_tasks' => function ($q) {
                $q->whereHas('status', function ($sq) {
                    $sq->where('name', 'Done')->orWhere('name', 'Completed');
                });
            }])
            ->orderBy('completed_tasks', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                $user->completion_rate = $user->total_tasks > 0
                    ? round(($user->completed_tasks / $user->total_tasks) * 100, 2)
                    : 0;

                return $user;
            });

        // Monthly project creation trend
        $monthlyTrend = Project::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('count(*) as count')
        )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Project by team
        $projectsByTeam = DB::table('project_teams')
            ->join('teams', 'project_teams.team_id', '=', 'teams.id')
            ->select('teams.name', DB::raw('count(*) as count'))
            ->groupBy('teams.id', 'teams.name')
            ->get()
            ->pluck('count', 'name')
            ->toArray();

        return view('admin.analytics.index', compact(
            'totalProjects',
            'activeProjects',
            'completedProjects',
            'draftProjects',
            'archivedProjects',
            'avgCompletionTime',
            'totalTasks',
            'completedTasks',
            'completionRate',
            'progressDistribution',
            'topPerformers',
            'monthlyTrend',
            'projectsByTeam'
        ));
    }

    /**
     * Export analytics data as JSON
     */
    public function export(Request $request)
    {
        $data = [
            'generated_at' => now()->toDateTimeString(),
            'projects' => [
                'total' => Project::count(),
                'by_status' => Project::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get()
                    ->toArray(),
            ],
            'tasks' => [
                'total' => Task::count(),
                'by_status' => Task::select('task_status_id', DB::raw('count(*) as count'))
                    ->groupBy('task_status_id')
                    ->with('status')
                    ->get()
                    ->map(fn ($item) => [
                        'status' => $item->status?->name ?? 'Unknown',
                        'count' => $item->count,
                    ])
                    ->toArray(),
            ],
        ];

        return response()->json($data);
    }
}
