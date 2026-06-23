<?php

namespace App\Modules\Members\Providers;

use App\Modules\Members\Livewire\LibraryCard;
use App\Modules\Members\Livewire\MemberBulkImport;
use App\Modules\Members\Livewire\MemberForm;
use App\Modules\Members\Livewire\MemberList;
use App\Modules\Members\Livewire\MembershipRequestList;
use App\Modules\Members\Livewire\MemberShow;
use App\Modules\Members\Livewire\SuspensionList;
use App\Modules\Members\Services\LibraryCardService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class MembersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LibraryCardService::class);
    }

    public function boot(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../Views', 'members');

        Livewire::component('member-list', MemberList::class);
        Livewire::component('member-form', MemberForm::class);
        Livewire::component('member-show', MemberShow::class);
        Livewire::component('member-bulk-import', MemberBulkImport::class);
        Livewire::component('library-card', LibraryCard::class);
        Livewire::component('membership-request-list', MembershipRequestList::class);
        Livewire::component('suspension-list', SuspensionList::class);
    }
}
