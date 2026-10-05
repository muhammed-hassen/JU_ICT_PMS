<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Reports. Every number is built from the projects and tasks the viewer is
 * allowed to see, so a Team Leader's report covers their teams only. The
 * page, the PDF and the Excel (CSV) export all read the same data.
 */
class ProjectAnalyticsController extends Controller
{
    public function index(): View
    {
        return view('admin.analytics.index', $this->reportData(auth()->user()));
    }

    /** Machine-readable copy of the report. */
    public function export(): JsonResponse
    {
        $data = $this->reportData(auth()->user());

        return response()->json([
            'generated_at' => now()->toDateTimeString(),
            'scope' => $data['scopeLabel'],
            'projects' => [
                'total' => $data['totalProjects'],
                'by_status' => $data['statusCounts'],
            ],
            'tasks' => [
                'total' => $data['totalTasks'],
                'completed' => $data['completedTasks'],
                'overdue' => $data['overdueTasks'],
            ],
            'budget' => [
                'total' => $data['totalBudget'],
                'spent' => $data['totalSpent'],
            ],
            'project_rows' => $data['projectRows'],
            'people' => $data['topPerformers']->map->only(['name', 'email', 'total_tasks', 'completed_tasks', 'completion_rate'])->values(),
        ]);
    }

    public function exportPdf(): Response
    {
        $data = $this->reportData(auth()->user()) + ['generatedAt' => now()];

        return Pdf::loadView('admin.analytics.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download('ju-ict-pms-report-'.now()->format('Y-m-d').'.pdf');
    }

    /** CSV with a UTF-8 byte order mark, so Excel opens it with the right characters. */
    public function exportExcel(): StreamedResponse
    {
        $data = $this->reportData(auth()->user());
        $filename = 'ju-ict-pms-report-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['JU ICT PMS report', $data['scopeLabel'], 'Generated '.now()->format('Y-m-d H:i')]);
            fputcsv($out, []);
            fputcsv($out, ['Summary']);
            foreach ([
                'Projects' => $data['totalProjects'],
                'Active projects' => $data['activeProjects'],
                'Completed projects' => $data['completedProjects'],
                'Tasks' => $data['totalTasks'],
                'Completed tasks' => $data['completedTasks'],
                'Overdue tasks' => $data['overdueTasks'],
                'Task completion rate (%)' => $data['completionRate'],
                'Total budget (ETB)' => $data['totalBudget'],
                'Total spent (ETB)' => $data['totalSpent'],
            ] as $label => $value) {
                fputcsv($out, [$label, $value]);
            }

            fputcsv($out, []);
            fputcsv($out, ['Projects']);
            fputcsv($out, ['Project', 'Status', 'Progress (%)', 'Start', 'End', 'Phases', 'Tasks', 'Completed tasks', 'Overdue tasks', 'Planned progress (%)', 'Budget (ETB)', 'Estimated cost (ETB)', 'Spent (ETB)']);
            foreach ($data['projectRows'] as $row) {
                fputcsv($out, array_values($row));
            }

            fputcsv($out, []);
            fputcsv($out, ['People']);
            fputcsv($out, ['Name', 'Email', 'Tasks', 'Completed', 'Completion rate (%)']);
            foreach ($data['topPerformers'] as $person) {
                fputcsv($out, [$person->name, $person->email, $person->total_tasks, $person->completed_tasks, $person->completion_rate]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Everything the report shows, limited to what this user can see.
     */
    private function reportData(User $user): array
    {
        $projects = $user->getVisibleProjects()->load(['phases.tasks.status']);
        $tasks = Task::with(['status', 'assignee'])
            ->whereIn('id', $user->getVisibleTaskIds())
            ->get();

        $isDone = fn (Task $task) => in_array($task->status?->name, ['Done', 'Completed'], true);

        $statusCounts = collect(['active', 'completed', 'draft', 'archived'])
            ->mapWithKeys(fn ($status) => [$status => $projects->where('status', $status)->count()])
            ->all();

        $totalTasks = $tasks->count();
        $completedTasks = $tasks->filter($isDone)->count();

        // Planned length of completed projects, in days.
        $avgCompletionTime = $projects->where('status', 'completed')
            ->filter(fn ($p) => $p->start_date && $p->end_date)
            ->avg(fn ($p) => $p->start_date->diffInDays($p->end_date)) ?? 0;

        $progressDistribution = $projects->pluck('progress_percentage')
            ->countBy(fn ($progress) => match (true) {
                $progress >= 90 => '90-100%',
                $progress >= 70 => '70-89%',
                $progress >= 50 => '50-69%',
                $progress >= 30 => '30-49%',
                default => '0-29%',
            })
            ->all();

        $monthlyTrend = $projects->pluck('created_at')
            ->countBy(fn ($createdAt) => $createdAt->format('Y-m'))
            ->sortKeysDesc()
            ->take(12)
            ->all();

        $projectRows = $projects->sortBy('name')->map(function (Project $project) use ($isDone) {
            $projectTasks = $project->phases->flatMap->tasks;

            return [
                'name' => $project->name,
                'status' => ucfirst($project->status ?? 'draft'),
                'progress' => round((float) $project->progress_percentage),
                'start' => $project->start_date?->format('Y-m-d') ?? '',
                'end' => $project->end_date?->format('Y-m-d') ?? '',
                'phases' => $project->phases->count(),
                'tasks' => $projectTasks->count(),
                'completed' => $projectTasks->filter($isDone)->count(),
                'overdue' => $projectTasks->filter->isOverdue()->count(),
                'planned' => $this->plannedProgress($project),
                'budget' => $project->budget !== null ? (float) $project->budget : null,
                'estimated_cost' => (float) $projectTasks->sum('estimated_cost'),
                'spent' => (float) $projectTasks->sum('actual_cost'),
            ];
        })->values()->all();

        return [
            'scopeLabel' => $user->isDirector() ? 'Whole JU-ICT Team' : 'Your teams',
            'totalProjects' => $projects->count(),
            'activeProjects' => $statusCounts['active'],
            'completedProjects' => $statusCounts['completed'],
            'draftProjects' => $statusCounts['draft'],
            'archivedProjects' => $statusCounts['archived'],
            'statusCounts' => $statusCounts,
            'avgCompletionTime' => $avgCompletionTime,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'overdueTasks' => $tasks->filter->isOverdue()->count(),
            'completionRate' => $totalTasks > 0 ? round($completedTasks / $totalTasks * 100, 1) : 0,
            'progressDistribution' => $progressDistribution,
            'monthlyTrend' => $monthlyTrend,
            'projectRows' => $projectRows,
            'topPerformers' => $this->people($tasks, $isDone),
            'totalBudget' => collect($projectRows)->sum('budget'),
            'totalSpent' => collect($projectRows)->sum('spent'),
        ];
    }

    /**
     * Where the project should be by today if work ran evenly from start to end
     * date. Compared with actual progress, this is the planned-vs-actual view.
     */
    private function plannedProgress(Project $project): ?int
    {
        if (! $project->start_date || ! $project->end_date) {
            return null;
        }

        $total = max(1, $project->start_date->diffInDays($project->end_date));
        $elapsed = $project->start_date->diffInDays(now(), false);

        return (int) round(min(100, max(0, $elapsed / $total * 100)));
    }

    /** People with tasks in scope, most completed first. */
    private function people(Collection $tasks, callable $isDone): Collection
    {
        return $tasks->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->map(function (Collection $assigned) use ($isDone) {
                $person = $assigned->first()->assignee;
                $done = $assigned->filter($isDone)->count();
                $person->total_tasks = $assigned->count();
                $person->completed_tasks = $done;
                $person->completion_rate = round($done / $assigned->count() * 100, 1);

                return $person;
            })
            ->filter()
            ->sortByDesc('completed_tasks')
            ->take(10)
            ->values();
    }
}
