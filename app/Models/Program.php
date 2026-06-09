<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Shared\Traits\Auditable;

class Program extends Model
{
    use SoftDeletes, Auditable;

    protected $fillable = [
        'name',
        'code',
        'department_id',
        'duration_years',
        'description',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'duration_years' => 'integer',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
