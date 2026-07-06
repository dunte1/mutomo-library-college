<?php

namespace App\Modules\Subscriptions\Jobs;

use App\Modules\Subscriptions\Services\SubscriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessSubscriptionRenewals implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle(SubscriptionService $subscriptionService): void
    {
        // Process expired subscriptions and send expiration notices
        $expired = $subscriptionService->processExpiredSubscriptions();

        // Process trial expirations
        $trialsExpired = $subscriptionService->processTrialExpirations();

        // Process grace period expirations
        $graceExpired = $subscriptionService->processGracePeriodExpirations();

        // Process auto-renewals
        $renewed = $subscriptionService->processDueRenewals();

        // Send expiring soon notifications (7 days before expiry)
        $expiringSoonNotified = $subscriptionService->sendExpiringSoonNotifications(7);

        Log::info("Subscription processing: {$expired} expired, {$trialsExpired} trials expired, {$graceExpired} grace expired, {$renewed} renewed, {$expiringSoonNotified} expiring-soon notified");
    }
}
