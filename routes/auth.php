<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('guest')->group(function () {
    $throttle = app()->environment('local') ? 'throttle:60,1' : 'throttle:30,1';

    Volt::route('register', 'pages.auth.register')
        ->name('register')->middleware($throttle);

    Volt::route('register/student', 'pages.auth.register-student')
        ->name('register.student')->middleware($throttle);

    Volt::route('login', 'pages.auth.login')
        ->name('login')->middleware($throttle);

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request')->middleware($throttle);

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset')->middleware($throttle);
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});
