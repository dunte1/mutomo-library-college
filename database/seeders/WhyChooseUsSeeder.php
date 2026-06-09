<?php

namespace Database\Seeders;

use App\Modules\Settings\Models\WhyChooseUs;
use Illuminate\Database\Seeder;

class WhyChooseUsSeeder extends Seeder
{
    public function run(): void
    {
        WhyChooseUs::create([
            'title' => 'Medical-Focused Collection',
            'description' => 'Curated collections of medical textbooks, nursing journals, clinical references, and healthcare research papers essential for health sciences education.',
            'icon' => '🛡️',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        WhyChooseUs::create([
            'title' => 'Anytime, Anywhere Access',
            'description' => 'Our digital library and mobile-friendly platform ensure you can access learning resources 24/7, whether on campus, during clinical rotations, or at home.',
            'icon' => '📱',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        WhyChooseUs::create([
            'title' => 'AI-Powered Insights',
            'description' => 'Smart recommendation engine suggests relevant resources based on your program, reading history, and academic needs, helping you discover the right materials faster.',
            'icon' => '🤖',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        WhyChooseUs::create([
            'title' => 'Flexible Payment Options',
            'description' => 'Choose from affordable subscription plans with convenient M-Pesa and mobile money payment options designed for Kenyan students and institutions.',
            'icon' => '💳',
            'sort_order' => 3,
            'is_active' => true,
        ]);
    }
}
