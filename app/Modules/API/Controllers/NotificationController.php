<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Resources\NotificationResource;
use App\Modules\API\Services\ApiResponseService;
use App\Modules\Notifications\Models\InAppNotification;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Routing\Controller;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService,
        protected ApiResponseService $response,
    ) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'type' => 'nullable|string|in:overdue,due_reminder,hold_available,fine,subscription,system,message',
        ]);

        $query = InAppNotification::where('user_id', auth()->id());

        if (! empty($data['type'])) {
            $query->byType($data['type']);
        }

        $notifications = $query->latest()
            ->paginate(min((int) ($data['per_page'] ?? 20), 100));

        $notifications->getCollection()->transform(fn ($n) => new NotificationResource($n));

        return $this->response->paginated($notifications, [
            'unread_count' => $this->notificationService->getUnreadCount(auth()->user()),
        ]);
    }

    public function markAsRead(int $id): \Illuminate\Http\JsonResponse
    {
        $this->notificationService->markAsRead($id);

        return $this->response->success(null, 'Notification marked as read.');
    }

    public function markAllAsRead(): \Illuminate\Http\JsonResponse
    {
        $this->notificationService->markAllAsRead(auth()->user());

        return $this->response->success(null, 'All notifications marked as read.');
    }

    public function unreadCount(): \Illuminate\Http\JsonResponse
    {
        $count = $this->notificationService->getUnreadCount(auth()->user());

        return $this->response->success(['unread_count' => $count]);
    }
}
