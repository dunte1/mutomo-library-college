<?php

namespace App\Modules\Catalog\Providers;

use App\Modules\Catalog\Livewire\BookBulkUpload;
use App\Modules\Catalog\Livewire\BookForm;
use App\Modules\Catalog\Livewire\BookList;
use App\Modules\Catalog\Livewire\BookShow;
use App\Modules\Catalog\Livewire\AuthorForm;
use App\Modules\Catalog\Livewire\AuthorList;
use App\Modules\Catalog\Livewire\CategoryForm;
use App\Modules\Catalog\Livewire\CategoryList;
use App\Modules\Catalog\Livewire\PublisherForm;
use App\Modules\Catalog\Livewire\PublisherList;
use App\Modules\Catalog\Livewire\InventoryList;
use App\Modules\Catalog\Livewire\NewArrivals;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../Views', 'catalog');

        Livewire::component('book-list', BookList::class);
        Livewire::component('book-form', BookForm::class);
        Livewire::component('book-show', BookShow::class);
        Livewire::component('book-bulk-upload', BookBulkUpload::class);
        Livewire::component('category-list', CategoryList::class);
        Livewire::component('category-form', CategoryForm::class);
        Livewire::component('author-list', AuthorList::class);
        Livewire::component('author-form', AuthorForm::class);
        Livewire::component('publisher-list', PublisherList::class);
        Livewire::component('publisher-form', PublisherForm::class);
        Livewire::component('inventory-list', InventoryList::class);
        Livewire::component('new-arrivals', NewArrivals::class);
    }
}
