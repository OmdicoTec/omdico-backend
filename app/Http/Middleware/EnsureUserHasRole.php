<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // type admin, customer & seller
        // but can is special command for check sub
        // $request->user()->hasRole($role);

        if (!$request->user()->hasRole($roles)) {
            return response()->json([
                'message' => 'You are not authorized to access this resource',
            ], Response::HTTP_FORBIDDEN);
        }
        return $next($request);
    }
}
