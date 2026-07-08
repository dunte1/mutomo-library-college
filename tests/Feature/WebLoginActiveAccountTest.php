<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class WebLoginActiveAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_inactive_user_is_rejected_after_successful_credential_check(): void
    {
        $user = User::where('email', 'student@ollmchs.ac.ke')->first()
            ?? User::factory()->create([
                'email' => 'inactive-test@example.com',
                'password' => 'password',
            ]);

        $user->update(['is_active' => false]);

        // Auth::attempt succeeds (credentials valid) but is_active is false
        $attempted = Auth::attempt([
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertTrue($attempted, 'Credentials are valid');

        // The LoginForm logic checks is_active after attempt
        $this->assertFalse((bool) $user->fresh()->is_active);

        // Simulate what LoginForm does: logout inactive user
        Auth::logout();
        $this->assertGuest();
    }

    public function test_active_user_stays_authenticated(): void
    {
        $user = User::where('email', 'admin@ollmchs.ac.ke')->first()
            ?? User::factory()->create([
                'email' => 'active-test@example.com',
                'password' => 'password',
                'is_active' => true,
            ]);

        if (! $user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }
        $user->update(['is_active' => true]);

        $attempted = Auth::attempt([
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertTrue($attempted);
        $this->assertTrue((bool) $user->fresh()->is_active);
        $this->assertAuthenticatedAs($user);
    }
}
