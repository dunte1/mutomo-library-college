<?php

namespace App\Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaTransaction extends Model
{
    protected $fillable = [
        'user_id', 'transaction_id', 'merchant_request_id', 'checkout_request_id',
        'mpesa_receipt', 'phone_number', 'amount', 'status', 'result_desc', 'callback_data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'callback_data' => 'array',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }
}
