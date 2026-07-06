<?php

namespace App\Modules\Subscriptions\Services;

use App\Models\User;
use App\Modules\Subscriptions\Models\Subscription;

class SubscriptionEnforcementService
{
    public function canAccess(User $user, string $capability): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $subscription = $this->getEffectiveSubscription($user);

        if (! $subscription) {
            return false;
        }

        if ($subscription->isActive()) {
            return true;
        }

        if ($subscription->isOnTrial()) {
            return $this->trialCapabilities($capability);
        }

        if ($subscription->isInGracePeriod()) {
            return $this->graceCapabilities($capability);
        }

        return false;
    }

    public function requireActiveSubscription(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->hasActiveSubscription();
    }

    public function getDenialMessage(string $capability): string
    {
        return match ($capability) {
            'borrow' => 'An active subscription is required to borrow books.',
            'add_books' => 'An active subscription is required to add books to the catalog.',
            'register_members' => 'An active subscription is required to register new members.',
            'upload_assets' => 'An active subscription is required to upload digital assets.',
            default => 'An active subscription is required to access this feature.',
        };
    }

    protected function getEffectiveSubscription(User $user): ?Subscription
    {
        return Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'trial', 'expired'])
            ->latest('id')
            ->first();
    }

    protected function trialCapabilities(string $capability): bool
    {
        return in_array($capability, ['borrow', 'view'], true);
    }

    protected function graceCapabilities(string $capability): bool
    {
        return in_array($capability, ['view', 'export'], true);
    }
}
