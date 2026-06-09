<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if ($user->hasActiveSubscription()) {
            return $next($request);
        }

        return redirect()->route('subscriptions.plans')
            ->with('error', 'An active subscription is required to access this feature.');
    }
}
