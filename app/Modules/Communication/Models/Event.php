<?php

namespace App\Modules\Communication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'location',
        'start_date',
        'end_date',
        'type',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
