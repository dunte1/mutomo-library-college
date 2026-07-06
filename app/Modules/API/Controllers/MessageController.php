<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Resources\MessageResource;
use App\Modules\API\Services\ApiResponseService;
use App\Modules\Communication\Models\Message;
use App\Modules\Communication\Services\MessagingService;
use Illuminate\Routing\Controller;

class MessageController extends Controller
{
    public function __construct(
        protected MessagingService $messagingService,
        protected ApiResponseService $response,
    ) {}

    public function inbox(): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $messages = $this->messagingService->getInbox(
            auth()->user(),
            min((int) ($data['per_page'] ?? 15), 100)
        );

        $messages->getCollection()->transform(fn ($recipient) => new MessageResource(
            $recipient->message->load(['sender', 'replies'])
        ));

        return $this->response->paginated($messages, [
            'total_unread' => $this->messagingService->getUnreadCount(auth()->user()),
        ]);
    }

    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $message = Message::with([
            'sender', 'attachments', 'replies.sender',
        ])->findOrFail($id);

        $this->messagingService->markAsRead(auth()->user(), $id);

        return $this->response->success(new MessageResource($message));
    }

    public function store(): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'recipient_ids' => ['required', 'array', 'min:1'],
            'recipient_ids.*' => ['integer', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'priority' => ['required', 'string', 'in:low,normal,high'],
            'type' => ['required', 'string', 'in:direct,group,department,program'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
        ]);

        $message = $this->messagingService->sendMessage(auth()->user(), $data);

        return $this->response->success(
            new MessageResource($message->load('sender')),
            'Message sent.',
            201
        );
    }

    public function reply(int $id): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'body' => ['required', 'string'],
            'reply_all' => ['nullable', 'boolean'],
        ]);

        $parent = Message::findOrFail($id);

        $message = $data['reply_all'] ?? false
            ? $this->messagingService->replyAllToMessage(auth()->user(), $parent, $data['body'])
            : $this->messagingService->replyToMessage(auth()->user(), $parent, $data['body']);

        return $this->response->success(
            new MessageResource($message->load('sender')),
            'Reply sent.'
        );
    }

    public function sent(): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $messages = $this->messagingService->getSentMessages(
            auth()->user(),
            min((int) ($data['per_page'] ?? 15), 100)
        );

        $messages->getCollection()->transform(fn ($msg) => new MessageResource(
            $msg->load(['recipients.recipient'])
        ));

        return $this->response->paginated($messages);
    }

    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        $message = Message::findOrFail($id);
        $this->messagingService->deleteMessage($message, auth()->user());

        return $this->response->success(null, 'Message deleted.');
    }
}
