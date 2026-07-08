<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionIdleTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && session()->has('last_activity_at')) {
            $lastActivity = session('last_activity_at');
            $idleMinutes = (int) (config('auth.idle_timeout_minutes', 30));

            if ($lastActivity instanceof \DateTimeImmutable) {
                $diff = $lastActivity->diffInMinutes(now());
            } else {
                $diff = now()->diffInMinutes(\Carbon\Carbon::parse($lastActivity));
            }

            if ($diff >= $idleMinutes) {
                auth()->logout();
                session()->invalidate();
                session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Session expired due to inactivity.'], 401);
                }

                return redirect()->route('login')->with('status', 'Your session expired due to inactivity. Please log in again.');
            }
        }

        session(['last_activity_at' => now()]);

        return $next($request);
    }
}
