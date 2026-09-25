<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PhaseController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectTemplateController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Organization\DirectorController;
use App\Http\Controllers\Organization\MemberController;
use App\Http\Controllers\Organization\TeamController;
use App\Http\Controllers\Organization\TeamLeaderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ========== PUBLIC ROUTES ==========
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

// ========== AUTHENTICATION ==========
Auth::routes(['register' => false]);

// ========== HOME ==========
Route::get('/home', [HomeController::class, 'index'])->name('home');

// ========== USER CRUD ==========
Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
});

// ============================================================
// ========== ROUTES ACCESSIBLE TO ALL AUTHENTICATED USERS ==========
// ============================================================
Route::middleware(['auth'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // ====== MY TASKS ======
        Route::get('tasks/my', [TaskController::class, 'myTasks'])->name('tasks.my');

        // ====== PROJECTS ======
        Route::get('projects', [ProjectController::class, 'index'])
            ->name('projects.index')
            ->middleware('permission:view-projects');

        Route::get('projects/create', [ProjectController::class, 'create'])
            ->name('projects.create')
            ->middleware('permission:create-project');

        Route::post('projects', [ProjectController::class, 'store'])
            ->name('projects.store')
            ->middleware('permission:create-project');

        Route::get('projects/{project}', [ProjectController::class, 'show'])
            ->name('projects.show')
            ->middleware('permission:view-project-details');

        // ====== PHASES ======
        Route::get('phases', [PhaseController::class, 'index'])
            ->name('phases.index')
            ->middleware('permission:view-phases');

        Route::get('phases/create', [PhaseController::class, 'create'])
            ->name('phases.create')
            ->middleware('permission:create-phase');

        Route::post('phases', [PhaseController::class, 'store'])
            ->name('phases.store')
            ->middleware('permission:create-phase');

        Route::get('phases/{phase}', [PhaseController::class, 'show'])
            ->name('phases.show')
            ->middleware('permission:view-phases');

        Route::get('projects/{project}/phases', [PhaseController::class, 'indexNested'])
            ->name('projects.phases.index')
            ->middleware('permission:view-phases');

        Route::get('projects/{project}/phases/create', [PhaseController::class, 'createNested'])
            ->name('projects.phases.create')
            ->middleware('permission:create-phase');

        Route::post('projects/{project}/phases', [PhaseController::class, 'storeNested'])
            ->name('projects.phases.store')
            ->middleware('permission:create-phase');

        // ====== TASKS ======
        Route::get('tasks', [TaskController::class, 'index'])
            ->name('tasks.index')
            ->middleware('permission:view-tasks');

        Route::get('tasks/kanban', [TaskController::class, 'kanban'])
            ->name('tasks.kanban')
            ->middleware('permission:view-tasks');

        Route::post('tasks/kanban/reorder', [TaskController::class, 'kanbanReorder'])
            ->name('tasks.kanban-reorder')
            ->middleware('permission:edit-task');

        Route::get('tasks/{task}', [TaskController::class, 'show'])
            ->name('tasks.show')
            ->middleware('permission:view-tasks');

        Route::get('tasks/{task}/edit', [TaskController::class, 'edit'])
            ->name('tasks.edit')
            ->middleware('permission:edit-task');

        Route::put('tasks/{task}', [TaskController::class, 'update'])
            ->name('tasks.update')
            ->middleware('permission:edit-task');

        Route::delete('tasks/{task}', [TaskController::class, 'destroy'])
            ->name('tasks.destroy')
            ->middleware('permission:delete-task');

        Route::post('tasks/{task}/assign', [TaskController::class, 'assign'])
            ->name('tasks.assign')
            ->middleware('permission:assign-task');

        Route::post('tasks/{task}/unassign', [TaskController::class, 'unassign'])
            ->name('tasks.unassign')
            ->middleware('permission:assign-task');

        Route::post('tasks/assign-multiple', [TaskController::class, 'assignMultiple'])
            ->name('tasks.assign-multiple')
            ->middleware('permission:assign-task');

        Route::post('tasks/{task}/transition', [TaskController::class, 'transition'])
            ->name('tasks.transition')
            ->middleware('permission:edit-task');

        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])
            ->name('tasks.update-status')
            ->middleware('permission:edit-task');

        Route::patch('tasks/{task}/progress', [TaskController::class, 'updateProgress'])
            ->name('tasks.update-progress')
            ->middleware('permission:edit-task');

        Route::post('tasks/{task}/restore', [TaskController::class, 'restore'])
            ->name('tasks.restore')
            ->middleware('permission:edit-task');

        Route::delete('tasks/{task}/force', [TaskController::class, 'forceDelete'])
            ->name('tasks.force-delete')
            ->middleware('permission:delete-task');

        // ====== TASKS UNDER PHASE ======
        Route::get('phases/{phase}/tasks', [TaskController::class, 'index'])
            ->name('phases.tasks.index')
            ->middleware('permission:view-tasks');

        Route::get('phases/{phase}/tasks/create', [TaskController::class, 'create'])
            ->name('phases.tasks.create')
            ->middleware('permission:create-task');

        Route::post('phases/{phase}/tasks', [TaskController::class, 'store'])
            ->name('phases.tasks.store')
            ->middleware('permission:create-task');

        // ====== PROJECT TEMPLATES - VIEW ONLY ======
        Route::get('templates', [ProjectTemplateController::class, 'index'])
            ->name('templates.index')
            ->middleware('permission:view-templates');

        // ============================================================
        // ====== PROGRESS UPDATE ======
        // ============================================================
        Route::post('phases/{phase}/update-progress', [PhaseController::class, 'updateProgress'])
            ->name('phases.update-progress')
            ->middleware('permission:edit-phase');

        Route::post('projects/{project}/update-progress', [PhaseController::class, 'updateProjectProgress'])
            ->name('projects.update-progress')
            ->middleware('permission:edit-project');
    });

// ============================================================
// ========== ADMIN ONLY ROUTES - Requires access-admin ==========
// ============================================================
Route::middleware(['auth', 'permission:access-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // ====== POSTS (Example) ======
        Route::get('posts', function () {
            return view('posts');
        })->name('posts.index');

        // ====== ROLES & PERMISSIONS ======
        Route::resource('roles', RoleController::class);

        Route::resource('permissions', PermissionController::class)
            ->middleware('permission:configure-system');

        // ====== TEMPLATES - CREATE, EDIT, DELETE ======
        Route::get('templates/create', [ProjectTemplateController::class, 'create'])
            ->name('templates.create')
            ->middleware('permission:create-template');

        Route::post('templates', [ProjectTemplateController::class, 'store'])
            ->name('templates.store')
            ->middleware('permission:create-template');

        Route::get('templates/{template}/edit', [ProjectTemplateController::class, 'edit'])
            ->name('templates.edit')
            ->middleware('permission:edit-template');

        Route::put('templates/{template}', [ProjectTemplateController::class, 'update'])
            ->name('templates.update')
            ->middleware('permission:edit-template');

        Route::delete('templates/{template}', [ProjectTemplateController::class, 'destroy'])
            ->name('templates.destroy')
            ->middleware('permission:delete-template');

        // ====== PROJECTS - EDIT, DELETE ======
        Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])
            ->name('projects.edit')
            ->middleware('permission:edit-project');

        Route::put('projects/{project}', [ProjectController::class, 'update'])
            ->name('projects.update')
            ->middleware('permission:edit-project');

        Route::delete('projects/{project}', [ProjectController::class, 'destroy'])
            ->name('projects.destroy')
            ->middleware('permission:delete-project');

        // ====== PHASES - EDIT, DELETE ======
        Route::get('phases/{phase}/edit', [PhaseController::class, 'edit'])
            ->name('phases.edit')
            ->middleware('permission:edit-phase');

        Route::put('phases/{phase}', [PhaseController::class, 'update'])
            ->name('phases.update')
            ->middleware('permission:edit-phase');

        Route::delete('phases/{phase}', [PhaseController::class, 'destroy'])
            ->name('phases.destroy')
            ->middleware('permission:delete-phase');

        Route::patch('phases/{phase}/status', [PhaseController::class, 'updateStatus'])
            ->name('phases.update-status')
            ->middleware('permission:edit-phase');

        Route::post('phases/{phase}/restore', [PhaseController::class, 'restore'])
            ->name('phases.restore')
            ->middleware('permission:edit-phase');

        Route::delete('phases/{phase}/force', [PhaseController::class, 'forceDelete'])
            ->name('phases.force-delete')
            ->middleware('permission:delete-phase');

        Route::post('projects/{project}/phases/reorder', [PhaseController::class, 'reorder'])
            ->name('projects.phases.reorder')
            ->middleware('permission:reorder-phases');

        // ============================================================
        // ====== ORGANIZATION - FULL CRUD (Admin Only) ======
        // ============================================================
        Route::prefix('organization')
            ->name('organization.')
            ->group(function () {

                // ===== TEAMS =====
                Route::get('teams', [TeamController::class, 'index'])
                    ->name('teams.index')
                    ->middleware('permission:view-teams');

                Route::get('teams/create', [TeamController::class, 'create'])
                    ->name('teams.create')
                    ->middleware('permission:manage-teams');

                Route::post('teams', [TeamController::class, 'store'])
                    ->name('teams.store')
                    ->middleware('permission:manage-teams');

                Route::get('teams/{team}', [TeamController::class, 'show'])
                    ->name('teams.show')
                    ->middleware('permission:view-teams');

                Route::get('teams/{team}/edit', [TeamController::class, 'edit'])
                    ->name('teams.edit')
                    ->middleware('permission:manage-teams');

                Route::put('teams/{team}', [TeamController::class, 'update'])
                    ->name('teams.update')
                    ->middleware('permission:manage-teams');

                Route::delete('teams/{team}', [TeamController::class, 'destroy'])
                    ->name('teams.destroy')
                    ->middleware('permission:manage-teams');

                // ===== MEMBERS =====
                Route::get('members', [MemberController::class, 'index'])
                    ->name('members.index')
                    ->middleware('permission:view-members');

                Route::get('members/create', [MemberController::class, 'create'])
                    ->name('members.create')
                    ->middleware('permission:manage-members');

                Route::post('members', [MemberController::class, 'store'])
                    ->name('members.store')
                    ->middleware('permission:manage-members');

                Route::get('members/{member}', [MemberController::class, 'show'])
                    ->name('members.show')
                    ->middleware('permission:view-members');

                Route::get('members/{member}/edit', [MemberController::class, 'edit'])
                    ->name('members.edit')
                    ->middleware('permission:manage-members');

                Route::put('members/{member}', [MemberController::class, 'update'])
                    ->name('members.update')
                    ->middleware('permission:manage-members');

                Route::delete('members/{member}', [MemberController::class, 'destroy'])
                    ->name('members.destroy')
                    ->middleware('permission:manage-members');

                // ===== DIRECTORS =====
                Route::get('directors', [DirectorController::class, 'index'])
                    ->name('directors.index')
                    ->middleware('permission:view-directors');

                Route::get('directors/create', [DirectorController::class, 'create'])
                    ->name('directors.create')
                    ->middleware('permission:manage-directors');

                Route::post('directors', [DirectorController::class, 'store'])
                    ->name('directors.store')
                    ->middleware('permission:manage-directors');

                Route::get('directors/{director}', [DirectorController::class, 'show'])
                    ->name('directors.show')
                    ->middleware('permission:view-directors');

                Route::get('directors/{director}/edit', [DirectorController::class, 'edit'])
                    ->name('directors.edit')
                    ->middleware('permission:manage-directors');

                Route::put('directors/{director}', [DirectorController::class, 'update'])
                    ->name('directors.update')
                    ->middleware('permission:manage-directors');

                Route::delete('directors/{director}', [DirectorController::class, 'destroy'])
                    ->name('directors.destroy')
                    ->middleware('permission:manage-directors');

                // ===== TEAM LEADERS =====
                Route::get('team-leaders', [TeamLeaderController::class, 'index'])
                    ->name('team-leaders.index')
                    ->middleware('permission:view-team-leaders');

                Route::get('team-leaders/create', [TeamLeaderController::class, 'create'])
                    ->name('team-leaders.create')
                    ->middleware('permission:manage-team-leaders');

                Route::post('team-leaders', [TeamLeaderController::class, 'store'])
                    ->name('team-leaders.store')
                    ->middleware('permission:manage-team-leaders');

                Route::get('team-leaders/{teamLeader}', [TeamLeaderController::class, 'show'])
                    ->name('team-leaders.show')
                    ->middleware('permission:view-team-leaders');

                Route::get('team-leaders/{teamLeader}/edit', [TeamLeaderController::class, 'edit'])
                    ->name('team-leaders.edit')
                    ->middleware('permission:manage-team-leaders');

                Route::put('team-leaders/{teamLeader}', [TeamLeaderController::class, 'update'])
                    ->name('team-leaders.update')
                    ->middleware('permission:manage-team-leaders');

                Route::delete('team-leaders/{teamLeader}', [TeamLeaderController::class, 'destroy'])
                    ->name('team-leaders.destroy')
                    ->middleware('permission:manage-team-leaders');
            });

        // ====== ACTIVITY LOG ======
        Route::get('activity', [ActivityController::class, 'index'])
            ->name('activity.index')
            ->middleware('permission:view-audit-logs');

        Route::get('activity/user/{user}', [ActivityController::class, 'user'])
            ->name('activity.user')
            ->middleware('permission:view-audit-logs');

        Route::get('activity/project/{project}', [ActivityController::class, 'project'])
            ->name('activity.project')
            ->middleware('permission:view-audit-logs');

        Route::get('activity/task/{task}', [ActivityController::class, 'task'])
            ->name('activity.task')
            ->middleware('permission:view-audit-logs');
    });
