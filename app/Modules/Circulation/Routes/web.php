<?php

use App\Modules\Circulation\Controllers\PatronRenewalController;
use App\Modules\Circulation\Livewire\CirculationList;
use App\Modules\Circulation\Livewire\IssueBook;
use App\Modules\Circulation\Livewire\OverrideDueDates;
use App\Modules\Circulation\Livewire\ReservationList;
use App\Modules\Circulation\Livewire\ReturnBook;
use App\Modules\Circulation\Livewire\WaitlistList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('circulation')->name('circulation.')->group(function () {
    Route::get('/', CirculationList::class)->name('index')->middleware('permission:view-circulation');
    Route::get('/issue', IssueBook::class)->name('issue')->middleware('permission:borrow-books');
    Route::get('/return', ReturnBook::class)->name('return')->middleware('permission:return-books');
    Route::get('/reservations', ReservationList::class)->name('reservations')->middleware('permission:manage-reservations');
    Route::get('/waitlists', WaitlistList::class)->name('waitlists')->middleware('permission:manage-waitlists');
    Route::get('/override-due-dates', OverrideDueDates::class)->name('override-due-dates')->middleware('permission:override-due-dates');
    Route::post('/renew/{borrowId}', [PatronRenewalController::class, 'renew'])->name('renew')->middleware('permission:renew-books');
});
