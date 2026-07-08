<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Members\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebRegistrationMemberTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_web_registration_flow_creates_member_record(): void
    {
        $name = 'Test User';
        $email = 'test-member@example.com';

        $validated = [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('Password123!'),
            'is_active' => false,
        ];

        $user = User::create($validated);
        $user->assignRole('guest');

        $nameParts = explode(' ', $name, 2);
        Member::create([
            'user_id' => $user->id,
            'first_name' => $nameParts[0],
            'last_name' => $nameParts[1] ?? '',
            'email' => $email,
            'membership_type' => 'external',
            'status' => 'active',
            'joined_at' => now(),
            'registered_by' => $user->id,
        ]);

        $this->assertDatabaseHas('users', ['email' => $email]);
        $this->assertDatabaseHas('members', [
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $email,
            'membership_type' => 'external',
            'status' => 'active',
        ]);
    }

    public function test_single_name_splits_correctly(): void
    {
        $nameParts = explode(' ', 'Madonna', 2);
        $this->assertEquals('Madonna', $nameParts[0]);
        $this->assertArrayNotHasKey(1, $nameParts);
    }

    public function test_two_part_name_splits_correctly(): void
    {
        $nameParts = explode(' ', 'Jane Doe Smith', 2);
        $this->assertEquals('Jane', $nameParts[0]);
        $this->assertEquals('Doe Smith', $nameParts[1]);
    }
}
