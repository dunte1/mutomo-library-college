<?php

namespace App\Modules\Circulation\Providers;

use App\Modules\Circulation\Livewire\CirculationList;
use App\Modules\Circulation\Livewire\FineManagement;
use App\Modules\Circulation\Livewire\IssueBook;
use App\Modules\Circulation\Livewire\OverrideDueDates;
use App\Modules\Circulation\Livewire\PatronReservation;
use App\Modules\Circulation\Livewire\ReservationList;
use App\Modules\Circulation\Livewire\ReturnBook;
use App\Modules\Circulation\Livewire\WaitlistList;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class CirculationServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../Views', 'circulation');

        Livewire::component('circulation-list', CirculationList::class);
        Livewire::component('fine-management', FineManagement::class);
        Livewire::component('issue-book', IssueBook::class);
        Livewire::component('override-due-dates', OverrideDueDates::class);
        Livewire::component('patron-reservation', PatronReservation::class);
        Livewire::component('return-book', ReturnBook::class);
        Livewire::component('reservation-list', ReservationList::class);
        Livewire::component('waitlist-list', WaitlistList::class);
    }
}
