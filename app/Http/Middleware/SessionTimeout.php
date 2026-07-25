<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $timeoutMinutes = max(1, (int) config('session.lifetime', 120));
        $now = now()->timestamp;

        // Prefer per-session activity (avoids kicking users out on fresh login
        // because of an old last_activity_at value in the database).
        $lastActivity = (int) $request->session()->get('last_activity_at', $now);
        $idleMinutes = (int) floor(($now - $lastActivity) / 60);

        if ($idleMinutes >= $timeoutMinutes) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your session has expired. Please sign in again.',
            ]);
        }

        $request->session()->put('last_activity_at', $now);
        $user->forceFill(['last_activity_at' => now()])->saveQuietly();

        return $next($request);
    }
}
