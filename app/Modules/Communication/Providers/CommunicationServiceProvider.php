<?php

namespace App\Modules\Communication\Providers;

use App\Modules\Communication\Livewire\Analytics;
use App\Modules\Communication\Livewire\AnnouncementForm;
use App\Modules\Communication\Livewire\AnnouncementList;
use App\Modules\Communication\Livewire\BulletinForm;
use App\Modules\Communication\Livewire\BulletinList;
use App\Modules\Communication\Livewire\EventForm;
use App\Modules\Communication\Livewire\EventList;
use App\Modules\Communication\Livewire\MessageForm;
use App\Modules\Communication\Livewire\MessageList;
use App\Modules\Communication\Livewire\MessageShow;
use App\Modules\Communication\Livewire\TemplateForm;
use App\Modules\Communication\Livewire\TemplateList;
use App\Modules\Communication\Livewire\BroadcastMessageForm;
use App\Modules\Communication\Livewire\MessageLogList;
use App\Modules\Communication\Services\MessagingService;
use App\Modules\Communication\Services\PushNotificationService;
use App\Modules\Communication\Services\SmsService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class CommunicationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MessagingService::class);
        $this->app->singleton(SmsService::class);
        $this->app->singleton(PushNotificationService::class);
    }

    public function boot(): void
    {
        Route::middleware('web')
            ->group(__DIR__.'/../Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../Views', 'communication');

        Livewire::component('announcement-list', AnnouncementList::class);
        Livewire::component('announcement-form', AnnouncementForm::class);
        Livewire::component('bulletin-list', BulletinList::class);
        Livewire::component('bulletin-form', BulletinForm::class);
        Livewire::component('event-list', EventList::class);
        Livewire::component('event-form', EventForm::class);
        Livewire::component('message-list', MessageList::class);
        Livewire::component('message-form', MessageForm::class);
        Livewire::component('message-show', MessageShow::class);
        Livewire::component('template-list', TemplateList::class);
        Livewire::component('template-form', TemplateForm::class);
        Livewire::component('communication-analytics', Analytics::class);
        Livewire::component('broadcast-message-form', BroadcastMessageForm::class);
        Livewire::component('message-log-list', MessageLogList::class);
    }
}
