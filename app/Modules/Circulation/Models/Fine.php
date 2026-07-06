<?php

namespace App\Modules\Circulation\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;
    protected $fillable = [
        'borrow_record_id',
        'user_id',
        'type',
        'amount',
        'paid_amount',
        'waived_amount',
        'status',
        'reason',
        'notes',
        'assessed_at',
        'paid_at',
        'waived_at',
        'waived_by',
        'assessed_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'waived_amount' => 'decimal:2',
            'assessed_at' => 'datetime',
            'paid_at' => 'datetime',
            'waived_at' => 'datetime',
        ];
    }

    const STATUS_PENDING = 'pending';

    const STATUS_PAID = 'paid';

    const STATUS_WAIVED = 'waived';

    const STATUS_DISPUTED = 'disputed';

    public function borrowRecord()
    {
        return $this->belongsTo(BorrowRecord::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return $this->amount - $this->paid_amount - $this->waived_amount;
    }

    public function isFullyPaid(): bool
    {
        return $this->outstanding_balance <= 0;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
