<?php

namespace App\Providers;

use App\Console\Commands\GenerateVapidKeys;
use App\Listeners\InvalidateSessionsOnRoleChange;
use App\View\Composers\LayoutComposer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Events\PermissionAttachedEvent;
use Spatie\Permission\Events\PermissionDetachedEvent;
use Spatie\Permission\Events\RoleAttachedEvent;
use Spatie\Permission\Events\RoleDetachedEvent;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.app', LayoutComposer::class);

        Event::listen(RoleAttachedEvent::class, InvalidateSessionsOnRoleChange::class);
        Event::listen(RoleDetachedEvent::class, InvalidateSessionsOnRoleChange::class);
        Event::listen(PermissionAttachedEvent::class, InvalidateSessionsOnRoleChange::class);
        Event::listen(PermissionDetachedEvent::class, InvalidateSessionsOnRoleChange::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateVapidKeys::class,
            ]);
        }
    }
}
