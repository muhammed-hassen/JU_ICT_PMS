<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectTemplateController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
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
            ->except('show');

        Route::resource('templates', ProjectTemplateController::class);

        Route::resource('projects', ProjectController::class);

});
