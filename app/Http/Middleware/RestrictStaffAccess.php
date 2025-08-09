<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictStaffAccess
{
    /**
     * Handle an incoming request.
     *
     * Staff users can only access the Sales and Products pages on web routes.
     * API routes (api/*) are not restricted here to avoid breaking page AJAX.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        // Allow all non-staff users
        if ($user->role !== 'staff') {
            return $next($request);
        }

        // Allow API routes (handled by controllers/policies separately if needed)
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Allowed web paths for staff
        $allowedPaths = [
            'sales',
            'products',
        ];

        // Normalize current path (without leading slash)
        $path = ltrim($request->path(), '/');

        // If not exactly one of the allowed top-level pages, redirect to sales
        $isAllowed = in_array($path, $allowedPaths, true);
        if (!$isAllowed) {
            return redirect()->route('sales.index');
        }

        return $next($request);
    }
}


