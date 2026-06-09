<?php

namespace App\Modules\Communication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'type',
        'status',
        'published_at',
        'expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
