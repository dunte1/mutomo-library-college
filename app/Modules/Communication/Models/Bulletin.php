<?php

namespace App\Modules\Communication\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Bulletin extends Model
{
    protected $fillable = [
        'title',
        'content',
        'department_id',
        'status',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
