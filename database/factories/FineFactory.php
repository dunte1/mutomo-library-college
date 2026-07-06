<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Circulation\Models\BorrowRecord;
use App\Modules\Circulation\Models\Fine;
use Illuminate\Database\Eloquent\Factories\Factory;

class FineFactory extends Factory
{
    protected $model = Fine::class;

    public function definition(): array
    {
        return [
            'borrow_record_id' => BorrowRecord::factory(),
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 50, 2000),
            'paid_amount' => 0,
            'waived_amount' => 0,
            'status' => Fine::STATUS_PENDING,
            'reason' => fake()->randomElement(['Overdue fine', 'Lost book fine', 'Damaged book fine']),
            'assessed_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Fine::STATUS_PAID,
            'paid_amount' => $attributes['amount'],
            'paid_at' => fake()->dateTimeBetween($attributes['assessed_at'], 'now'),
        ]);
    }
}
