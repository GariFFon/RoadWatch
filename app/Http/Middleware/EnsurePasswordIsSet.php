<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsSet
{
    /**
     * Redirect Google OAuth users who haven't set a password yet
     * to the mandatory set-password screen before they can proceed.
     *
     * Add this middleware to all protected routes so Google users
     * can't bypass the password setup step.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only check authenticated Google users
        if ($user && $user->needsPasswordSetup()) {
            // Allow access to the set-password routes themselves (avoid infinite redirect)
            if ($request->routeIs('password.setup', 'password.setup.store', 'logout')) {
                return $next($request);
            }

            return redirect()->route('password.setup')
                ->with('warning', 'Please set a password to continue using RoadWatch.');
        }

        return $next($request);
    }
}
