<?php

namespace App\Modules\Assignments\Providers;

use App\Modules\Assignments\Livewire\TeacherAssignments;
use App\Modules\Assignments\Livewire\StudentAssignments;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AssignmentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../Views', 'assignments');

        Livewire::component('teacher-assignments', TeacherAssignments::class);
        Livewire::component('student-assignments', StudentAssignments::class);
    }
}
