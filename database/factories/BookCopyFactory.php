<?php

namespace Database\Factories;

use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\BookCopy;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookCopyFactory extends Factory
{
    protected $model = BookCopy::class;

    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'barcode' => fake()->unique()->numerify('BC#####'),
            'rfid_tag' => fake()->unique()->numerify('RFID#####'),
            'shelf_location' => fake()->randomElement(['A1', 'A2', 'B1', 'B2', 'C1', 'C2']),
            'status' => BookCopy::STATUS_AVAILABLE,
            'condition' => 'good',
        ];
    }
}
