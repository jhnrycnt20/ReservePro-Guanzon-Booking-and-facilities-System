<?php

namespace App\Http\Middleware;

use App\Helpers\RoleRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage: middleware('role:admin,front_desk')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthorized.');
        }

        $allowed = collect($roles)
            ->flatMap(fn (string $role) => explode(',', $role))
            ->map(fn (string $role) => trim($role))
            ->filter()
            ->values();

        if ($allowed->isEmpty() || ! $allowed->contains($user->role?->slug)) {
            if ($request->expectsJson()) {
                abort(403, 'You do not have permission to access this resource.');
            }

            // Send signed-in users back to their own area instead of a dead-end 403 page.
            return redirect()
                ->to(RoleRedirect::dashboardPath($user))
                ->with('error', 'That page belongs to a different role. Use the Front Desk account for reservations and logs.');
        }

        return $next($request);
    }
}
