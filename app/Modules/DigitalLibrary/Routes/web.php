<?php

use App\Modules\DigitalLibrary\Controllers\DigitalAssetFileController;
use App\Modules\DigitalLibrary\Livewire\DigitalAssetEdit;
use App\Modules\DigitalLibrary\Livewire\DigitalAssetList;
use App\Modules\DigitalLibrary\Livewire\DigitalAssetReader;
use App\Modules\DigitalLibrary\Livewire\DigitalAssetShow;
use App\Modules\DigitalLibrary\Livewire\DigitalAssetUpload;
use App\Modules\DigitalLibrary\Livewire\DigitalCategoryList;
use App\Modules\DigitalLibrary\Livewire\DownloadsList;
use App\Modules\DigitalLibrary\Livewire\FreeBooksSearch;
use App\Modules\DigitalLibrary\Livewire\Recommendations;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('digital-library')->name('digital-library.')->group(function () {
    Route::get('/', DigitalAssetList::class)->name('index')->middleware('permission:view-digital-assets');
    Route::get('/free-books', FreeBooksSearch::class)->name('free-books')->middleware('permission:view-digital-assets');
    Route::get('/upload', DigitalAssetUpload::class)->name('upload')->middleware(['permission:upload-digital-assets', 'subscription:upload_assets']);
    Route::get('/recommendations', Recommendations::class)->name('recommendations')->middleware('permission:view-recommendations');
    Route::get('/downloads', DownloadsList::class)->name('downloads')->middleware('permission:download-digital-assets');
    Route::get('/categories', DigitalCategoryList::class)->name('categories')->middleware('permission:view-digital-categories');
    Route::get('/{asset}/file', [DigitalAssetFileController::class, 'show'])->name('file')->middleware('permission:view-digital-assets');
    Route::get('/{asset}/read', DigitalAssetReader::class)->name('read')->middleware('permission:view-digital-assets');
    Route::get('/{asset}/edit', DigitalAssetEdit::class)->name('edit')->middleware('permission:upload-digital-assets');
    Route::get('/{asset}', DigitalAssetShow::class)->name('show')->middleware('permission:view-digital-assets');
});
