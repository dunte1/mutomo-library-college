<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Notifications\Models\InAppNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

class InAppNotificationFactory extends Factory
{
    protected $model = InAppNotification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['overdue', 'due_reminder', 'hold_available', 'fine', 'message', 'borrow', 'return']),
            'title' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'icon' => fake()->randomElement(['exclamation-circle', 'clock', 'bookmark', 'credit-card', 'chat', 'check-circle']),
            'is_read' => false,
        ];
    }
}
