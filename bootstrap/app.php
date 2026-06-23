<?php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../app/Modules/API/Routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/auth.php'));
            Route::get('/health', \App\Http\Controllers\HealthCheckController::class)->middleware('auth');

            \Livewire\Livewire::setUpdateRoute(function ($handle) {
                return Route::post('/livewire/update', $handle)->middleware('web');
            });
        },
    )
    ->withProviders([
        \App\Providers\ModuleServiceProvider::class,
        \App\Modules\Auth\Providers\AuthServiceProvider::class,
        \App\Modules\Catalog\Providers\CatalogServiceProvider::class,
        \App\Modules\Circulation\Providers\CirculationServiceProvider::class,
        \App\Modules\DigitalLibrary\Providers\DigitalLibraryServiceProvider::class,
        \App\Modules\Finance\Providers\FinanceServiceProvider::class,
        \App\Modules\Members\Providers\MembersServiceProvider::class,
        \App\Modules\Settings\Providers\SettingsServiceProvider::class,
        \App\Modules\API\Providers\ApiServiceProvider::class,
        \App\Modules\Notifications\Providers\NotificationsServiceProvider::class,
        \App\Modules\Subscriptions\Providers\SubscriptionsServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            '2fa' => \App\Http\Middleware\TwoFactorMiddleware::class,
            'subscription' => \App\Http\Middleware\CheckSubscriptionStatus::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\LogUserActivity::class,
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);
        $middleware->api(append: [
            'throttle:api',
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ensure ModelNotFoundException renders the custom 404 page
        $exceptions->renderable(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404);
        });
    })->create();