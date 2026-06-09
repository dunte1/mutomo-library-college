<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Shared\Traits\Auditable;

class Department extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
