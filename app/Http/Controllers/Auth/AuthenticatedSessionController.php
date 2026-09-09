<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditLogService;
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
    public function store(LoginRequest $request, AuditLogService $auditLog): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Reset activity so SessionTimeout does not treat a prior idle stamp as expired.
        $request->user()?->forceFill(['last_activity_at' => now()])->save();
        $request->session()->put('last_activity_at', now()->timestamp);

        $auditLog->log($request->user(), 'auth.login', $request->user(), null, [
            'login_as' => $request->input('login_as', 'staff'),
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $auditLog->log($request->user(), 'auth.logout', $request->user());

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
