<?php

namespace Database\Seeders;

use App\Modules\Settings\Models\NewsletterSubscriber;
use Illuminate\Database\Seeder;

class NewsletterSubscriberSeeder extends Seeder
{
    public function run(): void
    {
        $subscribers = [
            ['email' => 'john.doe@example.com', 'name' => 'John Doe'],
            ['email' => 'jane.smith@mutomo.ac.ke', 'name' => 'Jane Smith'],
            ['email' => 'mary.wanjiku@ollmchs.org', 'name' => 'Mary Wanjiku'],
            ['email' => 'peter.kamau@gmail.com', 'name' => 'Peter Kamau'],
            ['email' => 'faith.chebet@mutomo.ac.ke', 'name' => 'Faith Chebet'],
        ];

        foreach ($subscribers as $data) {
            NewsletterSubscriber::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'is_active' => true,
                    'subscribed_at' => now()->subDays(rand(1, 60)),
                ]
            );
        }
    }
}
