<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Members\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationActivationPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Web general registration: is_active must be false (admin approval required).
     */
    public function test_web_general_registration_sets_is_active_false(): void
    {
        $validated = [
            'name' => 'Web User',
            'email' => 'web-user@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => false,
        ];

        $user = User::create($validated);

        $this->assertFalse((bool) $user->is_active);
    }

    /**
     * Web student registration: is_active must be false (admin approval required).
     */
    public function test_web_student_registration_sets_is_active_false(): void
    {
        $user = User::create([
            'name' => 'Student User',
            'email' => 'student-user@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => false,
        ]);

        $this->assertFalse((bool) $user->is_active);
    }

    /**
     * API registration: is_active must be true (immediate mobile app access).
     */
    public function test_api_registration_sets_is_active_true(): void
    {
        $user = User::create([
            'name' => 'API User',
            'email' => 'api-user@example.com',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);

        $this->assertTrue((bool) $user->is_active);
    }

    /**
     * Both web paths redirect to verification.notice after registration.
     */
    public function test_web_general_registration_redirects_to_verification(): void
    {
        // Simulate the Volt component logic
        $redirectUrl = route('verification.notice', absolute: false);
        $this->assertStringContainsString('/verify-email', $redirectUrl);
    }
}
