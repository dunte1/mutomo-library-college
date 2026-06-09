<?php

namespace Database\Seeders;

use App\Modules\Settings\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        Testimonial::create([
            'author_name' => 'Jane Doe',
            'author_role' => 'Nursing Student',
            'content' => 'The digital library has been a game-changer for my clinical studies. I can access medical journals from my phone during rotation breaks.',
            'rating' => 5,
            'status' => 'approved',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        Testimonial::create([
            'author_name' => 'Dr. James Mwangi',
            'author_role' => 'Faculty Member',
            'content' => 'An excellent resource for both teaching and research. The catalog is well-organized and the borrowing system is remarkably efficient.',
            'rating' => 5,
            'status' => 'approved',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Testimonial::create([
            'author_name' => 'Alice Kamau',
            'author_role' => 'Medical Lab Sciences',
            'content' => 'The AI recommendations helped me discover textbooks I would never have found on my own. Subscription pricing is very affordable for students.',
            'rating' => 4,
            'status' => 'approved',
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }
}
