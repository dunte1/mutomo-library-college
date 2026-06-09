<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
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
            $q->where('email', 'like', "%{$term}%")
              ->orWhere('name', 'like', "%{$term}%");
        });
    }
}
