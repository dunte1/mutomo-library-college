<?php

namespace App\Modules\DigitalLibrary\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DigitalAssetCategory extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'is_active', 'created_by'];

    protected $casts = ['is_active' => 'boolean'];

    public function assets(): HasMany
    {
        return $this->hasMany(DigitalAsset::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
