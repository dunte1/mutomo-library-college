<?php

namespace App\Modules\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'biography' => $this->biography,
            'nationality' => $this->nationality,
            'photo' => $this->photo ? url('storage/'.$this->photo) : null,
            'birth_date' => $this->birth_date?->toDateString(),
            'death_date' => $this->death_date?->toDateString(),
            'books_count' => $this->whenCounted('books', fn () => $this->books_count),
        ];
    }
}
