<?php

use App\Modules\API\Controllers\AuthController;
use App\Modules\API\Controllers\BookController;
use App\Modules\API\Controllers\CirculationController;
use App\Modules\API\Controllers\DigitalAssetController;
use App\Modules\API\Controllers\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])
        ->name('auth.login')
        ->middleware('throttle:6,1');

    Route::post('/mpesa/callback', \App\Modules\API\Controllers\MpesaCallbackController::class)
        ->name('mpesa.callback')
        ->middleware('throttle:10,1');

    Route::post('/stripe/webhook', StripeWebhookController::class)
        ->name('stripe.webhook')
        ->middleware('throttle:30,1');

    Route::get('/books/search', [BookController::class, 'search'])
        ->name('books.search')
        ->middleware('throttle:30,1');

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        // Protected API routes
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/user', [AuthController::class, 'user'])->name('auth.user');

        Route::get('/books', [BookController::class, 'index'])->name('books.index')->middleware('permission:view-books');
        Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show')->middleware('permission:view-books');

        Route::get('/circulation/active', [CirculationController::class, 'activeBorrows'])->name('circulation.active');
        Route::get('/circulation/history', [CirculationController::class, 'history'])->name('circulation.history');
        Route::get('/circulation/overdue', [CirculationController::class, 'overdue'])->name('circulation.overdue');
        Route::post('/circulation/issue', [CirculationController::class, 'issue'])->name('circulation.issue')->middleware('permission:borrow-books');
        Route::post('/circulation/return', [CirculationController::class, 'returnBook'])->name('circulation.return')->middleware('permission:return-books');
        Route::get('/circulation/fines', [CirculationController::class, 'myFines'])->name('circulation.fines');

        Route::get('/digital-assets', [DigitalAssetController::class, 'index'])->name('digital-assets.index')->middleware('permission:view-digital-assets');
        Route::get('/digital-assets/{asset}', [DigitalAssetController::class, 'show'])->name('digital-assets.show')->middleware('permission:view-digital-assets');
        Route::get('/digital-assets/{asset}/download', [DigitalAssetController::class, 'download'])->name('digital-assets.download')->middleware('permission:download-digital-assets');
    });
});
