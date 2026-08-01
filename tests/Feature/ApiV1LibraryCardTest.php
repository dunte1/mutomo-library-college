<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Members\Models\LibraryCard;
use App\Modules\Members\Models\Member;
use App\Modules\Subscriptions\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiV1LibraryCardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Member $member;

    protected LibraryCard $card;

    protected string $baseUrl = '/api/v1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Plan::factory()->create(['name' => 'Free', 'is_active' => true, 'price' => 0]);

        $this->user = User::factory()->create()->assignRole('student');
        $this->member = Member::create([
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'member_id' => 'OLLMCHS-2026-000001',
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '+254712345678',
            'gender' => 'male',
            'id_number' => '1234567890',
            'admission_number' => 'AD2026001',
            'class' => 'Year 2',
            'blood_group' => 'B-',
            'membership_type' => 'student',
            'status' => Member::STATUS_ACTIVE,
            'joined_at' => now(),
            'expires_at' => now()->addYear(),
        ]);
        $this->card = LibraryCard::create([
            'member_id' => $this->member->id,
            'card_number' => 'CRD-000001',
            'qr_code' => '<svg xmlns="http://www.w3.org/2000/svg"><rect/></svg>',
            'barcode' => 'BAR-000001',
            'status' => 'active',
            'issued_at' => now(),
            'expires_at' => now()->addYear(),
            'issued_by' => $this->user->id,
        ]);
        $this->user->givePermissionTo('view-library-cards');
    }

    protected function headers(): array
    {
        return ['Authorization' => 'Bearer '.$this->user->createToken('test')->plainTextToken];
    }

    public function test_show_returns_library_card(): void
    {
        $response = $this->getJson("{$this->baseUrl}/library-card", $this->headers());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id', 'card_number', 'status', 'issued_at', 'expires_at',
                    'qr_code_svg', 'barcode', 'member',
                ],
            ])
            ->assertJsonPath('data.card_number', $this->card->card_number)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.member.full_name', $this->member->full_name)
            ->assertJsonPath('data.member.email', $this->member->email)
            ->assertJsonPath('data.member.phone', $this->member->phone)
            ->assertJsonPath('data.member.student_id', $this->member->student_id)
            ->assertJsonPath('data.member.class', 'Year 2')
            ->assertJsonPath('data.member.blood_group', 'B-')
            ->assertJsonPath('data.member.member_status', $this->member->status);
    }

    public function test_show_returns_404_when_no_active_card(): void
    {
        $this->card->update(['status' => 'expired']);

        $response = $this->getJson("{$this->baseUrl}/library-card", $this->headers());

        $response->assertStatus(404)
            ->assertJson(['message' => 'No active library card found. Please visit the library to get your card issued.']);
    }

    public function test_show_returns_404_when_user_has_no_member(): void
    {
        $user2 = User::factory()->create()->assignRole('student');
        $user2->givePermissionTo('view-library-cards');

        $response = $this->getJson("{$this->baseUrl}/library-card", [
            'Authorization' => 'Bearer '.$user2->createToken('test')->plainTextToken,
        ]);

        $response->assertStatus(404);
    }

    public function test_qr_code_returns_svg(): void
    {
        $response = $this->getJson("{$this->baseUrl}/library-card/qr-code", $this->headers());

        $response->assertOk()
            ->assertJsonStructure(['data' => ['qr_code_svg', 'card_number']])
            ->assertJsonPath('data.card_number', $this->card->card_number);
    }

    public function test_barcode_returns_data(): void
    {
        $response = $this->getJson("{$this->baseUrl}/library-card/barcode", $this->headers());

        $response->assertOk()
            ->assertJsonStructure(['data' => ['barcode', 'card_number']])
            ->assertJsonPath('data.card_number', $this->card->card_number);
    }

    public function test_pdf_download_returns_pdf(): void
    {
        $response = $this->get("{$this->baseUrl}/library-card/pdf", $this->headers());

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type') ?? '');
    }

    public function test_json_endpoints_require_authentication(): void
    {
        $this->getJson("{$this->baseUrl}/library-card")->assertStatus(401);
        $this->getJson("{$this->baseUrl}/library-card/qr-code")->assertStatus(401);
        $this->getJson("{$this->baseUrl}/library-card/barcode")->assertStatus(401);
    }
}
