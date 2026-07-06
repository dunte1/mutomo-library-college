<?php

use App\Modules\Subscriptions\Livewire\Admin\PlanForm;
use App\Modules\Subscriptions\Livewire\Admin\PlanList;
use App\Modules\Subscriptions\Livewire\Admin\RevenueDashboard;
use App\Modules\Subscriptions\Livewire\Admin\SubscriptionList;
use App\Modules\Subscriptions\Livewire\MySubscription;
use App\Modules\Subscriptions\Livewire\SubscriptionCheckout;
use App\Modules\Subscriptions\Livewire\SubscriptionPlans;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('subscriptions')->name('subscriptions.')->group(function () {
    Route::get('/plans', SubscriptionPlans::class)->name('plans');
    Route::get('/my', MySubscription::class)->name('my');
    Route::get('/checkout/{plan}', SubscriptionCheckout::class)->name('checkout');
});

Route::middleware(['auth', 'verified', 'permission:manage-subscriptions'])->prefix('admin/subscriptions')->name('admin.subscriptions.')->group(function () {
    Route::get('/plans', PlanList::class)->name('plans');
    Route::get('/plans/create', PlanForm::class)->name('plans.create');
    Route::get('/plans/{plan}/edit', PlanForm::class)->name('plans.edit');
    Route::get('/', SubscriptionList::class)->name('index');
    Route::get('/revenue', RevenueDashboard::class)->name('revenue');
});
