<?php

namespace App\Modules\Shared\Helpers;

use Illuminate\Support\Facades\Log;

class AuditHelper
{
    public static function log(string $action, string $module, array $details = []): void
    {
        $user = auth()->user();

        $properties = $details;
        if (!app()->runningInConsole()) {
            $properties = array_merge($properties, [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        activity()
            ->causedBy($user)
            ->withProperties($properties)
            ->event($action)
            ->log("{$module}: {$action}");
    }

    public static function info(string $message, array $context = []): void
    {
        Log::info($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        Log::warning($message, $context);
    }
}
