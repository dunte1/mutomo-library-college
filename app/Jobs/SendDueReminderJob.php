<?php

namespace App\Jobs;

use App\Mail\DueDateReminder;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Communication\Services\WhatsAppService;
use App\Modules\Notifications\Models\InAppNotification;
use App\Modules\Settings\Models\Setting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDueReminderJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public BorrowRecord $borrowRecord, public int $daysUntilDue) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $user = $this->borrowRecord->user;
        if (!$user) return;

        $bookTitle = $this->borrowRecord->bookCopy?->book?->title ?? 'Unknown';

        // In-app notification
        InAppNotification::create([
            'user_id' => $user->id,
            'type' => 'due_reminder',
            'title' => 'Book Due Soon',
            'body' => "Your borrowed book \"{$bookTitle}\" is due in {$this->daysUntilDue} day(s). Please return it on time to avoid fines.",
            'icon' => 'clock',
            'action_url' => route('circulation.index'),
        ]);

        // Email notification
        try {
            $emailEnabled = Setting::where('key', 'email_notifications_enabled')->value('value');
            if ($emailEnabled && $emailEnabled !== 'false' && $user->email) {
                Mail::to($user->email)->queue(new DueDateReminder($this->borrowRecord, $this->daysUntilDue));
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to send due reminder email for borrow #{$this->borrowRecord->id}: {$e->getMessage()}");
        }

        // WhatsApp notification
        try {
            $whatsappEnabled = Setting::where('key', 'whatsapp_notifications')->value('value');
            if ($whatsappEnabled && $whatsappEnabled !== 'false' && $user->phone) {
                $message = "📚 *Library Due Date Reminder*\n\n"
                    . "Hi {$user->name},\n\n"
                    . "Your borrowed book \"{$bookTitle}\" is due in {$this->daysUntilDue} day(s) (Due: {$this->borrowRecord->due_at->format('d M Y')}).\n\n"
                    . "Please return it on time to avoid overdue fines.\n\n"
                    . "Thank you,\n"
                    . config('app.name');
                $whatsapp->send($user->phone, $message);
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to send due reminder WhatsApp for borrow #{$this->borrowRecord->id}: {$e->getMessage()}");
        }

        activity()
            ->performedOn($this->borrowRecord)
            ->causedBy($user)
            ->log("Due reminder sent for borrow #{$this->borrowRecord->id} ({$this->daysUntilDue} days before due)");
    }
}
