<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            DummyDataSeeder::class,
            CatalogSeeder::class,
            CirculationSeeder::class,
            DigitalLibrarySeeder::class,
            FinanceSeeder::class,
            SubscriptionSeeder::class,
            FeaturesSeeder::class,
            WhyChooseUsSeeder::class,
            TestimonialSeeder::class,
            NewsletterSubscriberSeeder::class,
        ]);
    }
}
