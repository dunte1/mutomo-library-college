<?php

namespace App\Modules\Notifications\Providers;

use App\Modules\Notifications\Livewire\NotificationList;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class NotificationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Modules\Notifications\Services\NotificationService::class);
    }

    public function boot(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../Views', 'notifications');

        Livewire::component('notification-list', NotificationList::class);
    }
}
