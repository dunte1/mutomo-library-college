<?php

use App\Modules\Reports\Livewire\CatalogReports;
use App\Modules\Reports\Livewire\CirculationReports;
use App\Modules\Reports\Livewire\DigitalLibraryReports;
use App\Modules\Reports\Livewire\MemberReports;
use App\Modules\Reports\Livewire\ReportsDashboard;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', ReportsDashboard::class)->name('dashboard')->middleware('permission:view-reports');
    Route::get('/catalog', CatalogReports::class)->name('catalog')->middleware('permission:view-reports');
    Route::get('/circulation', CirculationReports::class)->name('circulation')->middleware('permission:view-reports');
    Route::get('/members', MemberReports::class)->name('members')->middleware('permission:view-reports');
    Route::get('/digital-library', DigitalLibraryReports::class)->name('digital-library')->middleware('permission:view-reports');
});
