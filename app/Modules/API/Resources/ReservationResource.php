<?php

namespace App\Modules\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'book' => $this->whenLoaded('book', fn () => [
                'id' => $this->book->id,
                'title' => $this->book->title,
                'cover_image' => $this->book->cover_image ? url('storage/'.$this->book->cover_image) : null,
                'authors' => $this->book->relationLoaded('authors')
                    ? $this->book->authors->map(fn ($a) => ['name' => $a->name])
                    : [],
            ]),
            'status' => $this->status,
            'reserved_at' => $this->reserved_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'days_remaining' => $this->expires_at ? (int) max(0, now()->diffInDays($this->expires_at, false)) : null,
            'notes' => $this->notes,
        ];
    }
}
