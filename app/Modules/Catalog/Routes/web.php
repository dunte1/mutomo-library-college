<?php

use App\Modules\Catalog\Livewire\AuthorForm;
use App\Modules\Catalog\Livewire\AuthorList;
use App\Modules\Catalog\Livewire\BookBulkUpload;
use App\Modules\Catalog\Livewire\BookForm;
use App\Modules\Catalog\Livewire\BookList;
use App\Modules\Catalog\Livewire\BookShow;
use App\Modules\Catalog\Livewire\CategoryForm;
use App\Modules\Catalog\Livewire\CategoryList;
use App\Modules\Catalog\Livewire\InventoryList;
use App\Modules\Catalog\Livewire\NewArrivals;
use App\Modules\Catalog\Livewire\PublisherForm;
use App\Modules\Catalog\Livewire\PublisherList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('catalog')->name('catalog.')->group(function () {
    Route::get('/books', BookList::class)->name('books.index')->middleware('permission:view-books');
    Route::get('/books/create', BookForm::class)->name('books.create')->middleware('permission:create-books');
    Route::get('/books/bulk-upload', BookBulkUpload::class)->name('books.bulk-upload')->middleware('permission:create-books');
    Route::get('/books/{id}/edit', BookForm::class)->name('books.edit')->middleware('permission:edit-books');
    Route::get('/books/{id}', BookShow::class)->name('books.show')->middleware('permission:view-books');

    Route::get('/categories', CategoryList::class)->name('categories')->middleware('permission:view-books');
    Route::get('/categories/create', CategoryForm::class)->name('categories.create')->middleware('permission:create-books');
    Route::get('/categories/{id}/edit', CategoryForm::class)->name('categories.edit')->middleware('permission:edit-books');

    Route::get('/authors', AuthorList::class)->name('authors')->middleware('permission:view-authors');
    Route::get('/authors/create', AuthorForm::class)->name('authors.create')->middleware('permission:create-authors');
    Route::get('/authors/{id}/edit', AuthorForm::class)->name('authors.edit')->middleware('permission:edit-authors');

    Route::get('/publishers', PublisherList::class)->name('publishers')->middleware('permission:view-publishers');
    Route::get('/publishers/create', PublisherForm::class)->name('publishers.create')->middleware('permission:create-publishers');
    Route::get('/publishers/{id}/edit', PublisherForm::class)->name('publishers.edit')->middleware('permission:edit-publishers');

    Route::get('/inventory', InventoryList::class)->name('inventory')->middleware('permission:view-inventory');
    Route::get('/new-arrivals', NewArrivals::class)->name('new-arrivals')->middleware('permission:view-new-arrivals');
});
