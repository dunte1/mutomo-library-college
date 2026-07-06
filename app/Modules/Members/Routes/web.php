<?php

use App\Modules\Members\Controllers\LibraryCardController;
use App\Modules\Members\Livewire\LibraryCard;
use App\Modules\Members\Livewire\MemberForm;
use App\Modules\Members\Livewire\MemberList;
use App\Modules\Members\Livewire\MembershipRequestList;
use App\Modules\Members\Livewire\MemberShow;
use App\Modules\Members\Livewire\SuspensionList;
use Illuminate\Support\Facades\Route;

// Template download (no auth required for CSV template)
Route::get('/members/bulk/template', function () {
    $headers = [
        'first_name', 'last_name', 'email', 'phone', 'admission_number',
        'id_number', 'gender', 'date_of_birth', 'year_of_study', 'department',
        'program', 'membership_type', 'address',
    ];
    $sample = [
        'John', 'Doe', 'john.doe@school.ac.ke', '+254 712 345 678', 'ADM-2026-001',
        '12345678', 'male', '2005-06-15', 'Year 1', 'Science',
        'Computer Science', 'student', '123 Library Lane',
    ];

    $callback = function () use ($headers, $sample) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $headers);
        fputcsv($file, $sample);
        fclose($file);
    };

    return response()->stream($callback, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="member-import-template.csv"',
    ]);
})->name('members.bulk.template');

Route::middleware(['auth', 'verified'])
    ->prefix('members')
    ->name('members.')
    ->group(function () {
        Route::get('/', MemberList::class)
            ->name('index')
            ->middleware('permission:view-members');

        Route::get('/create', MemberForm::class)
            ->name('create')
            ->middleware(['permission:create-members', 'subscription:register_members']);

        Route::get('/requests', MembershipRequestList::class)
            ->name('requests')
            ->middleware('permission:manage-membership-requests');

        Route::get('/suspensions', SuspensionList::class)
            ->name('suspensions')
            ->middleware('permission:suspend-members');

        Route::get('/{id}/edit', MemberForm::class)
            ->name('edit')
            ->middleware('permission:edit-members');

        Route::get('/{id}', MemberShow::class)
            ->name('show')
            ->middleware('permission:view-members');

        Route::get('/{id}/card', LibraryCard::class)
            ->name('card')
            ->middleware('permission:view-library-cards');

        Route::get('/{id}/card/download', [LibraryCardController::class, 'download'])
            ->name('card.download')
            ->middleware('permission:view-library-cards');
    });

// Public card verification route (no auth required — accessible via QR scan)
Route::get('/verify/card/{cardNumber}', [LibraryCardController::class, 'verify'])
    ->name('verify.card');
