<?php

namespace Database\Factories;

use App\Modules\Subscriptions\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->unique()->slug(3),
            'type' => fake()->randomElement(['individual', 'school']),
            'billing_cycle' => fake()->randomElement(['monthly', 'yearly']),
            'price' => fake()->numberBetween(100, 50000),
            'currency' => 'KES',
            'description' => fake()->sentence(),
            'features' => [fake()->sentence()],
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
