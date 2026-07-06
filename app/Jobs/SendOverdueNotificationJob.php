<?php

namespace App\Jobs;

use App\Mail\OverdueNotice;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Communication\Services\WhatsAppService;
use App\Modules\Notifications\Models\InAppNotification;
use App\Modules\Settings\Models\Setting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOverdueNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public BorrowRecord $borrowRecord) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $user = $this->borrowRecord->user;

        if (! $user) {
            return;
        }

        $daysOverdue = now()->diffInDays($this->borrowRecord->due_at);
        $bookTitle = $this->borrowRecord->bookCopy?->book?->title ?? 'Unknown';

        InAppNotification::create([
            'user_id' => $user->id,
            'type' => 'overdue',
            'title' => 'Overdue Book Return',
            'body' => "Your borrowed book \"{$bookTitle}\" is {$daysOverdue} day(s) overdue. Please return it as soon as possible.",
            'icon' => 'clock',
            'action_url' => route('circulation.index'),
        ]);

        try {
            $emailEnabled = Setting::where('key', 'email_notifications_enabled')->value('value');
            if ($emailEnabled && $emailEnabled !== 'false' && $user->email) {
                Mail::to($user->email)->queue(new OverdueNotice($this->borrowRecord, $daysOverdue));
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send overdue email notification', [
                'user' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $whatsappEnabled = Setting::where('key', 'whatsapp_notifications')->value('value');
            if ($whatsappEnabled && $whatsappEnabled !== 'false' && $user->phone) {
                $message = "🚨 *Library Overdue Notice*\n\n"
                    ."Hi {$user->name},\n\n"
                    ."The book \"{$bookTitle}\" (Barcode: {$this->borrowRecord->bookCopy?->barcode}) is **{$daysOverdue} day(s) overdue**.\n\n"
                    ."Please return it immediately to avoid accumulating fines (KES " . config('fines.daily_rate', 50) . "/day).\n\n"
                    ."Thank you,\n"
                    .config('app.name');
                $whatsapp->send($user->phone, $message);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to send overdue WhatsApp notification', [
                'user' => $user->phone,
                'error' => $e->getMessage(),
            ]);
        }

        activity()
            ->performedOn($this->borrowRecord)
            ->causedBy($user)
            ->log("Overdue notification sent for borrow #{$this->borrowRecord->id}");
    }
}
