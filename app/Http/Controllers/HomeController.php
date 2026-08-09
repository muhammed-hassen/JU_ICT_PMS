<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        // ========== PROJECTS ==========
        $projects = $user->getVisibleProjects();
        $projectsCount = $projects->count();

        // Project status breakdown for chart
        $statusLabels = [];
        $statusData = [];
        $statusNames = ['draft', 'active', 'completed', 'archived'];
        $statusDisplay = ['Draft', 'Active', 'Completed', 'Archived'];

        $statusCounts = $projects->groupBy('status')->map->count();
        foreach ($statusNames as $index => $status) {
            $statusLabels[] = $statusDisplay[$index] ?? ucfirst($status);
            $statusData[] = $statusCounts[$status] ?? 0;
        }

        // ========== TASKS ==========
        $tasks = $user->getVisibleTasks();
        $tasksCount = $tasks->count();
        $completedTasks = $tasks->where('progress_percentage', 100)->count();
        $inProgressTasks = $tasks->whereBetween('progress_percentage', [1, 99])->count();
        $notStartedTasks = $tasks->where('progress_percentage', 0)->count();
        $overdueTasks = $tasks->filter(function ($task) {
            return $task->isOverdue();
        })->count();

        // ========== USERS ==========
        if ($user->isDirector()) {
            $usersCount = User::count();
            $teamMembersCount = User::whereHas('roles', function ($q) {
                $q->where('name', 'Team Member');
            })->count();
        } else {
            $usersCount = null;
            $teamIds = $user->getTeamIds();
            $teamMembersCount = User::whereHas('teams', function ($q) use ($teamIds) {
                $q->whereIn('teams.id', $teamIds);
            })->count();
        }

        // ========== RECENT PROJECTS ==========
        $recentProjects = $projects->sortByDesc('created_at')->take(5);

        return view('home', compact(
            'projectsCount',
            'tasksCount',
            'completedTasks',
            'inProgressTasks',
            'notStartedTasks',
            'overdueTasks',
            'usersCount',
            'teamMembersCount',
            'recentProjects',
            'statusLabels',
            'statusData'
        ));
    }
}
