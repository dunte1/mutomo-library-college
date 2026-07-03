<?php

use App\Modules\Notifications\Livewire\NotificationList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('notifications')
    ->name('notifications.')
    ->group(function () {
        Route::get('/', NotificationList::class)->name('index')->middleware('permission:view-notification-logs');
    });
