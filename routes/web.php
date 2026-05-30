<?php

use App\Http\Controllers\Admin\PermissionController;
<<<<<<< HEAD
use App\Http\Controllers\Admin\ProjectController;
=======
>>>>>>> 3549e1438c9e96b64732315126aea95c51142e5e
use App\Http\Controllers\Admin\ProjectTemplateController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\HomeController;
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
<<<<<<< HEAD

    // ========== PROJECT MANAGEMENT ROUTES (Add these) ==========
    Route::resource('projects', ProjectController::class)
        ->except('show')
        ->middleware('permission:view-projects|create-projects|edit-projects|delete-projects');

    Route::get('projects/{project}', [ProjectController::class, 'show'])
        ->name('projects.show')
        ->middleware('permission:view-projects');
=======
>>>>>>> 3549e1438c9e96b64732315126aea95c51142e5e
});
