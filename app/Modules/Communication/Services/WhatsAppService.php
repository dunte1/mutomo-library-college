<?php

namespace App\Modules\Communication\Services;

use App\Modules\Communication\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $provider;

    protected bool $enabled;

    protected ?string $token;

    protected ?string $phoneNumberId;

    protected ?string $from;

    public function __construct()
    {
        $this->provider = config('services.whatsapp.provider', 'log');
        $this->enabled = config('services.whatsapp.enabled', false);
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->from = config('services.whatsapp.from');
    }

    public function send(string $phoneNumber, string $message, array $options = []): bool
    {
        if (! $this->enabled) {
            $this->log('log', $phoneNumber, $message, 'WhatsApp disabled');

            return false;
        }

        try {
            $result = match ($this->provider) {
                'cloud-api' => $this->sendViaCloudApi($phoneNumber, $message),
                default => $this->sendViaLog($phoneNumber, $message),
            };

            $this->log($this->provider, $phoneNumber, $message, null, $result ? 'sent' : 'failed');

            return $result;
        } catch (\Throwable $e) {
            $this->log($this->provider, $phoneNumber, $message, $e->getMessage(), 'failed');
            Log::error("WhatsApp send failed: {$e->getMessage()}");

            return false;
        }
    }

    public function sendTemplate(string $phoneNumber, string $templateName, array $parameters = [], string $language = 'en_US'): bool
    {
        if (! $this->enabled || $this->provider !== 'cloud-api') {
            return $this->send($phoneNumber, 'Template: '.$templateName);
        }

        try {
            $response = Http::withToken($this->token)
                ->post("https://graph.facebook.com/v22.0/{$this->phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $this->normalizePhone($phoneNumber),
                    'type' => 'template',
                    'template' => [
                        'name' => $templateName,
                        'language' => ['code' => $language],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => array_map(fn ($p) => ['type' => 'text', 'text' => (string) $p], $parameters),
                            ],
                        ],
                    ],
                ]);

            $success = $response->successful();
            $this->log('cloud-api', $phoneNumber, "Template: {$templateName}", null, $success ? 'sent' : 'failed');

            return $success;
        } catch (\Throwable $e) {
            $this->log('cloud-api', $phoneNumber, "Template: {$templateName}", $e->getMessage(), 'failed');
            Log::error("WhatsApp template send failed: {$e->getMessage()}");

            return false;
        }
    }

    protected function sendViaCloudApi(string $phoneNumber, string $message): bool
    {
        if (! $this->token || ! $this->phoneNumberId) {
            throw new \RuntimeException('WhatsApp Cloud API not configured. Set WHATSAPP_TOKEN and WHATSAPP_PHONE_NUMBER_ID in .env');
        }

        $response = Http::withToken($this->token)
            ->post("https://graph.facebook.com/v22.0/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $this->normalizePhone($phoneNumber),
                'type' => 'text',
                'text' => ['preview_url' => false, 'body' => $message],
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('WhatsApp API error: '.$response->body());
        }

        return true;
    }

    protected function sendViaLog(string $phoneNumber, string $message): bool
    {
        Log::info("WhatsApp to {$phoneNumber}: {$message}");

        return true;
    }

    protected function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleaned) === 9) {
            $cleaned = '254'.$cleaned;
        } elseif (strlen($cleaned) === 10 && $cleaned[0] === '0') {
            $cleaned = '254'.substr($cleaned, 1);
        }

        return $cleaned;
    }

    protected function log(string $provider, string $phoneNumber, string $message, ?string $error = null, string $status = 'sent'): void
    {
        try {
            NotificationLog::create([
                'notifiable_type' => 'WhatsApp',
                'notifiable_id' => 0,
                'channel' => 'whatsapp',
                'type' => 'outbound',
                'title' => "WhatsApp to {$phoneNumber}",
                'body' => $message,
                'status' => $status,
                'sent_at' => now(),
                'error' => $error,
                'metadata' => ['provider' => $provider, 'phone' => $phoneNumber],
            ]);
        } catch (\Throwable $e) {
            Log::warning("Failed to log WhatsApp notification: {$e->getMessage()}");
        }
    }
}
