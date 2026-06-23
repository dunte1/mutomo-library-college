<?php

use App\Jobs\CheckOverdueBorrowsJob;
use App\Jobs\SendDueReminderJob;
use App\Jobs\SendOverdueNotificationJob;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Finance\Services\MpesaService;
use App\Modules\Settings\Services\SettingsService;
use App\Modules\Subscriptions\Jobs\ProcessSubscriptionRenewals;
use App\Modules\Subscriptions\Services\SubscriptionService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new CheckOverdueBorrowsJob)->dailyAt('02:00');

Schedule::call(function () {
    $overdue = BorrowRecord::whereIn('status', ['active', 'overdue'])
        ->where('due_at', '<', now())
        ->whereNull('returned_at')
        ->get();

    foreach ($overdue as $record) {
        SendOverdueNotificationJob::dispatch($record);
    }
})->dailyAt('08:00')->name('send-overdue-notifications');

Schedule::call(function () {
    $settings = app(SettingsService::class);
    $notificationSettings = $settings->getNotificationSettings();
    if (! ($notificationSettings['due_date_reminders'] ?? false)) {
        return;
    }

    $daysBefore = $notificationSettings['reminder_days_before'] ?? 2;
    $targetDate = now()->addDays($daysBefore);

    $dueSoon = BorrowRecord::where('status', BorrowRecord::STATUS_ACTIVE)
        ->whereDate('due_at', $targetDate->toDateString())
        ->whereNull('returned_at')
        ->with('user', 'bookCopy.book')
        ->get();

    foreach ($dueSoon as $record) {
        SendDueReminderJob::dispatch($record, $daysBefore);
    }
})->dailyAt('09:00')->name('send-due-date-reminders');

// Subscription renewal & expiration processing
Schedule::job(new ProcessSubscriptionRenewals)->dailyAt('01:00')->name('process-subscription-renewals');

// Send expiring-soon notifications for subscriptions expiring in 7 days
Schedule::call(function () {
    $service = app(SubscriptionService::class);
    $notified = $service->sendExpiringSoonNotifications(7);
    Log::info("Subscription expiring-soon notifications sent: {$notified}");
})->dailyAt('08:00')->name('send-subscription-expiry-notices');

// Automated database backup and cleanup
Schedule::command('backup:database')->dailyAt('03:00')->name('backup-database');
Schedule::command('backup:clean')->dailyAt('03:30')->name('clean-old-backups');

Schedule::call(function () {
    app(MpesaService::class)->cleanStalePendingTransactions(60);
})->everyThirtyMinutes()->name('clean-stale-mpesa-transactions');
