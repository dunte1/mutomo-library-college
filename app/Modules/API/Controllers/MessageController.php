<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Resources\MessageResource;
use App\Modules\API\Services\ApiResponseService;
use App\Modules\Communication\Models\Message;
use App\Modules\Communication\Models\MessageRecipient;
use App\Modules\Communication\Models\MessageTemplate;
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

    public function forward(int $id): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'recipient_ids' => ['required', 'array', 'min:1'],
            'recipient_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $original = Message::findOrFail($id);
        $message = $this->messagingService->forwardMessage(
            auth()->user(),
            $original,
            $data['recipient_ids']
        );

        return $this->response->success(
            new MessageResource($message->load('sender')),
            'Message forwarded.'
        );
    }

    public function search(): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'q' => ['required', 'string', 'max:255'],
            'scope' => ['nullable', 'string', 'in:inbox,sent,all'],
        ]);

        $user = auth()->user();
        $query = request('q');
        $scope = request('scope', 'inbox');

        $messages = Message::query()
            ->when($scope === 'inbox' || $scope === 'all', fn ($q) => $q->orWhereHas(
                'recipients',
                fn ($r) => $r->where('recipient_id', $user->id)
            ))
            ->when($scope === 'sent' || $scope === 'all', fn ($q) => $q->orWhere('sender_id', $user->id))
            ->where(function ($q) use ($query) {
                $q->where('subject', 'like', "%{$query}%")
                  ->orWhere('body', 'like', "%{$query}%");
            })
            ->with(['sender', 'recipients.recipient'])
            ->orderByDesc('created_at')
            ->paginate(min((int) (request('per_page', 15)), 100));

        $messages->getCollection()->transform(fn ($msg) => new MessageResource($msg));

        return $this->response->paginated($messages);
    }

    public function unreadCount(): \Illuminate\Http\JsonResponse
    {
        $count = $this->messagingService->getUnreadCount(auth()->user());

        return $this->response->success(['unread_count' => $count]);
    }

    public function markUnread(int $id): \Illuminate\Http\JsonResponse
    {
        $recipient = MessageRecipient::where('message_id', $id)
            ->where('recipient_id', auth()->id())
            ->firstOrFail();

        $recipient->update(['is_read' => false, 'read_at' => null]);

        return $this->response->success(null, 'Message marked as unread.');
    }

    public function archive(int $id): \Illuminate\Http\JsonResponse
    {
        $recipient = MessageRecipient::where('message_id', $id)
            ->where('recipient_id', auth()->id())
            ->firstOrFail();

        $recipient->delete();

        return $this->response->success(null, 'Message archived.');
    }

    public function archived(): \Illuminate\Http\JsonResponse
    {
        $messages = MessageRecipient::onlyTrashed()
            ->with(['message.sender'])
            ->byRecipient(auth()->id())
            ->orderByDesc('deleted_at')
            ->paginate(min((int) (request('per_page', 15)), 100));

        $messages->getCollection()->transform(fn ($recipient) => new MessageResource(
            $recipient->message->load(['sender', 'replies'])
        ));

        return $this->response->paginated($messages);
    }

    public function unarchive(int $id): \Illuminate\Http\JsonResponse
    {
        $recipient = MessageRecipient::onlyTrashed()
            ->where('message_id', $id)
            ->where('recipient_id', auth()->id())
            ->firstOrFail();

        $recipient->restore();

        return $this->response->success(null, 'Message restored from archive.');
    }

    // --- Message Templates ---

    public function templatesIndex(): \Illuminate\Http\JsonResponse
    {
        $templates = MessageTemplate::active()
            ->orderBy('name')
            ->paginate(min((int) (request('per_page', 50)), 100));

        return $this->response->paginated($templates);
    }

    public function templatesStore(): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['string'],
        ]);

        $template = MessageTemplate::create([
            ...$data,
            'created_by' => auth()->id(),
        ]);

        return $this->response->success($template, 'Template created.', 201);
    }

    public function templatesShow(int $id): \Illuminate\Http\JsonResponse
    {
        $template = MessageTemplate::findOrFail($id);

        return $this->response->success($template);
    }

    public function templatesUpdate(int $id): \Illuminate\Http\JsonResponse
    {
        $template = MessageTemplate::findOrFail($id);

        $data = request()->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'subject' => ['sometimes', 'string', 'max:255'],
            'body' => ['sometimes', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'variables' => ['nullable', 'array'],
            'variables.*' => ['string'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $template->update($data);

        return $this->response->success($template, 'Template updated.');
    }

    public function templatesDestroy(int $id): \Illuminate\Http\JsonResponse
    {
        $template = MessageTemplate::findOrFail($id);
        $template->delete();

        return $this->response->success(null, 'Template deleted.');
    }

    public function templatesApply(int $id): \Illuminate\Http\JsonResponse
    {
        $template = MessageTemplate::findOrFail($id);

        $data = request()->validate([
            'variables' => ['nullable', 'array'],
        ]);

        $rendered = $this->messagingService->applyTemplate($template, $data['variables'] ?? []);

        return $this->response->success($rendered);
    }

    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        $message = Message::findOrFail($id);
        $this->messagingService->deleteMessage($message, auth()->user());

        return $this->response->success(null, 'Message deleted.');
    }
}
