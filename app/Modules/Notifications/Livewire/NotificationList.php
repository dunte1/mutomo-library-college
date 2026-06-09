<?php

namespace App\Modules\Notifications\Livewire;

use App\Modules\Notifications\Services\NotificationService;
use Livewire\Component;

class NotificationList extends Component
{
    public string $filter = 'all';

    public function markAllAsRead(): void
    {
        app(NotificationService::class)->markAllAsRead(auth()->user());
    }

    public function markAsRead(int $id): void
    {
        app(NotificationService::class)->markAsRead($id);
    }

    public function render()
    {
        $service = app(NotificationService::class);
        $notifications = $service->getNotifications(auth()->user(), 50);
        $unreadCount = $service->getUnreadCount(auth()->user());

        return view('notifications::livewire.notification-list', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
