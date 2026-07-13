<?php

namespace App\Modules\API\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DigitalAssetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'file_type' => $this->file_type,
            'file_size' => (int) $this->file_size,
            'file_extension' => $this->file_extension,
            'file_url' => $this->file_path ? url('storage/'.$this->file_path) : null,
            'cover_image' => $this->cover_image ? url('storage/'.$this->cover_image) : null,
            'thumbnail_url' => $this->cover_image ? url('storage/'.$this->cover_image) : null,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'isbn' => $this->isbn,
            'publication_year' => (int) $this->publication_year,
            'language' => $this->language,
            'keywords' => $this->keywords,
            'access_level' => $this->access_level,
            'allow_download' => $this->allow_download,
            'allow_printing' => $this->allow_printing,
            'times_downloaded' => (int) $this->times_downloaded,
            'times_viewed' => (int) $this->times_viewed,
            'download_count' => (int) $this->times_downloaded,
            'is_featured' => $this->is_featured,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
