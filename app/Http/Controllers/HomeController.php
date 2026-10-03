<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

/**
 * One dashboard per role (SRS 5.10). Each role lands on the page built for
 * its job: the Director sees the whole office, the System Administrator sees
 * accounts and access, a Team Leader sees their team, a Member sees their work.
 */
class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $user = auth()->user();

        return match (true) {
            $user->hasRole('System Administrator') => $this->systemDashboard(),
            $user->isDirector() => $this->directorDashboard($user),
            $user->hasRole('Team Leader') => $this->teamLeaderDashboard($user),
            default => $this->memberDashboard($user),
        };
    }

    private function directorDashboard(User $user): View
    {
        $projects = Project::with('creator')->get();
        $tasks = Task::with(['status', 'assignee', 'phase.project'])->get();

        $teams = Team::with(['teamLeader', 'members', 'projects'])->orderBy('name')->get()
            ->map(function (Team $team) use ($tasks) {
                $memberIds = $team->members->pluck('id');
                $teamTasks = $tasks->whereIn('assigned_to', $memberIds);

                return [
                    'team' => $team,
                    'leader' => $team->teamLeader?->name,
                    'members' => $memberIds->count(),
                    'projects' => $team->projects->count(),
                    'open' => $teamTasks->reject(fn ($t) => $this->isDone($t))->count(),
                    'overdue' => $teamTasks->filter->isOverdue()->count(),
                ];
            });

        return view('dashboards.director', $this->projectStats($projects) + $this->taskStats($tasks) + [
            'usersCount' => User::count(),
            'teams' => $teams,
            'overdueList' => $tasks->filter->isOverdue()->sortBy('deadline')->take(6)->values(),
            'recentProjects' => $projects->sortByDesc('created_at')->take(5),
        ]);
    }

    private function systemDashboard(): View
    {
        $roles = Role::withCount('users')->orderBy('name')->get();

        return view('dashboards.system', [
            'usersCount' => User::count(),
            'rolesCount' => $roles->count(),
            'permissionsCount' => Permission::count(),
            'usersWithoutRole' => User::doesntHave('roles')->count(),
            'teamsCount' => Team::count(),
            'roles' => $roles,
            'recentUsers' => User::with('roles')->latest()->take(6)->get(),
            'recentActivity' => ActivityLog::with('user')->latest()->take(8)->get(),
        ]);
    }

    private function teamLeaderDashboard(User $user): View
    {
        $projects = $user->getVisibleProjects()->load('phases');
        $tasks = Task::with(['status', 'assignee', 'phase.project'])->whereIn('id', $user->getVisibleTaskIds())->get();

        $members = User::whereHas('teams', fn ($q) => $q->whereIn('teams.id', $user->getTeamIds()))
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get()
            ->map(function (User $member) use ($tasks) {
                $mine = $tasks->where('assigned_to', $member->id);

                return [
                    'user' => $member,
                    'open' => $mine->reject(fn ($t) => $this->isDone($t))->count(),
                    'overdue' => $mine->filter->isOverdue()->count(),
                    'done' => $mine->filter(fn ($t) => $this->isDone($t))->count(),
                ];
            });

        return view('dashboards.team-leader', $this->taskStats($tasks) + [
            'teamNames' => Team::whereIn('id', $user->getTeamIds())->orderBy('name')->pluck('name'),
            'projects' => $projects->sortByDesc('progress_percentage')->values(),
            'awaitingReview' => $tasks->filter(fn ($t) => $t->status?->name === 'Under Review')->values(),
            'members' => $members,
            'overdueList' => $tasks->filter->isOverdue()->sortBy('deadline')->take(6)->values(),
        ]);
    }

    private function memberDashboard(User $user): View
    {
        $tasks = Task::with(['status', 'priority', 'phase.project'])
            ->where(fn ($q) => $q->where('assigned_to', $user->id)
                ->orWhereHas('assignedUsers', fn ($a) => $a->where('user_id', $user->id)))
            ->get();

        $open = $tasks->reject(fn ($t) => $this->isDone($t));

        return view('dashboards.member', [
            'openCount' => $open->count(),
            'inProgressCount' => $tasks->filter(fn ($t) => $t->status?->name === 'In Progress')->count(),
            'dueThisWeekCount' => $open->filter(fn ($t) => $t->deadline && $t->deadline->between(now(), now()->addDays(7)))->count(),
            'overdueCount' => $tasks->filter->isOverdue()->count(),
            'completedCount' => $tasks->count() - $open->count(),
            'upNext' => $open->sortBy(fn ($t) => $t->deadline?->timestamp ?? PHP_INT_MAX)->take(6)->values(),
            'recentlyAssigned' => $user->assignedTasks()->with(['phase.project', 'status'])->orderByPivot('assigned_at', 'desc')->take(5)->get(),
            'projects' => $tasks->pluck('phase.project')->filter()->unique('id')->values(),
        ]);
    }

    private function isDone(Task $task): bool
    {
        return in_array($task->status?->name, ['Done', 'Completed'], true);
    }

    private function taskStats(Collection $tasks): array
    {
        return [
            'tasksCount' => $tasks->count(),
            'completedTasks' => $tasks->filter(fn ($t) => $this->isDone($t))->count(),
            'inProgressTasks' => $tasks->filter(fn ($t) => $t->status?->name === 'In Progress')->count(),
            'reviewTasks' => $tasks->filter(fn ($t) => $t->status?->name === 'Under Review')->count(),
            'notStartedTasks' => $tasks->filter(fn ($t) => $t->status?->name === 'Not Started')->count(),
            'overdueTasks' => $tasks->filter->isOverdue()->count(),
        ];
    }

    private function projectStats(Collection $projects): array
    {
        $names = ['draft' => 'Draft', 'active' => 'Active', 'completed' => 'Completed', 'archived' => 'Archived'];

        return [
            'projectsCount' => $projects->count(),
            'statusLabels' => array_values($names),
            'statusData' => array_map(fn ($s) => $projects->where('status', $s)->count(), array_keys($names)),
        ];
    }
}
