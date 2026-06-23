<?php

use App\Modules\Communication\Livewire\Analytics;
use App\Modules\Communication\Livewire\AnnouncementForm;
use App\Modules\Communication\Livewire\AnnouncementList;
use App\Modules\Communication\Livewire\BroadcastMessageForm;
use App\Modules\Communication\Livewire\BulletinForm;
use App\Modules\Communication\Livewire\BulletinList;
use App\Modules\Communication\Livewire\EventForm;
use App\Modules\Communication\Livewire\EventList;
use App\Modules\Communication\Livewire\MessageForm;
use App\Modules\Communication\Livewire\MessageList;
use App\Modules\Communication\Livewire\MessageLogList;
use App\Modules\Communication\Livewire\MessageShow;
use App\Modules\Communication\Livewire\TemplateForm;
use App\Modules\Communication\Livewire\TemplateList;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('communication')->name('communication.')->group(function () {
    Route::get('/announcements', AnnouncementList::class)->name('announcements.index')->middleware('permission:manage-announcements');
    Route::get('/announcements/create', AnnouncementForm::class)->name('announcements.create')->middleware('permission:manage-announcements');
    Route::get('/announcements/{id}/edit', AnnouncementForm::class)->name('announcements.edit')->middleware('permission:manage-announcements');

    Route::get('/bulletins', BulletinList::class)->name('bulletins.index')->middleware('permission:manage-bulletins');
    Route::get('/bulletins/create', BulletinForm::class)->name('bulletins.create')->middleware('permission:manage-bulletins');
    Route::get('/bulletins/{id}/edit', BulletinForm::class)->name('bulletins.edit')->middleware('permission:manage-bulletins');

    Route::get('/events', EventList::class)->name('events.index')->middleware('permission:manage-events');
    Route::get('/events/create', EventForm::class)->name('events.create')->middleware('permission:manage-events');
    Route::get('/events/{id}/edit', EventForm::class)->name('events.edit')->middleware('permission:manage-events');

    Route::get('/messages', MessageList::class)->name('messages.index')->middleware('permission:view-messages');
    Route::get('/messages/create', MessageForm::class)->name('messages.create')->middleware('permission:send-messages');
    Route::get('/messages/logs', MessageLogList::class)->name('messages.logs')->middleware('permission:view-message-logs');
    Route::get('/messages/{id}', MessageShow::class)->name('messages.show')->middleware('permission:view-messages');

    Route::get('/templates', TemplateList::class)->name('templates.index')->middleware('permission:manage-templates');
    Route::get('/templates/create', TemplateForm::class)->name('templates.create')->middleware('permission:manage-templates');
    Route::get('/templates/{id}/edit', TemplateForm::class)->name('templates.edit')->middleware('permission:manage-templates');

    Route::get('/analytics', Analytics::class)->name('analytics')->middleware('permission:view-communication-analytics');

    Route::get('/broadcasts', BroadcastMessageForm::class)->name('broadcasts')->middleware('permission:manage-broadcasts');
});
