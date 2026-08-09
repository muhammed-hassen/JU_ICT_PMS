<?php

// app/Http/Controllers/Admin/ActivityController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('permission:view-audit-logs')->only(['index', 'show']);
    }

    /**
     * Display all activities
     */
    public function index(Request $request): View
    {
        $query = ActivityLog::query()
            ->with(['user', 'loggable'])
            ->orderBy('created_at', 'desc');

        // Filter by user
        if ($request->filled('user')) {
            $query->where('user_id', $request->user);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by type
        if ($request->filled('type')) {
            $typeMap = [
                'project' => 'App\Models\Project',
                'phase' => 'App\Models\Phase',
                'task' => 'App\Models\Task',
                'team' => 'App\Models\Team',
                'user' => 'App\Models\User',
            ];
            if (isset($typeMap[$request->type])) {
                $query->where('loggable_type', $typeMap[$request->type]);
            }
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $activities = $query->paginate(20);

        // Get filter data
        $users = User::orderBy('name')->get();
        $actions = ActivityLog::distinct()->pluck('action')->toArray();
        $types = ['project', 'phase', 'task', 'team', 'user'];

        // Get stats
        $stats = [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::whereDate('created_at', now()->toDateString())->count(),
            'this_week' => ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => ActivityLog::whereMonth('created_at', now()->month)->count(),
        ];

        return view('admin.activity.index', compact('activities', 'users', 'actions', 'types', 'stats'));
    }

    /**
     * Display activities for a specific user
     */
    public function user(User $user): View
    {
        $activities = ActivityLog::where('user_id', $user->id)
            ->with(['user', 'loggable'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => ActivityLog::where('user_id', $user->id)->count(),
            'today' => ActivityLog::where('user_id', $user->id)
                ->whereDate('created_at', now()->toDateString())
                ->count(),
            'projects' => ActivityLog::where('user_id', $user->id)
                ->where('loggable_type', 'App\Models\Project')
                ->count(),
            'tasks' => ActivityLog::where('user_id', $user->id)
                ->where('loggable_type', 'App\Models\Task')
                ->count(),
        ];

        return view('admin.activity.user', compact('activities', 'user', 'stats'));
    }

    /**
     * Display activities for a specific project
     */
    public function project(Project $project): View
    {
        $activities = ActivityLog::where(function ($query) use ($project) {
            $query->where('loggable_type', 'App\Models\Project')
                ->where('loggable_id', $project->id)
                ->orWhere(function ($q) use ($project) {
                    $q->where('loggable_type', 'App\Models\Phase')
                        ->whereIn('loggable_id', $project->phases->pluck('id'));
                })
                ->orWhere(function ($q) use ($project) {
                    $taskIds = $project->phases->flatMap->tasks->pluck('id');
                    $q->where('loggable_type', 'App\Models\Task')
                        ->whereIn('loggable_id', $taskIds);
                });
        })
            ->with(['user', 'loggable'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.activity.project', compact('activities', 'project'));
    }

    /**
     * Display activities for a specific task
     */
    public function task(Task $task): View
    {
        $activities = ActivityLog::where('loggable_type', 'App\Models\Task')
            ->where('loggable_id', $task->id)
            ->with(['user', 'loggable'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.activity.task', compact('activities', 'task'));
    }
}
