<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Catalog\Models\Book;
use App\Modules\Circulation\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'status' => Reservation::STATUS_PENDING,
            'reserved_at' => fake()->dateTimeBetween('-14 days', 'now'),
            'expires_at' => fake()->dateTimeBetween('+1 day', '+14 days'),
        ];
    }
}
