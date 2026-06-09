<?php

namespace App\Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'name', 'type', 'parameters', 'file_path', 'file_type',
        'status', 'error', 'generated_by', 'generated_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'generated_at' => 'datetime',
    ];

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public static function typeOptions(): array
    {
        return [
            'circulation_summary' => 'Circulation Summary',
            'overdue_report' => 'Overdue Report',
            'fine_report' => 'Fine Collection Report',
            'popular_books' => 'Popular Books',
            'member_activity' => 'Member Activity',
            'catalog_inventory' => 'Catalog Inventory',
            'financial_summary' => 'Financial Summary',
            'daily_transactions' => 'Daily Transactions',
        ];
    }

    public static function formatOptions(): array
    {
        return ['pdf' => 'PDF', 'csv' => 'CSV', 'excel' => 'Excel'];
    }
}
