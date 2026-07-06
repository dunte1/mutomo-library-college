<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Communication\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'sender_id' => User::factory(),
            'subject' => fake()->sentence(),
            'body' => fake()->paragraphs(2, true),
            'priority' => Message::PRIORITY_NORMAL,
            'type' => Message::TYPE_DIRECT,
            'status' => Message::STATUS_SENT,
            'sent_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
