<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public route
Route::get('/', function () {
    return redirect('/users');
});
// Auth routes (ONLY ONCE)
Auth::routes();

// Home route
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Example admin page
Route::get('admin/posts', function () {
    return view('posts');
});

// Protected routes (auth + permissions)
Route::middleware(['auth'])->group(function () {

    // Users CRUD with permission control
    Route::resource('users', UserController::class);

});
