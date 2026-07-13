<?php

namespace App\Modules\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => (int) $this->parent_id,
            'books_count' => $this->whenCounted('books', fn () => (int) $this->books_count),
            'sort_order' => (int) $this->sort_order,
            'children' => self::collection($this->whenLoaded('children')),
        ];
    }
}
