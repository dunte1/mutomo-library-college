<?php

namespace App\Modules\Settings\Models;

use App\Modules\Shared\Traits\Auditable;
use App\Modules\Shared\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use Auditable, Searchable, SoftDeletes;

    protected $fillable = [
        'author_name',
        'author_role',
        'content',
        'rating',
        'status',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->whereLike('author_name', $term)
                ->orWhereLike('author_role', $term)
                ->orWhereLike('content', $term);
        });
    }

    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', $this->author_name);
        $initials = '';
        foreach ($parts as $part) {
            if (! empty(trim($part))) {
                $initials .= strtoupper(mb_substr($part, 0, 1));
            }
        }

        return $initials ?: '?';
    }
}
