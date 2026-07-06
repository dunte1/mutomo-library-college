<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Circulation\Models\BorrowRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class BorrowRecordFactory extends Factory
{
    protected $model = BorrowRecord::class;

    public function definition(): array
    {
        $borrowedAt = fake()->dateTimeBetween('-30 days', 'now');
        $dueAt = (clone $borrowedAt)->modify('+14 days');

        return [
            'user_id' => User::factory(),
            'book_copy_id' => BookCopy::factory(),
            'borrowed_at' => $borrowedAt,
            'due_at' => $dueAt,
            'status' => BorrowRecord::STATUS_ACTIVE,
            'max_renewals' => 2,
            'renewal_count' => 0,
        ];
    }

    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BorrowRecord::STATUS_RETURNED,
            'returned_at' => fake()->dateTimeBetween($attributes['borrowed_at'], 'now'),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'status' => BorrowRecord::STATUS_ACTIVE,
            'due_at' => fake()->dateTimeBetween('-14 days', '-1 day'),
        ]);
    }
}
