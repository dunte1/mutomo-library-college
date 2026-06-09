<?php

namespace App\Modules\Circulation\Models;

use App\Models\User;
use App\Modules\Catalog\Models\Book;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        'reserved_at',
        'expires_at',
        'notified_at',
        'fulfilled_at',
        'cancelled_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reserved_at' => 'datetime',
            'expires_at' => 'datetime',
            'notified_at' => 'datetime',
            'fulfilled_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    const STATUS_PENDING = 'pending';
    const STATUS_FULFILLED = 'fulfilled';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
