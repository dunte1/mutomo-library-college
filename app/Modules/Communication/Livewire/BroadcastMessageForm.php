<?php

namespace App\Modules\Communication\Livewire;

use App\Models\User;
use App\Modules\Communication\Models\Message;
use App\Modules\Communication\Services\MessagingService;
use Livewire\Component;

class BroadcastMessageForm extends Component
{
    public string $subject = '';

    public string $body = '';

    public string $targetType = 'all';

    public ?int $roleId = null;

    public bool $sendEmail = false;

    protected function rules(): array
    {
        return [
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'targetType' => 'required|in:all,staff,students',
            'sendEmail' => 'boolean',
        ];
    }

    public function send(): void
    {
        $this->authorize('manage-broadcasts');
        $this->validate();

        try {
            $service = app(MessagingService::class);
            $user = auth()->user();

            $data = [
                'subject' => $this->subject,
                'body' => $this->body,
                'type' => Message::TYPE_BROADCAST,
                'priority' => Message::PRIORITY_NORMAL,
            ];

            if ($this->targetType === 'staff') {
                $data['recipients'] = User::role(['super-admin', 'admin', 'librarian', 'assistant-librarian', 'finance-officer', 'ict-admin', 'department-head'])
                    ->where('is_active', true)
                    ->pluck('id')
                    ->toArray();
                $data['type'] = Message::TYPE_GROUP;
            } elseif ($this->targetType === 'students') {
                $data['recipients'] = User::role(['student', 'lecturer'])
                    ->where('is_active', true)
                    ->pluck('id')
                    ->toArray();
                $data['type'] = Message::TYPE_GROUP;
            }

            $service->sendMessage($user, $data);

            $this->dispatch('notify', type: 'success', message: 'Broadcast message sent successfully.');
            $this->reset(['subject', 'body', 'targetType', 'sendEmail']);
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to send broadcast message.');
        }
    }

    public function render()
    {
        return view('communication::livewire.broadcast-message-form');
    }
}
