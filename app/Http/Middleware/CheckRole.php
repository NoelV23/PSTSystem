<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Usage: middleware('role:admin,manager')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $allowed = in_array($user->role, $roles, true);

        if (!$allowed) {
            // For API endpoints, return 403; for web, redirect to dashboard or sales for staff
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
            if ($user->role === 'staff') {
                return redirect()->route('sales.index');
            }
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}


