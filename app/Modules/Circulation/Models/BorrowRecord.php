<?php

namespace App\Modules\Circulation\Models;

use App\Models\User;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Shared\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BorrowRecord extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'book_copy_id',
        'borrowed_at',
        'due_at',
        'returned_at',
        'renewed_at',
        'renewal_count',
        'max_renewals',
        'status',
        'notes',
        'issued_by',
        'received_by',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'user_id' => 'integer',
            'book_copy_id' => 'integer',
            'issued_by' => 'integer',
            'received_by' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
            'renewed_at' => 'datetime',
            'renewal_count' => 'integer',
            'max_renewals' => 'integer',
        ];
    }

    const STATUS_ACTIVE = 'active';

    const STATUS_RETURNED = 'returned';

    const STATUS_OVERDUE = 'overdue';

    const STATUS_LOST = 'lost';

    const STATUS_DAMAGED = 'damaged';

    // Relationships

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookCopy()
    {
        return $this->belongsTo(BookCopy::class, 'book_copy_id');
    }

    // Alias for bookCopy() — supports legacy calls using ->copy or with('copy')
    public function copy()
    {
        return $this->belongsTo(BookCopy::class, 'book_copy_id');
    }

    public function fine()
    {
        return $this->hasOne(Fine::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Helper methods

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE
            || ($this->isActive() && $this->due_at->isPast());
    }

    public function daysOverdue(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        $from = $this->due_at->isPast() ? $this->due_at : now();

        return (int) $from->diffInDays(now());
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_OVERDUE]);
    }

    public function scopeOverdue($query)
    {
        return $query->where(function ($q) {
            $q->where('status', self::STATUS_OVERDUE)
                ->orWhere(function ($q2) {
                    $q2->where('status', self::STATUS_ACTIVE)
                        ->where('due_at', '<', now());
                });
        });
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('borrowed_at', [$from, $to]);
    }
}
