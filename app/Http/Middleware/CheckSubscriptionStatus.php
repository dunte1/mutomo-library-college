<?php

namespace App\Http\Middleware;

use App\Modules\Subscriptions\Models\Subscription;
use App\Modules\Subscriptions\Services\SubscriptionEnforcementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    protected string $capability = 'view';

    public function handle(Request $request, Closure $next, ?string $capability = null): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $capability = $capability ?? $this->capability;

        $service = app(SubscriptionEnforcementService::class);

        $subscription = Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'trial', 'expired'])
            ->latest('id')
            ->first();

        if (! $subscription) {
            return redirect()->route('subscriptions.plans')
                ->with('error', 'A subscription is required to access this feature.');
        }

        if ($subscription->isActive()) {
            return $next($request);
        }

        if ($subscription->isOnTrial()) {
            if ($capability === 'view') {
                return $next($request);
            }

            return redirect()->route('subscriptions.plans')
                ->with('error', 'Upgrade your trial to access this feature.');
        }

        if ($subscription->isInGracePeriod()) {
            if (in_array($capability, ['view', 'export'], true)) {
                return $next($request);
            }

            return redirect()->route('subscriptions.plans')
                ->with('error', 'Your subscription has expired. Renew to regain full access.');
        }

        return redirect()->route('subscriptions.plans')
            ->with('error', $service->getDenialMessage($capability));
    }
}
