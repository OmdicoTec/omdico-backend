<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Http\Resources\v1\UserLogin;
use App\Interface\MustVerifyMobile;
// use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

class EnsureMobileIsVerifiedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role = null, $redirectToRoute = null): Response
    {
        if (! $request->user() ||
            ($request->user() instanceof MustVerifyMobile && ! $request->user()->hasVerifiedMobile())) {
                return response()->json([
                    'message' => 'شماره موبایل شما تایید نشده است.',
                    'user_status' => new UserLogin($request->user())
                ], 403);
        }
        if ($role === "all"){
            if (! $request->user()->token()->hasVerifiedMobile()){
                return response()->json([
                    'message' => 'لطفا از کد ارسال شده به موبایل خود استفاده کنید برای ورود حساب کاربری خود.',
                    'user_status' => new UserLogin($request->user())
                ], 403);
            }
        }

        return $next($request);
    }
}
