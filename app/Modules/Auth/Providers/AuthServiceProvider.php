<?php

namespace App\Modules\Auth\Providers;

use App\Modules\Auth\Livewire\TwoFactorVerify;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../Views', 'auth');

        Livewire::component('two-factor-verify', TwoFactorVerify::class);
    }
}
