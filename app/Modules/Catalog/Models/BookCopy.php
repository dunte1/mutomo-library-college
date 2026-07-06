<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Shared\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookCopy extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'book_id',
        'barcode',
        'rfid_tag',
        'shelf_location',
        'status',
        'condition',
        'acquired_at',
        'price',
        'notes',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'acquired_at' => 'date',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    const STATUS_AVAILABLE = 'available';

    const STATUS_BORROWED = 'borrowed';

    const STATUS_RESERVED = 'reserved';

    const STATUS_DAMAGED = 'damaged';

    const STATUS_LOST = 'lost';

    const STATUS_WITHDRAWN = 'withdrawn';

    const CONDITION_NEW = 'new';

    const CONDITION_GOOD = 'good';

    const CONDITION_FAIR = 'fair';

    const CONDITION_POOR = 'poor';

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function borrowRecords()
    {
        return $this->hasMany(BorrowRecord::class);
    }

    public function currentBorrow()
    {
        return $this->hasOne(BorrowRecord::class)
            ->whereNull('returned_at')
            ->latest('borrowed_at');
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isBorrowed(): bool
    {
        return $this->status === self::STATUS_BORROWED;
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeBorrowed($query)
    {
        return $query->where('status', self::STATUS_BORROWED);
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('shelf_location', 'like', "%{$location}%");
    }

    public function scopeByBarcode($query, $barcode)
    {
        return $query->where('barcode', $barcode);
    }
}
