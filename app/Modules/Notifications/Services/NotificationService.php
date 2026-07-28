<?php

namespace App\Modules\Notifications\Services;

use App\Models\User;
use App\Modules\Notifications\Models\InAppNotification;

class NotificationService
{
    public function send(
        User $user,
        string $type,
        string $title,
        ?string $body = null,
        ?string $icon = null,
        ?string $actionUrl = null,
        ?array $data = null,
    ): ?InAppNotification {
        $prefs = $user->notification_preferences ?? [];

        $channelMap = [
            'overdue' => 'overdue_alerts',
            'due_reminder' => 'due_date_reminders',
            'hold_available' => 'hold_available',
            'fine' => 'fine_notifications',
            'borrow' => 'due_date_reminders',
            'return' => 'due_date_reminders',
        ];

        $prefKey = $channelMap[$type] ?? 'in_app';
        if (isset($prefs[$prefKey]) && $prefs[$prefKey] === false) {
            return null;
        }

        return InAppNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'icon' => $icon,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);
    }

    public function sendOverdueNotice(User $user, string $bookTitle, int $daysOverdue): void
    {
        $this->send(
            $user,
            'overdue',
            'Book Overdue',
            "{$bookTitle} is {$daysOverdue} day(s) overdue. Please return it as soon as possible.",
            'exclamation-circle',
            route('circulation.index'),
        );
    }

    public function sendDueReminder(User $user, string $bookTitle, string $dueDate): void
    {
        $this->send(
            $user,
            'due_reminder',
            'Due Date Reminder',
            "{$bookTitle} is due on {$dueDate}.",
            'clock',
            route('circulation.index'),
        );
    }

    public function sendHoldAvailable(User $user, string $bookTitle): void
    {
        $this->send(
            $user,
            'hold_available',
            'Hold Available',
            "{$bookTitle} is now available for pickup. Please collect it within the stated period.",
            'bookmark',
            route('circulation.index'),
        );
    }

    public function sendFineAssessed(User $user, string $reason, float $amount): void
    {
        $this->send(
            $user,
            'fine',
            'Fine Assessed',
            "A fine of KES {$amount} has been assessed for: {$reason}.",
            'credit-card',
            route('finance.fines'),
        );
    }

    public function markAsRead(int $notificationId): void
    {
        InAppNotification::where('id', $notificationId)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAllAsRead(User $user): void
    {
        InAppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public function getUnreadCount(User $user): int
    {
        return InAppNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    public function getNotifications(User $user, int $limit = 20): iterable
    {
        return InAppNotification::where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function sendCardIssued(User $user, string $cardNumber): void
    {
        $this->send(
            $user,
            'card_issued',
            'Library Card Issued',
            "Your library card ({$cardNumber}) has been issued. You can view it from your dashboard.",
            'credit-card',
            route('members.my-card'),
        );
    }

    public function sendCardExpiringSoon(User $user, string $cardNumber, string $expiresAt): void
    {
        $this->send(
            $user,
            'card_expiring',
            'Library Card Expiring Soon',
            "Your library card ({$cardNumber}) expires on {$expiresAt}. Please visit the library to renew.",
            'exclamation-triangle',
            route('members.my-card'),
        );
    }

    public function deleteOldNotifications(int $daysOld = 90): int
    {
        return InAppNotification::where('created_at', '<', now()->subDays($daysOld))
            ->delete();
    }
}
