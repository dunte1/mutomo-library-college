<?php

use App\Http\Controllers\HealthCheckController;
use App\Http\Middleware\CheckSubscriptionStatus;
use App\Http\Middleware\LogUserActivity;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\TwoFactorMiddleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../app/Modules/API/Routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/auth.php'));
            Route::get('/health', HealthCheckController::class)->middleware('auth');

            Livewire::setUpdateRoute(function ($handle) {
                return Route::post('/livewire/update', $handle)->middleware('web');
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            '2fa' => TwoFactorMiddleware::class,
            'subscription' => CheckSubscriptionStatus::class,
        ]);
        $middleware->web(append: [
            LogUserActivity::class,
            SecurityHeadersMiddleware::class,
        ]);
        $middleware->api(append: [
            'throttle:api',
            SecurityHeadersMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ensure ModelNotFoundException renders the custom 404 page
        $exceptions->renderable(function (ModelNotFoundException $e) {
            abort(404);
        });
    })->create();
