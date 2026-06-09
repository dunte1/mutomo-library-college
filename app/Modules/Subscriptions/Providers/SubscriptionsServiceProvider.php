<?php

namespace App\Modules\Subscriptions\Providers;

use App\Modules\Subscriptions\Livewire\Admin\PlanForm;
use App\Modules\Subscriptions\Livewire\Admin\PlanList;
use App\Modules\Subscriptions\Livewire\Admin\SubscriptionList;
use App\Modules\Subscriptions\Livewire\MySubscription;
use App\Modules\Subscriptions\Livewire\SubscriptionCheckout;
use App\Modules\Subscriptions\Livewire\SubscriptionPlans;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class SubscriptionsServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../Views', 'subscriptions');

        Livewire::component('subscription-plans', SubscriptionPlans::class);
        Livewire::component('my-subscription', MySubscription::class);
        Livewire::component('subscription-checkout', SubscriptionCheckout::class);
        Livewire::component('admin.plan-list', PlanList::class);
        Livewire::component('admin.plan-form', PlanForm::class);
        Livewire::component('admin.subscription-list', SubscriptionList::class);
    }
}
