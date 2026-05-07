<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

Auth::routes();

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
});
