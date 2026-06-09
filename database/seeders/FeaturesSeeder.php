<?php

namespace Database\Seeders;

use App\Modules\Settings\Models\Feature;
use Illuminate\Database\Seeder;

class FeaturesSeeder extends Seeder
{
    public function run(): void
    {
        Feature::create([
            'title' => 'Comprehensive Catalog',
            'description' => 'Thousands of medical textbooks, journals, and research papers at your fingertips.',
            'icon' => '📚',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        Feature::create([
            'title' => 'Digital Library',
            'description' => 'Access eBooks, lecture notes, and multimedia resources from anywhere, anytime.',
            'icon' => '💻',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Feature::create([
            'title' => 'Smart Analytics',
            'description' => 'AI-powered recommendations and comprehensive borrowing analytics for informed decisions.',
            'icon' => '📊',
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }
}
