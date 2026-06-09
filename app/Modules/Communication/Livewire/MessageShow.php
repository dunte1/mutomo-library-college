<?php

namespace App\Modules\Communication\Livewire;

use App\Modules\Communication\Models\Message;
use App\Modules\Communication\Services\MessagingService;
use Livewire\Component;

class MessageShow extends Component
{
    public Message $message;

    public function mount(int $id, MessagingService $messagingService): void
    {
        $this->message = Message::with(['sender', 'recipients.recipient', 'attachments'])
            ->findOrFail($id);

        $messagingService->markAsRead(auth()->user(), $id);
    }

    public function delete(MessagingService $messagingService): void
    {
        $messagingService->deleteMessage($this->message, auth()->user());
        $this->dispatch('notify', message: 'Message deleted.', type: 'success');
        $this->redirect(route('communication.messages.index'), navigate: true);
    }

    public function render()
    {
        return view('communication::livewire.message-show', [
            'message' => $this->message,
        ]);
    }
}
