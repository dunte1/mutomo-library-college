<?php

namespace Database\Seeders;

use App\Modules\Settings\Models\Setting;
use App\Modules\Subscriptions\Models\Plan;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $individualMonthly = (float) Setting::value('individual_monthly_fee', 0);
        $individualYearly = (float) Setting::value('individual_yearly_fee', 0);
        $schoolMonthly = (float) Setting::value('school_monthly_fee', 0);
        $schoolYearly = (float) Setting::value('school_yearly_fee', 0);

        $plans = [
            [
                'name' => 'Individual Monthly',
                'slug' => 'individual-monthly',
                'type' => 'individual',
                'billing_cycle' => 'monthly',
                'price' => $individualMonthly ?: 500,
                'currency' => 'KES',
                'description' => 'Access to library resources for individual users on a monthly basis.',
                'features' => ['Unlimited book borrows', 'Access to digital library', 'Book reservations', 'Borrow history tracking'],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Individual Yearly',
                'slug' => 'individual-yearly',
                'type' => 'individual',
                'billing_cycle' => 'yearly',
                'price' => $individualYearly ?: 5000,
                'currency' => 'KES',
                'description' => 'Best value for individual users with a full year of access.',
                'features' => ['Unlimited book borrows', 'Access to digital library', 'Book reservations', 'Priority borrowing', 'Borrow history tracking'],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'School Monthly',
                'slug' => 'school-monthly',
                'type' => 'school',
                'billing_cycle' => 'monthly',
                'price' => $schoolMonthly ?: 2000,
                'currency' => 'KES',
                'description' => 'Institutional access for schools and training institutions.',
                'features' => ['Multi-user access', 'Digital library for students', 'Bulk book borrowing', 'Institutional reports', 'Dedicated support'],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'School Yearly',
                'slug' => 'school-yearly',
                'type' => 'school',
                'billing_cycle' => 'yearly',
                'price' => $schoolYearly ?: 20000,
                'currency' => 'KES',
                'description' => 'Complete institutional access for the entire academic year.',
                'features' => ['Multi-user access', 'Digital library for students', 'Bulk book borrowing', 'Institutional reports', 'Priority support', 'Custom integrations'],
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Subscription plans seeded successfully.');
    }
}
