<?php

namespace App\Modules\API\Providers;

use App\Modules\API\Services\ApiResponseService;
use App\Modules\API\Services\AuthenticationService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class ApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ApiResponseService::class);
        $this->app->singleton(AuthenticationService::class);
    }

    public function boot(): void
    {
        // General API rate limit: 60 requests per minute per user/ip
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip()));

        // Strict rate limit for login/register (6 per minute)
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(6)
            ->by($request->input('email') ?: $request->ip()));

        // Moderate rate limit for search (30 per minute)
        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(30)
            ->by($request->user()?->id ?: $request->ip()));

        // Webhook rate limit (10 per minute)
        RateLimiter::for('webhooks', fn (Request $request) => Limit::perMinute(10)
            ->by($request->ip()));
    }
}
