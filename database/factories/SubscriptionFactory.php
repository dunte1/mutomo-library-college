<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Subscriptions\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'status' => fake()->randomElement(['active', 'expired', 'cancelled', 'pending']),
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'renewal_date' => now()->addMonth(),
            'billing_cycle' => 'monthly',
            'auto_renew' => true,
        ];
    }
}
