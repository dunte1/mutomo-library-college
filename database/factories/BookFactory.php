<?php

namespace Database\Factories;

use App\Modules\Catalog\Models\Book;
use App\Modules\Catalog\Models\Category;
use App\Modules\Catalog\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'title' => ucfirst($title),
            'slug' => \Illuminate\Support\Str::slug($title).'-'.fake()->unique()->numerify('####'),
            'isbn' => fake()->unique()->isbn13(),
            'description' => fake()->paragraphs(2, true),
            'category_id' => Category::factory(),
            'publisher_id' => Publisher::factory(),
            'publication_year' => fake()->numberBetween(2000, 2026),
            'language' => 'en',
            'edition' => fake()->optional()->word(),
            'pages' => fake()->optional()->numberBetween(100, 1200),
            'is_active' => true,
        ];
    }
}
