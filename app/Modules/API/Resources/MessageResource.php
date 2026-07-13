<?php

namespace App\Modules\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sender' => $this->whenLoaded('sender', fn () => [
                'id' => $this->sender->id,
                'name' => $this->sender->name,
                'avatar' => $this->sender->avatar ? url('storage/'.$this->sender->avatar) : null,
            ]),
            'subject' => $this->subject,
            'body' => $this->body,
            'body_preview' => mb_substr(strip_tags($this->body), 0, 150),
            'priority' => $this->priority,
            'type' => $this->type,
            'is_read' => $this->whenPivotLoaded('message_recipients', fn () => (bool) $this->pivot?->is_read, null),
            'read_at' => $this->whenPivotLoaded('message_recipients', fn () => $this->pivot?->read_at?->toIso8601String(), null),
            'has_attachments' => $this->relationLoaded('attachments') && $this->attachments->isNotEmpty(),
            'attachments' => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($a) => [
                'id' => $a->id,
                'file_name' => $a->file_name,
                'file_size' => $a->file_size,
                'mime_type' => $a->mime_type,
                'url' => url('storage/'.$a->file_path),
            ])),
            'replies_count' => $this->whenCounted('replies', fn () => (int) $this->replies_count),
            'replies' => $this->whenLoaded('replies', fn () => self::collection($this->replies)),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
