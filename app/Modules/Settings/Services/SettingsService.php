<?php

namespace App\Modules\Settings\Services;

use App\Modules\Settings\Models\Setting;
use App\Modules\Settings\Repositories\SettingsRepository;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    public function __construct(
        protected SettingsRepository $repository,
    ) {}

    public function getDisplaySettings(): array
    {
        return [
            'site_name' => $this->cached('site_name', 'OLLMCHS Library'),
            'site_description' => $this->cached('site_description', 'Library Management System'),
            'library_address' => $this->cached('library_address', ''),
            'library_phone' => $this->cached('library_phone', ''),
            'library_email' => $this->cached('library_email', ''),
            'opening_hours' => $this->cached('opening_hours', 'Mon-Fri: 8:00 AM - 5:00 PM'),
        ];
    }

    public function getCirculationRules(): array
    {
        return [
            'max_borrow_days' => (int) $this->cached('max_borrow_days', 14),
            'max_borrow_items' => (int) $this->cached('max_borrow_items', 5),
            'renewal_days' => (int) $this->cached('renewal_days', 7),
            'max_renewals' => (int) $this->cached('max_renewals', 2),
            'fine_per_day' => (float) $this->cached('fine_per_day', 50),
            'grace_period_days' => (int) $this->cached('grace_period_days', 0),
        ];
    }

    public function getDigitalLibrarySettings(): array
    {
        return [
            'max_upload_size' => (int) $this->cached('max_upload_size', 102400),
            'allowed_file_types' => $this->cached('allowed_file_types', 'pdf,doc,docx,ppt,pptx,mp4,mp3,epub'),
            'auto_approve_uploads' => (bool) $this->cached('auto_approve_uploads', false),
            'max_assets_per_user' => (int) $this->cached('max_assets_per_user', 50),
        ];
    }

    public function getNotificationSettings(): array
    {
        return [
            'email_notifications' => (bool) $this->cached('email_notifications', true),
            'sms_notifications' => (bool) $this->cached('sms_notifications', false),
            'whatsapp_notifications' => (bool) $this->cached('whatsapp_notifications', false),
            'due_date_reminders' => (bool) $this->cached('due_date_reminders', true),
            'overdue_alerts' => (bool) $this->cached('overdue_alerts', true),
            'new_arrival_alerts' => (bool) $this->cached('new_arrival_alerts', false),
            'fine_notifications' => (bool) $this->cached('fine_notifications', true),
            'reminder_days_before' => (int) $this->cached('reminder_days_before', 2),
        ];
    }

    public function getSecuritySettings(): array
    {
        return [
            'min_password_length' => (int) $this->cached('min_password_length', 8),
            'require_uppercase' => (bool) $this->cached('require_uppercase', true),
            'require_numbers' => (bool) $this->cached('require_numbers', true),
            'require_special_chars' => (bool) $this->cached('require_special_chars', false),
            'max_login_attempts' => (int) $this->cached('max_login_attempts', 5),
            'session_timeout' => (int) $this->cached('session_timeout', 120),
            'two_factor_required' => (bool) $this->cached('two_factor_required', false),
        ];
    }

    public function getEmailSettings(): array
    {
        return [
            'mail_from_name' => $this->cached('mail_from_name', 'OLLMCHS Library'),
            'mail_from_address' => $this->cached('mail_from_address', 'noreply@ollmchs.edu'),
            'mail_driver' => $this->cached('mail_driver', 'smtp'),
            'mail_host' => $this->cached('mail_host', ''),
            'mail_port' => $this->cached('mail_port', '587'),
            'mail_encryption' => $this->cached('mail_encryption', 'tls'),
            'mail_username' => $this->cached('mail_username', ''),
        ];
    }

    public function getEmailSetting(string $key, mixed $default = null): mixed
    {
        return $this->cached($key, $default);
    }

    public function getEmailPassword(): string
    {
        return $this->cached('mail_password', '');
    }

    public function hasEmailPassword(): bool
    {
        $val = $this->cached('mail_password', '');
        return !empty($val);
    }

    public function getBrandingSettings(): array
    {
        return [
            'document_logo' => $this->cached('document_logo', ''),
            'document_header_text' => $this->cached('document_header_text', config('app.name')),
            'document_footer_text' => $this->cached('document_footer_text', 'Official Library Document'),
            'document_primary_color' => $this->cached('document_primary_color', '#1E4FA3'),
            'document_show_verification_stamp' => (bool) $this->cached('document_show_verification_stamp', true),
            'document_show_qr_code' => (bool) $this->cached('document_show_qr_code', true),
            'document_footer_disclaimer' => $this->cached('document_footer_disclaimer', 'This document is electronically generated and is valid without a signature.'),
            'document_watermark_text' => $this->cached('document_watermark_text', ''),
        ];
    }

    public function getBackupSettings(): array
    {
        return [
            'auto_backup' => (bool) $this->cached('auto_backup', true),
            'backup_frequency' => $this->cached('backup_frequency', 'daily'),
            'backup_retention_days' => (int) $this->cached('backup_retention_days', 30),
            'backup_location' => $this->cached('backup_location', 'local'),
        ];
    }

    public function getLocalizationSettings(): array
    {
        return [
            'default_language' => $this->cached('default_language', 'en'),
            'default_timezone' => $this->cached('default_timezone', 'Africa/Nairobi'),
            'date_format' => $this->cached('date_format', 'd M Y'),
            'time_format' => $this->cached('time_format', 'H:i'),
            'currency' => $this->cached('currency', 'KES'),
            'first_day_of_week' => $this->cached('first_day_of_week', 'monday'),
        ];
    }

    public function getFooterSettings(): array
    {
        return [
            'footer_copyright' => $this->cached('footer_copyright', 'All rights reserved.'),
            'footer_facebook_url' => $this->cached('footer_facebook_url', ''),
            'footer_twitter_url' => $this->cached('footer_twitter_url', ''),
            'footer_instagram_url' => $this->cached('footer_instagram_url', ''),
            'footer_youtube_url' => $this->cached('footer_youtube_url', ''),
            'footer_linkedin_url' => $this->cached('footer_linkedin_url', ''),
            'footer_newsletter_visible' => (bool) $this->cached('footer_newsletter_visible', true),
        ];
    }

    public function updateSettings(string $group, array $data, array $sensitiveKeys = []): void
    {
        foreach ($data as $key => $value) {
            $isEncrypted = in_array($key, $sensitiveKeys) && !empty($value);

            if ($isEncrypted && $value !== null) {
                $value = encrypt($value);
            }

            Setting::updateOrCreate(
                ['key' => $key, 'group' => $group],
                [
                    'value' => $this->castValueForStorage($value),
                    'is_encrypted' => $isEncrypted,
                    'type' => is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'text'),
                ]
            );

            if ($key === 'site_name') {
                config(['app.name' => $value]);
            }
        }

        $this->clearGroupCache($group);
    }

    public function getAllGrouped(): array
    {
        return Setting::all()->groupBy('group')->map(function ($settings) {
            return $settings->keyBy('key')->map(function ($setting) {
                return $this->castValueForDisplay($setting->value, $setting->type);
            });
        })->toArray();
    }

    public function cached(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", now()->addDay(), function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            $value = $setting->value;

            if ($setting->is_encrypted && !empty($value)) {
                try {
                    $value = decrypt($value);
                } catch (\Exception $e) {
                    return $default;
                }
            }

            return $this->castValueForDisplay($value, $setting->type);
        });
    }

    protected function castValueForDisplay(mixed $value, string $type = 'text'): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value,
            'integer', 'number' => is_numeric($value) ? (int) $value : $value,
            'float' => is_numeric($value) ? (float) $value : $value,
            default => $value,
        };
    }

    protected function castValueForStorage(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return $value;
    }

    protected function clearGroupCache(string $group): void
    {
        $keys = Setting::byGroup($group)->pluck('key');

        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }

        Cache::forget("setting_group.{$group}");
    }
}
