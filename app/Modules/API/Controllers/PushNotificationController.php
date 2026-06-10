<?php

namespace App\Modules\API\Controllers;

use App\Models\PushSubscription;
use App\Modules\Communication\Models\NotificationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PushNotificationController extends Controller
{
    /**
     * Get the VAPID public key so the client can subscribe.
     */
    public function vapidKey(): JsonResponse
    {
        $publicKey = config('services.vapid.public_key');

        if (!$publicKey) {
            return response()->json(['error' => 'VAPID keys not configured. Run `php artisan vapid:generate`.'], 503);
        }

        return response()->json(['public_key' => $publicKey]);
    }

    /**
     * Subscribe: store or update the user's push subscription.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:2000'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $endpointHash = hash('sha256', $data['endpoint']);
        $userId = $request->user()->id;

        $subscription = PushSubscription::updateOrCreate(
            ['user_id' => $userId, 'endpoint_hash' => $endpointHash],
            [
                'endpoint' => $data['endpoint'],
                'p256dh' => $data['keys']['p256dh'],
                'auth' => $data['keys']['auth'],
                'user_agent' => $request->userAgent(),
                'expires_at' => $data['expires_at'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Push subscription saved.',
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Unsubscribe: delete a specific subscription by endpoint.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:2000'],
        ]);

        $endpointHash = hash('sha256', $data['endpoint']);

        $deleted = PushSubscription::forUser($request->user()->id)
            ->where('endpoint_hash', $endpointHash)
            ->delete();

        return response()->json([
            'success' => $deleted > 0,
            'message' => $deleted ? 'Push subscription removed.' : 'Subscription not found.',
        ]);
    }

    /**
     * Unsubscribe from all push notifications for the current user.
     */
    public function unsubscribeAll(Request $request): JsonResponse
    {
        $count = PushSubscription::forUser($request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} push subscription(s) removed.",
        ]);
    }

    /**
     * Get all active subscriptions for the current user.
     */
    public function subscriptions(Request $request): JsonResponse
    {
        $subscriptions = PushSubscription::forUser($request->user()->id)
            ->active()
            ->get(['id', 'user_agent', 'created_at']);

        return response()->json([
            'subscriptions' => $subscriptions,
            'count' => $subscriptions->count(),
        ]);
    }

    /**
     * Get the user's push notification preferences.
     */
    public function preferences(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'push_enabled' => PushSubscription::forUser($user->id)->active()->exists(),
            'subscription_count' => PushSubscription::forUser($user->id)->active()->count(),
            'notifications_configured' => config('services.vapid.public_key') !== null,
        ]);
    }
}
