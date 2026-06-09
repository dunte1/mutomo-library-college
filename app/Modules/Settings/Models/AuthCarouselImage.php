<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class AuthCarouselImage extends Model
{
    protected $fillable = [
        'image_path',
        'title',
        'subtitle',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }
}
