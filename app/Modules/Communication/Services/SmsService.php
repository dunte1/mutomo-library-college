<?php

namespace App\Modules\Communication\Services;

use App\Modules\Communication\Models\NotificationLog;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $provider;
    protected bool $enabled;

    public function __construct()
    {
        $this->provider = config('services.sms.provider', 'log');
        $this->enabled = config('services.sms.enabled', false);
    }

    public function send(string $phoneNumber, string $message, array $options = []): bool
    {
        if (!$this->enabled) {
            $this->log('log', $phoneNumber, $message, 'SMS disabled');
            return false;
        }

        try {
            $result = match ($this->provider) {
                'twilio' => $this->sendViaTwilio($phoneNumber, $message),
                'africastalking' => $this->sendViaAfricaSTalking($phoneNumber, $message),
                default => $this->sendViaLog($phoneNumber, $message),
            };

            $this->log($this->provider, $phoneNumber, $message, null, $result ? 'sent' : 'failed');
            return $result;
        } catch (\Throwable $e) {
            $this->log($this->provider, $phoneNumber, $message, $e->getMessage(), 'failed');
            Log::error("SMS send failed: {$e->getMessage()}");
            return false;
        }
    }

    public function sendBulk(array $recipients, string $message): array
    {
        $results = [];
        foreach ($recipients as $phoneNumber) {
            $results[$phoneNumber] = $this->send($phoneNumber, $message);
        }
        return $results;
    }

    protected function sendViaLog(string $phoneNumber, string $message): bool
    {
        Log::info("SMS to {$phoneNumber}: {$message}");
        return true;
    }

    protected function sendViaTwilio(string $phoneNumber, string $message): bool
    {
        throw new \RuntimeException('Twilio integration not configured. Set SMS_PROVIDER=log to use log driver.');
    }

    protected function sendViaAfricaSTalking(string $phoneNumber, string $message): bool
    {
        throw new \RuntimeException('AfricaSTalking integration not configured. Set SMS_PROVIDER=log to use log driver.');
    }

    protected function log(string $provider, string $phoneNumber, string $message, ?string $error = null, string $status = 'sent'): void
    {
        try {
            NotificationLog::create([
                'notifiable_type' => 'SMS',
                'notifiable_id' => 0,
                'channel' => 'sms',
                'type' => 'outbound',
                'title' => "SMS to {$phoneNumber}",
                'body' => $message,
                'status' => $status,
                'sent_at' => now(),
                'error' => $error,
                'metadata' => ['provider' => $provider, 'phone' => $phoneNumber],
            ]);
        } catch (\Throwable $e) {
            Log::warning("Failed to log SMS notification: {$e->getMessage()}");
        }
    }
}
