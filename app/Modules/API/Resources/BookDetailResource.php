<?php

namespace App\Modules\API\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'isbn' => $this->isbn,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'slug' => $this->slug,
            'description' => $this->description,
            'language' => $this->language,
            'pages' => $this->pages,
            'publication_year' => $this->publication_year,
            'edition' => $this->edition,
            'volume' => $this->volume,
            'series' => $this->series,
            'cover_image' => $this->cover_image ? url('storage/'.$this->cover_image) : null,
            'condition' => $this->condition,
            'status' => $this->status,
            'price' => $this->price,
            'dewey_decimal' => $this->dewey_decimal,
            'lc_classification' => $this->lc_classification,
            'tags' => $this->tags,
            'is_featured' => $this->is_featured,
            'category' => $this->whenLoaded('category', fn () => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
            'authors' => $this->whenLoaded('authors', fn () => $this->authors->map(fn ($author) => [
                'id' => $author->id,
                'name' => $author->name,
                'slug' => $author->slug,
            ])),
            'publisher' => $this->whenLoaded('publisher', fn () => $this->publisher ? [
                'id' => $this->publisher->id,
                'name' => $this->publisher->name,
                'slug' => $this->publisher->slug,
            ] : null),
            'subjects' => $this->whenLoaded('subjects', fn () => $this->subjects->map(fn ($subject) => [
                'id' => $subject->id,
                'name' => $subject->name,
            ])),
            'copies' => $this->whenLoaded('copies', fn () => $this->copies->map(fn ($copy) => [
                'id' => $copy->id,
                'barcode' => $copy->barcode,
                'shelf_location' => $copy->shelf_location,
                'status' => $copy->status,
                'condition' => $copy->condition,
                'current_borrow' => $copy->relationLoaded('currentBorrow') && $copy->currentBorrow ? [
                    'due_at' => $copy->currentBorrow->due_at->toIso8601String(),
                ] : null,
            ])),
            'total_copies' => $this->total_copies,
            'available_copies' => $this->available_copies,
            'average_rating' => $this->average_rating,
            'reviews_count' => $this->reviews_count,
            'digital_assets' => $this->whenLoaded('digitalAssets', fn () => $this->digitalAssets->map(fn ($asset) => [
                'id' => $asset->id,
                'title' => $asset->title,
                'file_type' => $asset->file_type,
            ])),
            'can_reserve' => $this->available_copies === 0,
            'can_borrow' => $this->available_copies > 0,
        ];
    }
}
