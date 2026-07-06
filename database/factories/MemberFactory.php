<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use App\Modules\Members\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'member_id' => 'OLLMCHS-'.fake()->year().'-'.fake()->numerify('######'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'gender' => fake()->randomElement(['male', 'female']),
            'id_number' => fake()->unique()->numerify('##########'),
            'admission_number' => fake()->unique()->numerify('AD#####'),
            'membership_type' => fake()->randomElement(['student', 'teacher', 'staff', 'external']),
            'status' => Member::STATUS_ACTIVE,
            'joined_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'expires_at' => fake()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
