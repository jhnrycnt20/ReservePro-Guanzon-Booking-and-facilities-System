<?php

namespace App\Http\Middleware;

use App\Helpers\RoleRedirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect()->to(RoleRedirect::dashboardRoute(Auth::guard($guard)->user()));
            }
        }

        return $next($request);
    }
}
