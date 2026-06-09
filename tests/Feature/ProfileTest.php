<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('profile.update-profile-information-form')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_profile_information_with_phone_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('profile.update-profile-information-form')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('phone', '+254712345678')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame('+254712345678', $user->phone);
    }

    public function test_profile_avatar_can_be_uploaded(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        Volt::test('profile.update-profile-information-form')
            ->set('avatar', $file)
            ->set('name', $user->name)
            ->set('email', $user->email)
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('profile.update-profile-information-form')
            ->set('name', 'Test User')
            ->set('email', $user->email)
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('profile.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $this->assertGuest();
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Volt::test('profile.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser')
            ->assertHasErrors('password');

        $this->assertAuthenticated();
    }
}
