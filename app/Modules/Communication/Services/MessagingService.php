<?php

namespace App\Modules\Communication\Services;

use App\Mail\NotificationMail;
use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use App\Modules\Communication\Models\CommunicationAnalytic;
use App\Modules\Communication\Models\Message;
use App\Modules\Communication\Models\MessageAttachment;
use App\Modules\Communication\Models\MessageRecipient;
use App\Modules\Communication\Models\MessageTemplate;
use App\Modules\Communication\Models\NotificationLog;
use App\Modules\Notifications\Services\NotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MessagingService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected SmsService $smsService,
        protected PushNotificationService $pushService,
    ) {}

    public function sendMessage(User $sender, array $data): Message
    {
        return DB::transaction(function () use ($sender, $data) {
            $message = Message::create([
                'sender_id' => $sender->id,
                'subject' => $data['subject'],
                'body' => $data['body'],
                'priority' => $data['priority'] ?? Message::PRIORITY_NORMAL,
                'type' => $data['type'] ?? Message::TYPE_DIRECT,
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'status' => isset($data['scheduled_at']) ? Message::STATUS_SCHEDULED : Message::STATUS_SENT,
                'sent_at' => isset($data['scheduled_at']) ? null : now(),
            ]);

            $this->addRecipients($message, $data);
            $this->handleAttachments($message, $data['attachments'] ?? []);

            if ($message->status === Message::STATUS_SENT) {
                $this->deliverMessage($message);
            }

            activity()
                ->performedOn($message)
                ->causedBy($sender)
                ->withProperties(['type' => $message->type, 'subject' => $message->subject])
                ->log("Message sent: {$message->subject}");

            return $message;
        });
    }

    protected function addRecipients(Message $message, array $data): void
    {
        if ($message->type === Message::TYPE_DIRECT && ! empty($data['recipients'])) {
            foreach ($data['recipients'] as $recipientId) {
                MessageRecipient::create([
                    'message_id' => $message->id,
                    'recipient_id' => $recipientId,
                    'copy_type' => 'to',
                ]);
            }
        }

        if ($message->type === Message::TYPE_GROUP && ! empty($data['recipients'])) {
            foreach ($data['recipients'] as $recipientId) {
                MessageRecipient::create([
                    'message_id' => $message->id,
                    'recipient_id' => $recipientId,
                    'copy_type' => 'to',
                ]);
            }
        }

        if ($message->type === Message::TYPE_BROADCAST) {
            User::where('is_active', true)->chunk(100, function ($users) use ($message) {
                foreach ($users as $user) {
                    MessageRecipient::create([
                        'message_id' => $message->id,
                        'recipient_id' => $user->id,
                        'recipient_type' => 'all',
                    ]);
                }
            });
        }

        if ($message->type === Message::TYPE_DEPARTMENT && ! empty($data['department_id'])) {
            $department = Department::find($data['department_id']);
            if ($department) {
                $userIds = User::where('department_id', $department->id)
                    ->where('is_active', true)
                    ->pluck('id');
                foreach ($userIds as $userId) {
                    MessageRecipient::create([
                        'message_id' => $message->id,
                        'recipient_id' => $userId,
                        'recipient_type' => "department:{$department->id}",
                    ]);
                }
            }
        }

        if ($message->type === Message::TYPE_PROGRAM && ! empty($data['program_id'])) {
            $program = Program::find($data['program_id']);
            if ($program) {
                $userIds = User::where('program_id', $program->id)
                    ->where('is_active', true)
                    ->pluck('id');
                foreach ($userIds as $userId) {
                    MessageRecipient::create([
                        'message_id' => $message->id,
                        'recipient_id' => $userId,
                        'recipient_type' => "program:{$program->id}",
                    ]);
                }
            }
        }
    }

    protected function handleAttachments(Message $message, array $attachments): void
    {
        foreach ($attachments as $attachment) {
            if ($attachment instanceof UploadedFile) {
                $path = $attachment->store('message-attachments/'.$message->id, 'public');
                MessageAttachment::create([
                    'message_id' => $message->id,
                    'file_path' => $path,
                    'file_name' => $attachment->getClientOriginalName(),
                    'file_size' => $attachment->getSize(),
                    'mime_type' => $attachment->getMimeType(),
                ]);
            }
        }
    }

    protected function shouldNotifyViaChannel(User $user, string $channel): bool
    {
        $prefs = $user->notification_preferences ?? [];

        return match ($channel) {
            'in_app' => $prefs['in_app'] ?? true,
            'email'  => $prefs['email'] ?? true,
            'push'   => $prefs['push'] ?? true,
            'sms'    => $prefs['sms'] ?? false,
            default  => true,
        };
    }

    protected function deliverMessage(Message $message): void
    {
        $message->recipients()->chunk(50, function ($recipients) use ($message) {
            foreach ($recipients as $recipient) {
                if (! $recipient->recipient) {
                    continue;
                }

                if ($this->shouldNotifyViaChannel($recipient->recipient, 'in_app')) {
                    $this->notificationService->send(
                        $recipient->recipient,
                        'message',
                        $message->subject,
                        $message->body,
                        'chat',
                        route('communication.messages.show', $message->id),
                    );
                }

                CommunicationAnalytic::create([
                    'message_id' => $message->id,
                    'event_type' => 'sent',
                    'user_id' => $recipient->recipient_id,
                ]);

                $recipient->markAsDelivered();
            }
        });

        if ($message->type === Message::TYPE_BROADCAST) {
            $this->broadcastViaEmail($message);
        }
    }

    protected function broadcastViaEmail(Message $message): void
    {
        $message->recipients()->chunk(50, function ($recipients) use ($message) {
            foreach ($recipients as $recipient) {
                if (! $recipient->recipient || ! $recipient->recipient->email) {
                    continue;
                }
                if (! $this->shouldNotifyViaChannel($recipient->recipient, 'email')) {
                    continue;
                }
                try {
                    Mail::to($recipient->recipient)->queue(
                        new NotificationMail($message->subject, $message->body)
                    );
                    $this->logNotification($recipient->recipient, 'mail', $message);
                } catch (\Throwable $e) {
                    $this->logNotification($recipient->recipient, 'mail', $message, $e->getMessage());
                }
            }
        });
    }

    protected function logNotification($user, string $channel, Message $message, ?string $error = null): void
    {
        NotificationLog::create([
            'notifiable_type' => get_class($user),
            'notifiable_id' => $user->id,
            'channel' => $channel,
            'type' => 'message_'.$message->type,
            'title' => $message->subject,
            'body' => $message->body,
            'status' => $error ? 'failed' : 'sent',
            'sent_at' => now(),
            'error' => $error,
            'metadata' => ['message_id' => $message->id],
        ]);
    }

    public function getInbox(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return MessageRecipient::with(['message.sender'])
            ->byRecipient($user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getSentMessages(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Message::with(['recipients.recipient'])
            ->where('sender_id', $user->id)
            ->where('status', '!=', Message::STATUS_DRAFT)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getScheduledMessages(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Message::with(['recipients.recipient'])
            ->where('sender_id', $user->id)
            ->scheduled()
            ->orderBy('scheduled_at')
            ->paginate($perPage);
    }

    public function cancelScheduledMessage(Message $message, User $user): void
    {
        if ($message->sender_id !== $user->id || $message->status !== Message::STATUS_SCHEDULED) {
            abort(403);
        }

        $message->update(['status' => Message::STATUS_DRAFT, 'scheduled_at' => null]);

        activity()
            ->performedOn($message)
            ->causedBy($user)
            ->log("Scheduled message cancelled: {$message->subject}");
    }

    public function getDrafts(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Message::with(['recipients.recipient'])
            ->where('sender_id', $user->id)
            ->byStatus(Message::STATUS_DRAFT)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function saveDraft(User $sender, array $data): Message
    {
        return DB::transaction(function () use ($sender, $data) {
            if (! empty($data['message_id'])) {
                $message = Message::where('sender_id', $sender->id)
                    ->where('status', Message::STATUS_DRAFT)
                    ->findOrFail($data['message_id']);
                $message->update([
                    'subject' => $data['subject'],
                    'body' => $data['body'],
                    'priority' => $data['priority'] ?? Message::PRIORITY_NORMAL,
                    'type' => $data['type'] ?? Message::TYPE_DIRECT,
                ]);
                $message->recipients()->delete();
                $message->attachments()->delete();
            } else {
                $message = Message::create([
                    'sender_id' => $sender->id,
                    'subject' => $data['subject'],
                    'body' => $data['body'],
                    'priority' => $data['priority'] ?? Message::PRIORITY_NORMAL,
                    'type' => $data['type'] ?? Message::TYPE_DIRECT,
                    'status' => Message::STATUS_DRAFT,
                ]);
            }

            $this->addRecipients($message, $data);
            $this->handleAttachments($message, $data['attachments'] ?? []);

            activity()
                ->performedOn($message)
                ->causedBy($sender)
                ->log("Message saved as draft: {$message->subject}");

            return $message;
        });
    }

    public function getUnreadCount(User $user): int
    {
        return MessageRecipient::byRecipient($user->id)->unread()->count();
    }

    public function markAsRead(User $user, int $messageId): void
    {
        $recipient = MessageRecipient::where('message_id', $messageId)
            ->where('recipient_id', $user->id)
            ->first();

        if ($recipient && ! $recipient->is_read) {
            $recipient->markAsRead();

            CommunicationAnalytic::create([
                'message_id' => $messageId,
                'event_type' => 'read',
                'user_id' => $user->id,
            ]);
        }
    }

    public function applyTemplate(MessageTemplate $template, array $data = []): array
    {
        return $template->render($data);
    }

    public function sendScheduledMessages(): int
    {
        $count = 0;
        Message::scheduled()
            ->where('scheduled_at', '<=', now())
            ->chunk(50, function ($messages) use (&$count) {
                foreach ($messages as $message) {
                    try {
                        $message->markAsSent();
                        $this->deliverMessage($message);
                        $count++;
                    } catch (\Throwable $e) {
                        $message->markAsFailed();
                        report($e);
                    }
                }
            });

        return $count;
    }

    public function deleteMessage(Message $message, User $user): void
    {
        if ($message->sender_id !== $user->id && ! $user->can('manage-broadcasts')) {
            abort(403);
        }

        activity()
            ->performedOn($message)
            ->causedBy($user)
            ->log("Message deleted: {$message->subject}");

        $message->delete();
    }

    public function getMessageStats(): array
    {
        return [
            'total_sent' => Message::sent()->count(),
            'total_scheduled' => Message::scheduled()->count(),
            'total_drafts' => Message::byStatus(Message::STATUS_DRAFT)->count(),
            'total_failed' => Message::byStatus(Message::STATUS_FAILED)->count(),
            'total_read' => MessageRecipient::where('is_read', true)->count(),
            'total_unread' => MessageRecipient::unread()->count(),
            'messages_this_month' => Message::sent()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    public function sendFromTemplate(User $sender, MessageTemplate $template, array $recipientIds, array $variables = []): Message
    {
        $rendered = $template->render($variables);

        return $this->sendMessage($sender, [
            'subject' => $rendered['subject'],
            'body' => $rendered['body'],
            'recipients' => $recipientIds,
            'type' => Message::TYPE_DIRECT,
            'priority' => Message::PRIORITY_NORMAL,
        ]);
    }

    public function replyToMessage(User $sender, Message $parent, string $body): Message
    {
        return DB::transaction(function () use ($sender, $parent, $body) {
            $message = Message::create([
                'parent_id' => $parent->id,
                'sender_id' => $sender->id,
                'subject' => 'Re: '.$parent->subject,
                'body' => $body,
                'priority' => Message::PRIORITY_NORMAL,
                'type' => Message::TYPE_DIRECT,
                'status' => Message::STATUS_SENT,
                'sent_at' => now(),
            ]);

            MessageRecipient::create([
                'message_id' => $message->id,
                'recipient_id' => $parent->sender_id,
                'copy_type' => 'to',
            ]);

            $this->deliverMessage($message);

            activity()
                ->performedOn($parent)
                ->causedBy($sender)
                ->withProperties(['reply_id' => $message->id, 'subject' => $message->subject])
                ->log("Reply sent: {$message->subject}");

            return $message;
        });
    }

    public function replyAllToMessage(User $sender, Message $parent, string $body): Message
    {
        return DB::transaction(function () use ($sender, $parent, $body) {
            $message = Message::create([
                'parent_id' => $parent->id,
                'sender_id' => $sender->id,
                'subject' => 'Re: '.$parent->subject,
                'body' => $body,
                'priority' => Message::PRIORITY_NORMAL,
                'type' => Message::TYPE_DIRECT,
                'status' => Message::STATUS_SENT,
                'sent_at' => now(),
            ]);

            $recipientIds = $parent->recipients()
                ->where('recipient_id', '!=', $sender->id)
                ->pluck('recipient_id')
                ->push($parent->sender_id)
                ->unique()
                ->filter()
                ->values()
                ->toArray();

            foreach ($recipientIds as $recipientId) {
                MessageRecipient::create([
                    'message_id' => $message->id,
                    'recipient_id' => $recipientId,
                    'copy_type' => 'to',
                ]);
            }

            $this->deliverMessage($message);

            activity()
                ->performedOn($parent)
                ->causedBy($sender)
                ->withProperties(['reply_all_id' => $message->id, 'subject' => $message->subject])
                ->log("Reply all sent: {$message->subject}");

            return $message;
        });
    }

    public function forwardMessage(User $sender, Message $original, array $recipientIds): Message
    {
        return DB::transaction(function () use ($sender, $original, $recipientIds) {
            $message = Message::create([
                'sender_id' => $sender->id,
                'subject' => 'Fwd: '.$original->subject,
                'body' => $original->body,
                'priority' => Message::PRIORITY_NORMAL,
                'type' => Message::TYPE_DIRECT,
                'status' => Message::STATUS_SENT,
                'sent_at' => now(),
            ]);

            foreach ($recipientIds as $recipientId) {
                MessageRecipient::create([
                    'message_id' => $message->id,
                    'recipient_id' => $recipientId,
                    'copy_type' => 'to',
                ]);
            }

            foreach ($original->attachments as $attachment) {
                MessageAttachment::create([
                    'message_id' => $message->id,
                    'file_path' => $attachment->file_path,
                    'file_name' => $attachment->file_name,
                    'file_size' => $attachment->file_size,
                    'mime_type' => $attachment->mime_type,
                ]);
            }

            $this->deliverMessage($message);

            activity()
                ->performedOn($original)
                ->causedBy($sender)
                ->withProperties(['forward_id' => $message->id, 'subject' => $message->subject])
                ->log("Message forwarded: {$message->subject}");

            return $message;
        });
    }
}
