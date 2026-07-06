<?php

namespace App\Modules\Communication\Livewire;

use App\Modules\Communication\Models\Message;
use App\Modules\Communication\Services\MessagingService;
use Livewire\Component;
use Livewire\WithPagination;

class MessageList extends Component
{
    use WithPagination;

    public string $tab = 'inbox';

    public string $search = '';

    public string $typeFilter = '';

    public string $priorityFilter = '';

    protected $queryString = ['tab', 'search', 'typeFilter', 'priorityFilter'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $id, MessagingService $messagingService): void
    {
        $message = Message::findOrFail($id);
        $messagingService->deleteMessage($message, auth()->user());
        $this->dispatch('notify', message: 'Message deleted successfully.', type: 'success');
    }

    public function cancelScheduled(int $id, MessagingService $messagingService): void
    {
        $message = Message::findOrFail($id);
        $messagingService->cancelScheduledMessage($message, auth()->user());
        $this->dispatch('notify', message: 'Scheduled message cancelled.', type: 'success');
    }

    public function render(MessagingService $messagingService)
    {
        $user = auth()->user();

        if ($this->tab === 'sent') {
            $messages = $messagingService->getSentMessages($user, 15);
        } elseif ($this->tab === 'drafts') {
            $messages = $messagingService->getDrafts($user, 15);
        } elseif ($this->tab === 'scheduled') {
            $messages = $messagingService->getScheduledMessages($user, 15);
        } else {
            $messages = $messagingService->getInbox($user, 15);
        }

        $stats = $messagingService->getMessageStats();
        $draftCount = Message::where('sender_id', $user->id)
            ->byStatus(Message::STATUS_DRAFT)
            ->count();

        return view('communication::livewire.message-list', [
            'messages' => $messages,
            'stats' => $stats,
            'unreadCount' => $messagingService->getUnreadCount($user),
            'draftCount' => $draftCount,
        ]);
    }
}
