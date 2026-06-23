<?php

namespace App\Modules\Communication\Services;

use App\Models\PushSubscription;
use App\Models\User;
use App\Modules\Communication\Models\NotificationLog;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotificationService
{
    protected bool $enabled;

    protected ?WebPush $webPush = null;

    public function __construct()
    {
        $this->enabled = config('services.vapid.public_key') !== null
            && config('services.vapid.private_key') !== null;
    }

    public function send(User $user, string $title, string $body, array $data = []): bool
    {
        if (! $this->enabled) {
            $this->log($user, $title, $body, 'VAPID keys not configured', 'failed');

            return false;
        }

        $subscriptions = PushSubscription::forUser($user->id)->active()->get();

        if ($subscriptions->isEmpty()) {
            $this->log($user, $title, $body, 'No active push subscriptions', 'failed');

            return false;
        }

        $payload = json_encode(array_merge([
            'title' => $title,
            'body' => $body,
            'icon' => '/icons/icon-192.png',
            'badge' => '/icons/icon-72.png',
            'timestamp' => now()->timestamp,
        ], $data));

        $webPush = $this->getWebPush();
        $sent = false;
        $errors = [];

        foreach ($subscriptions as $sub) {
            try {
                $pushSub = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'keys' => [
                        'p256dh' => $sub->p256dh,
                        'auth' => $sub->auth,
                    ],
                ]);

                $webPush->sendOneNotification($pushSub, $payload, ['TTL' => 86400]);
                $sent = true;
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
                if (str_contains($e->getMessage(), '410')) {
                    $sub->delete();
                    Log::info("Removed expired push subscription for user {$user->id}: {$sub->endpoint}");
                }
            }
        }

        try {
            $webPush->flush();
        } catch (\Throwable $e) {
        }

        $status = $sent ? 'sent' : 'failed';
        $errorMsg = $errors ? implode('; ', array_slice($errors, 0, 3)) : null;
        $this->log($user, $title, $body, $errorMsg, $status);

        if (! $sent && ! empty($errors)) {
            Log::error("Push notification failed for user {$user->id}: ".implode('; ', $errors));
        }

        return $sent;
    }

    public function sendBulk(array $users, string $title, string $body, array $data = []): array
    {
        $results = [];
        foreach ($users as $user) {
            $results[$user->id] = $this->send($user, $title, $body, $data);
        }

        return $results;
    }

    public function sendToAll(string $title, string $body, array $data = []): int
    {
        $count = 0;
        $userIds = PushSubscription::active()->distinct()->pluck('user_id');
        User::whereIn('id', $userIds)->where('is_active', true)->chunk(100, function ($users) use ($title, $body, $data, &$count) {
            foreach ($users as $user) {
                if ($this->send($user, $title, $body, $data)) {
                    $count++;
                }
            }
        });

        return $count;
    }

    public function testSend(User $user): bool
    {
        return $this->send($user, 'Test Notification', 'Your push notifications are working correctly!', [
            'tag' => 'test_notification', 'requireInteraction' => true,
        ]);
    }

    protected function getWebPush(): WebPush
    {
        if ($this->webPush === null) {
            $this->webPush = new WebPush([
                'VAPID' => [
                    'subject' => config('app.url'),
                    'publicKey' => config('services.vapid.public_key'),
                    'privateKey' => config('services.vapid.private_key'),
                ],
            ]);
            $this->webPush->setAutomaticPadding(false);
            $this->webPush->setReuseVAPIDHeaders(true);
        }

        return $this->webPush;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function activeSubscriberCount(): int
    {
        return PushSubscription::active()->distinct('user_id')->count('user_id');
    }

    public function log(User $user, string $title, string $body, ?string $error = null, string $status = 'sent'): void
    {
        try {
            NotificationLog::create([
                'notifiable_type' => get_class($user),
                'notifiable_id' => $user->id,
                'channel' => 'push',
                'type' => 'push_notification',
                'title' => $title,
                'body' => $body,
                'status' => $status,
                'sent_at' => now(),
                'error' => $error,
            ]);
        } catch (\Throwable $e) {
            Log::warning("Failed to log push notification: {$e->getMessage()}");
        }
    }
}
