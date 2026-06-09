<?php

namespace App\Modules\Communication\Services;

use App\Models\User;
use App\Modules\Communication\Models\NotificationLog;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = config('services.push.enabled', false);
    }

    public function send(User $user, string $title, string $body, array $data = []): bool
    {
        if (!$this->enabled) {
            $this->log($user, $title, $body, 'Push notifications disabled', 'failed');
            return false;
        }

        try {
            $result = $this->sendViaServiceWorker($user, $title, $body, $data);

            $this->log($user, $title, $body, null, $result ? 'sent' : 'failed');
            return $result;
        } catch (\Throwable $e) {
            $this->log($user, $title, $body, $e->getMessage(), 'failed');
            Log::error("Push notification failed for user {$user->id}: {$e->getMessage()}");
            return false;
        }
    }

    public function sendBulk(array $users, string $title, string $body, array $data = []): array
    {
        $results = [];
        foreach ($users as $user) {
            $results[$user->id] = $this->send($user, $title, $body, $data);
        }
        return $results;
    }

    protected function sendViaServiceWorker(User $user, string $title, string $body, array $data = []): bool
    {
        if (!$user->email) return false;

        try {
            \Illuminate\Support\Facades\Mail::to($user)->queue(
                new \App\Mail\NotificationMail($title, $body)
            );
            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    public function sendToAll(string $title, string $body, array $data = []): int
    {
        $count = 0;
        User::where('is_active', true)->chunk(100, function ($users) use ($title, $body, $data, &$count) {
            foreach ($users as $user) {
                if ($this->send($user, $title, $body, $data)) {
                    $count++;
                }
            }
        });
        return $count;
    }

    protected function log(User $user, string $title, string $body, ?string $error = null, string $status = 'sent'): void
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
