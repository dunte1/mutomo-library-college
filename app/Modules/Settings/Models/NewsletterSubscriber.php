<?php

namespace App\Modules\Settings\Models;

use App\Modules\Shared\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    use Searchable;
    protected $fillable = [
        'email',
        'name',
        'subscribed_at',
        'unsubscribed_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->whereLike('email', $term)
                ->orWhereLike('name', $term);
        });
    }
}
