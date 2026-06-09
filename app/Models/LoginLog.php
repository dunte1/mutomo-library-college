<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device',
        'platform',
        'browser',
        'location',
        'is_successful',
        'login_at',
        'logout_at',
        'session_id',
    ];

    protected function casts(): array
    {
        return [
            'is_successful' => 'boolean',
            'login_at' => 'datetime',
            'logout_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('is_successful', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('is_successful', false);
    }
}
