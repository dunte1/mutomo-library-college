<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Shared\Traits\Auditable;

class Author extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'slug',
        'biography',
        'birth_date',
        'death_date',
        'nationality',
        'photo',
        'website',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'death_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function books(): BelongsToMany
    {
        return $this->belongsToMany(Book::class, 'book_author')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%");
    }
}
