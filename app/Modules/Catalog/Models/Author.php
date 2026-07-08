<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Shared\Traits\Auditable;
use App\Modules\Shared\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use Auditable, Searchable, SoftDeletes;

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
        return $query->whereLike('name', $term);
    }
}
