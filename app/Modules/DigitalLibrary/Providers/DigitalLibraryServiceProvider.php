<?php

namespace App\Modules\DigitalLibrary\Providers;

use App\Modules\DigitalLibrary\Livewire\DigitalAssetList;
use App\Modules\DigitalLibrary\Livewire\DigitalAssetReader;
use App\Modules\DigitalLibrary\Livewire\DigitalAssetShow;
use App\Modules\DigitalLibrary\Livewire\DigitalAssetUpload;
use App\Modules\DigitalLibrary\Livewire\DigitalCategoryList;
use App\Modules\DigitalLibrary\Livewire\DownloadsList;
use App\Modules\DigitalLibrary\Livewire\Recommendations;
use App\Modules\DigitalLibrary\Models\DigitalAsset;
use App\Modules\DigitalLibrary\Observers\DigitalAssetObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class DigitalLibraryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../Views', 'digital-library');

        DigitalAsset::observe(DigitalAssetObserver::class);

        Livewire::component('digital-asset-list', DigitalAssetList::class);
        Livewire::component('digital-asset-reader', DigitalAssetReader::class);
        Livewire::component('digital-asset-upload', DigitalAssetUpload::class);
        Livewire::component('digital-asset-show', DigitalAssetShow::class);
        Livewire::component('recommendations', Recommendations::class);
        Livewire::component('downloads-list', DownloadsList::class);
        Livewire::component('digital-category-list', DigitalCategoryList::class);
    }
}
