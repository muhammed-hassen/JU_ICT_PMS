<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectTemplateController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public route
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

// Authentication
Auth::routes(['register' => false]);

// Home
Route::get('/home', [HomeController::class, 'index'])->name('home');

// ================= USER CRUD =================
Route::middleware(['auth'])->group(function () {

    Route::resource('users', UserController::class);

});

// ================= ADMIN MODULES =================
Route::middleware(['auth', 'permission:access-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('posts', function () {
            return view('posts');
        })->name('posts.index');

        Route::resource('roles', RoleController::class)
            ->except('show');

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

    // ========== PROJECT MANAGEMENT ROUTES (Add these) ==========
    Route::resource('projects', ProjectController::class)
        ->except('show')
        ->middleware('permission:view-projects|create-projects|edit-projects|delete-projects');

    Route::get('projects/{project}', [ProjectController::class, 'show'])
        ->name('projects.show')
        ->middleware('permission:view-projects');
});
