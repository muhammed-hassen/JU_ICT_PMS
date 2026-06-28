<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectTemplateController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Organization\DirectorController;
use App\Http\Controllers\Organization\MemberController;
use App\Http\Controllers\Organization\TeamController;
use App\Http\Controllers\Organization\TeamLeaderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

Auth::routes(['register' => false]);

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'permission:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('posts', function () {
        return view('posts');
    })->name('posts.index');

    Route::resource('roles', RoleController::class)
        ->except('show')
        ->middleware('permission:assign-role');

    Route::resource('permissions', PermissionController::class)
        ->except('show')
        ->middleware('permission:configure-system');

    Route::get('templates', [ProjectTemplateController::class, 'index'])
        ->name('templates.index')
        ->middleware('permission:view-templates');

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

    Route::prefix('organization')->name('organization.')->group(function () {
        Route::get('teams', [TeamController::class, 'index'])
            ->name('teams.index')
            ->middleware('permission:view-all-teams|view-team-members');
        Route::get('teams/create', [TeamController::class, 'create'])
            ->name('teams.create')
            ->middleware('permission:create-team');
        Route::post('teams', [TeamController::class, 'store'])
            ->name('teams.store')
            ->middleware('permission:create-team');
        Route::get('teams/{team}', [TeamController::class, 'show'])
            ->name('teams.show')
            ->middleware('permission:view-all-teams|view-team-members');
        Route::get('teams/{team}/edit', [TeamController::class, 'edit'])
            ->name('teams.edit')
            ->middleware('permission:edit-team|assign-team-leader|manage-team-members');
        Route::put('teams/{team}', [TeamController::class, 'update'])
            ->name('teams.update')
            ->middleware('permission:edit-team|assign-team-leader|manage-team-members');
        Route::delete('teams/{team}', [TeamController::class, 'destroy'])
            ->name('teams.destroy')
            ->middleware('permission:delete-team');

        Route::get('members', [MemberController::class, 'index'])
            ->name('members.index')
            ->middleware('permission:view-team-members');
        Route::get('members/create', [MemberController::class, 'create'])
            ->name('members.create')
            ->middleware('permission:assign-team-member|manage-team-members');
        Route::post('members', [MemberController::class, 'store'])
            ->name('members.store')
            ->middleware('permission:assign-team-member|manage-team-members');
        Route::get('members/{member}', [MemberController::class, 'show'])
            ->name('members.show')
            ->middleware('permission:view-team-members');
        Route::get('members/{member}/edit', [MemberController::class, 'edit'])
            ->name('members.edit')
            ->middleware('permission:manage-team-members|transfer-team-member');
        Route::put('members/{member}', [MemberController::class, 'update'])
            ->name('members.update')
            ->middleware('permission:manage-team-members|transfer-team-member');
        Route::delete('members/{member}', [MemberController::class, 'destroy'])
            ->name('members.destroy')
            ->middleware('permission:remove-team-member|manage-team-members');

        Route::get('directors', [DirectorController::class, 'index'])
            ->name('directors.index')
            ->middleware('permission:view-organization-structure');
        Route::get('directors/create', [DirectorController::class, 'create'])
            ->name('directors.create')
            ->middleware('permission:manage-organization-structure');
        Route::post('directors', [DirectorController::class, 'store'])
            ->name('directors.store')
            ->middleware('permission:manage-organization-structure');
        Route::get('directors/{director}', [DirectorController::class, 'show'])
            ->name('directors.show')
            ->middleware('permission:view-organization-structure');
        Route::get('directors/{director}/edit', [DirectorController::class, 'edit'])
            ->name('directors.edit')
            ->middleware('permission:manage-organization-structure');
        Route::put('directors/{director}', [DirectorController::class, 'update'])
            ->name('directors.update')
            ->middleware('permission:manage-organization-structure');
        Route::delete('directors/{director}', [DirectorController::class, 'destroy'])
            ->name('directors.destroy')
            ->middleware('permission:manage-organization-structure');

        Route::get('team-leaders', [TeamLeaderController::class, 'index'])
            ->name('team-leaders.index')
            ->middleware('permission:view-all-teams|view-team-members');
        Route::get('team-leaders/create', [TeamLeaderController::class, 'create'])
            ->name('team-leaders.create')
            ->middleware('permission:assign-team-leader');
        Route::post('team-leaders', [TeamLeaderController::class, 'store'])
            ->name('team-leaders.store')
            ->middleware('permission:assign-team-leader');
        Route::get('team-leaders/{teamLeader}', [TeamLeaderController::class, 'show'])
            ->name('team-leaders.show')
            ->middleware('permission:view-all-teams|view-team-members');
        Route::get('team-leaders/{teamLeader}/edit', [TeamLeaderController::class, 'edit'])
            ->name('team-leaders.edit')
            ->middleware('permission:assign-team-leader|manage-team-members');
        Route::put('team-leaders/{teamLeader}', [TeamLeaderController::class, 'update'])
            ->name('team-leaders.update')
            ->middleware('permission:assign-team-leader|manage-team-members');
        Route::delete('team-leaders/{teamLeader}', [TeamLeaderController::class, 'destroy'])
            ->name('team-leaders.destroy')
            ->middleware('permission:assign-team-leader');
    });

    Route::get('projects', [ProjectController::class, 'index'])
        ->name('projects.index')
        ->middleware('permission:view-all-projects|view-team-projects|view-own-projects');

    Route::get('projects/create', [ProjectController::class, 'create'])
        ->name('projects.create')
        ->middleware('permission:create-project');

    Route::post('projects', [ProjectController::class, 'store'])
        ->name('projects.store')
        ->middleware('permission:create-project');

    Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])
        ->name('projects.edit')
        ->middleware('permission:edit-project|edit-own-project');

    Route::put('projects/{project}', [ProjectController::class, 'update'])
        ->name('projects.update')
        ->middleware('permission:edit-project|edit-own-project');

    Route::delete('projects/{project}', [ProjectController::class, 'destroy'])
        ->name('projects.destroy')
        ->middleware('permission:delete-project');

    Route::get('projects/{project}', [ProjectController::class, 'show'])
        ->name('projects.show')
        ->middleware('permission:view-all-projects|view-team-projects|view-own-projects');
});
