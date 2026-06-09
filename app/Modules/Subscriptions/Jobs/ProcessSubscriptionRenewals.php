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
        $expired = $subscriptionService->processExpiredSubscriptions();
        $renewed = $subscriptionService->processDueRenewals();

        Log::info("Subscription processing: {$expired} expired, {$renewed} renewed");
    }
}
