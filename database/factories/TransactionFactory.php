<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Finance\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'transaction_number' => Transaction::generateNumber(),
            'type' => fake()->randomElement(['fine_payment', 'subscription_payment', 'manual_entry']),
            'payment_method' => fake()->randomElement(['cash', 'mpesa', 'bank_transfer', 'card', 'cheque']),
            'amount' => fake()->randomFloat(2, 100, 50000),
            'currency' => 'KES',
            'reference' => fake()->optional()->bothify('REF-####-????'),
            'description' => fake()->sentence(),
            'status' => 'completed',
            'paid_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
