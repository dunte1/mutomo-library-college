<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Catalog\Models\BookCopy;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Subscriptions\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CirculationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'librarian@ollmchs.ac.ke')->first() ?? User::factory()->create();
        $this->student = User::where('email', 'student@ollmchs.ac.ke')->first() ?? User::factory()->create();

        $plan = Plan::factory()->create(['is_active' => true]);
        Subscription::factory()->create([
            'user_id' => $this->user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);
    }

    public function test_circulation_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('circulation.index'));
        $response->assertOk();
    }

    public function test_issue_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('circulation.issue'));
        $response->assertOk();
    }

    public function test_return_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('circulation.return'));
        $response->assertOk();
    }

    public function test_api_issue_book(): void
    {
        $copy = BookCopy::where('status', 'available')->first();
        if (! $copy) {
            $this->markTestSkipped('No available copies');
        }

        $token = $this->user->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/circulation/issue', [
                'user_id' => $this->student->id,
                'barcode' => $copy->barcode,
            ]);
        $response->assertStatus(201);
    }
}
