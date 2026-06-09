<?php

namespace App\Modules\Reports\Providers;

use App\Modules\Reports\Livewire\CatalogReports;
use App\Modules\Reports\Livewire\CirculationReports;
use App\Modules\Reports\Livewire\DigitalLibraryReports;
use App\Modules\Reports\Livewire\MemberReports;
use App\Modules\Reports\Livewire\ReportsDashboard;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ReportsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../Views', 'reports');

        Livewire::component('reports-dashboard', ReportsDashboard::class);
        Livewire::component('catalog-reports', CatalogReports::class);
        Livewire::component('circulation-reports', CirculationReports::class);
        Livewire::component('member-reports', MemberReports::class);
        Livewire::component('digital-library-reports', DigitalLibraryReports::class);
    }
}
