<?php

namespace App\Modules\Communication\Livewire;

use App\Modules\Communication\Models\Message;
use App\Modules\Communication\Services\MessagingService;
use Livewire\Component;

class MessageShow extends Component
{
    public Message $message;

    public string $replyBody = '';

    public bool $showReplyAll = false;

    public bool $showForward = false;

    public string $forwardRecipientSearch = '';

    public array $selectedForwardRecipients = [];

    public function mount(int $id, MessagingService $messagingService): void
    {
        $this->message = Message::with(['sender', 'recipients.recipient', 'attachments', 'replies.sender'])
            ->findOrFail($id);

        $messagingService->markAsRead(auth()->user(), $id);
    }

    public function sendReply(MessagingService $messagingService): void
    {
        $this->authorize('reply-messages');
        $this->validate(['replyBody' => ['required', 'string']]);

        $messagingService->replyToMessage(
            auth()->user(),
            $this->message,
            $this->replyBody,
        );

        $this->replyBody = '';
        $this->message->load('replies.sender');

        $this->dispatch('notify', message: 'Reply sent.', type: 'success');
    }

    public function sendReplyAll(MessagingService $messagingService): void
    {
        $this->authorize('reply-all-messages');
        $this->validate(['replyBody' => ['required', 'string']]);

        $messagingService->replyAllToMessage(
            auth()->user(),
            $this->message,
            $this->replyBody,
        );

        $this->replyBody = '';
        $this->showReplyAll = false;
        $this->message->load('replies.sender');

        $this->dispatch('notify', message: 'Reply sent to all.', type: 'success');
    }

    public function toggleForward(): void
    {
        $this->authorize('forward-messages');
        $this->showForward = ! $this->showForward;
        $this->selectedForwardRecipients = [];
        $this->forwardRecipientSearch = '';
    }

    public function removeForwardRecipient(int $userId): void
    {
        $this->selectedForwardRecipients = array_values(
            array_diff($this->selectedForwardRecipients, [$userId])
        );
    }

    public function sendForward(MessagingService $messagingService): void
    {
        $this->authorize('forward-messages');
        $this->validate(['selectedForwardRecipients' => ['required', 'array', 'min:1']]);

        $messagingService->forwardMessage(
            auth()->user(),
            $this->message,
            $this->selectedForwardRecipients,
        );

        $this->showForward = false;
        $this->selectedForwardRecipients = [];
        $this->forwardRecipientSearch = '';

        $this->dispatch('notify', message: 'Message forwarded.', type: 'success');
    }

    public function delete(MessagingService $messagingService): void
    {
        $messagingService->deleteMessage($this->message, auth()->user());
        $this->dispatch('notify', message: 'Message deleted.', type: 'success');
        $this->redirect(route('communication.messages.index'), navigate: true);
    }

    public function render()
    {
        $forwardUsers = [];
        if ($this->showForward) {
            $forwardUsers = \App\Models\User::where('is_active', true)
                ->when($this->forwardRecipientSearch, fn ($q) => $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->forwardRecipientSearch}%")
                        ->orWhere('email', 'like', "%{$this->forwardRecipientSearch}%");
                }))
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        return view('communication::livewire.message-show', [
            'message' => $this->message,
            'forwardUsers' => $forwardUsers,
        ]);
    }
}
