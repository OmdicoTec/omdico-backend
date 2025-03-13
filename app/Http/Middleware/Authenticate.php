<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // if request from api version 1
        // return $request->expectsJson() ? null : route('login');
        if ($request->is('api/v2/*')) {
            return route('unAuthorized');
        }
        // if request from web
        return $request->expectsJson() ? null : route('unAuthorized');
    }
}
