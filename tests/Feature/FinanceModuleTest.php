<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@ollmchs.ac.ke')->first() ?? User::factory()->create();
    }

    public function test_finance_dashboard_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('finance.index'));
        $response->assertOk();
    }

    public function test_transactions_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('finance.transactions'));
        $response->assertOk();
    }

    public function test_fines_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('finance.fines'));
        $response->assertOk();
    }

    public function test_analytics_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('finance.analytics'));
        $response->assertOk();
    }

    public function test_reports_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('finance.reports'));
        $response->assertOk();
    }
}
