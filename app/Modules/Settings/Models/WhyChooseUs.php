<?php

namespace App\Modules\Settings\Models;

use App\Modules\Shared\Traits\Auditable;
use App\Modules\Shared\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhyChooseUs extends Model
{
    use Auditable, Searchable, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'icon',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->whereLike('title', $term)
                ->orWhereLike('description', $term);
        });
    }
}
