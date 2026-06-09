<?php

use App\Modules\Assignments\Livewire\TeacherAssignments;
use App\Modules\Assignments\Livewire\StudentAssignments;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('assignments')->name('assignments.')->group(function () {
    Route::get('/teacher', TeacherAssignments::class)
        ->name('teacher')
        ->middleware('permission:create-assignments');
    Route::get('/my', StudentAssignments::class)
        ->name('my')
        ->middleware('permission:view-assignments');
});
