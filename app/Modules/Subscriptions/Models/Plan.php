<?php

namespace App\Modules\Subscriptions\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Plan extends Model
{
    use HasFactory;

    protected static function newFactory(): PlanFactory
    {
        return PlanFactory::new();
    }

    protected $fillable = [
        'name', 'slug', 'type', 'billing_cycle', 'price',
        'currency', 'description', 'features', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Plan $plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name).'-'.Str::random(4);
            }
        });
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeMonthly($query)
    {
        return $query->where('billing_cycle', 'monthly');
    }

    public function scopeYearly($query)
    {
        return $query->where('billing_cycle', 'yearly');
    }

    public function isIndividual(): bool
    {
        return $this->type === 'individual';
    }

    public function isSchool(): bool
    {
        return $this->type === 'school';
    }

    public function isMonthly(): bool
    {
        return $this->billing_cycle === 'monthly';
    }

    public function isYearly(): bool
    {
        return $this->billing_cycle === 'yearly';
    }

    public static function typeOptions(): array
    {
        return ['individual' => 'Individual', 'school' => 'School'];
    }

    public static function billingCycleOptions(): array
    {
        return ['monthly' => 'Monthly', 'yearly' => 'Yearly'];
    }
}
