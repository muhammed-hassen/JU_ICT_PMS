<?php

namespace App\Providers;

use App\Support\RouteAccess;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // @canvisit(route('admin.projects.edit', $project)) ... @endcanvisit
        // Shows a link only when the route's permission middleware would let the user in.
        Blade::if('canvisit', fn (?string $url, string $method = 'GET') => RouteAccess::allows($url, $method));
    }
}
