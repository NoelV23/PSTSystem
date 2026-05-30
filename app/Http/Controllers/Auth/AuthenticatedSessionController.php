<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Support\UserActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Block inactive users immediately after authentication
        if (Auth::user() && (Auth::user()->status ?? null) === 'inactive') {
            UserActivity::record(Auth::id(), 'auth.blocked_inactive', 'Inactive account blocked after credentials verified.', [
                'email' => Auth::user()->email,
            ]);
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Your account is inactive. Please contact the administrator.']);
        }

        UserActivity::record(Auth::id(), 'auth.login', 'Signed in successfully.');

        if (Auth::user()->role === 'staff') {
            return redirect()->intended(route('sales.index', absolute: false));
        } else {
            return redirect()->intended(route('dashboard', absolute: false));
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $userId = $request->user()?->id;
        UserActivity::record($userId, 'auth.logout', 'Signed out.');

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
