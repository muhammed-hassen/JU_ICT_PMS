<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Throwable;

/**
 * Answers "would this user get past the route's permission middleware?"
 * without making the request.
 *
 * The sidebar and page links use it so a link is only shown when the page
 * behind it would actually open. Without it, the menu's own 'can' list and the
 * routes' middleware drift apart, and users see links that end in a 403.
 */
class RouteAccess
{
    public static function allows(?string $url, string $method = 'GET'): bool
    {
        $user = auth()->user();
        if (! $user || ! $url) {
            return false;
        }

        try {
            $route = Route::getRoutes()->match(Request::create($url, $method));
        } catch (Throwable) {
            return false;
        }

        foreach ($route->gatherMiddleware() as $middleware) {
            if (! is_string($middleware) || ! str_starts_with($middleware, 'permission:')) {
                continue;
            }

            // "permission:a|b" passes with any one of them; separate middleware must all pass.
            $abilities = explode('|', explode(',', substr($middleware, strlen('permission:')))[0]);
            if (! $user->hasAnyPermission($abilities)) {
                return false;
            }
        }

        return true;
    }
}
