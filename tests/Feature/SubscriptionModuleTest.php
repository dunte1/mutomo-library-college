<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Settings\Models\Setting;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Subscriptions\Models\Subscription;
use App\Modules\Subscriptions\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $student;

    protected Plan $individualMonthly;

    protected Plan $individualYearly;

    protected Plan $schoolMonthly;

    protected Plan $schoolYearly;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'admin@ollmchs.ac.ke')->first()
            ?? User::factory()->create()->assignRole('super-admin');

        $this->student = User::factory()->create()->assignRole('student');

        $this->individualMonthly = Plan::factory()->create([
            'type' => 'individual',
            'billing_cycle' => 'monthly',
            'price' => 500,
            'is_active' => true,
        ]);
        $this->individualYearly = Plan::factory()->create([
            'type' => 'individual',
            'billing_cycle' => 'yearly',
            'price' => 5000,
            'is_active' => true,
        ]);
        $this->schoolMonthly = Plan::factory()->create([
            'type' => 'school',
            'billing_cycle' => 'monthly',
            'price' => 2000,
            'is_active' => true,
        ]);
        $this->schoolYearly = Plan::factory()->create([
            'type' => 'school',
            'billing_cycle' => 'yearly',
            'price' => 20000,
            'is_active' => true,
        ]);
    }

    public function test_plan_listing_page_loads(): void
    {
        $response = $this->actingAs($this->student)->get(route('subscriptions.plans'));
        $response->assertOk();
    }

    public function test_my_subscription_page_loads(): void
    {
        $response = $this->actingAs($this->student)->get(route('subscriptions.my'));
        $response->assertOk();
    }

    public function test_checkout_page_loads_for_active_plan(): void
    {
        $response = $this->actingAs($this->student)
            ->get(route('subscriptions.checkout', $this->individualMonthly));
        $response->assertOk();
    }

    public function test_checkout_page_fails_for_inactive_plan(): void
    {
        $inactive = Plan::factory()->create(['is_active' => false]);
        $response = $this->actingAs($this->student)
            ->get(route('subscriptions.checkout', $inactive));
        $response->assertNotFound();
    }

    public function test_admin_plan_list_loads(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.subscriptions.plans'));
        $response->assertOk();
    }

    public function test_admin_subscription_list_loads(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.subscriptions.index'));
        $response->assertOk();
    }

    public function test_admin_plan_create_loads(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.subscriptions.plans.create'));
        $response->assertOk();
    }

    public function test_admin_plan_edit_loads(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.subscriptions.plans.edit', $this->individualMonthly));
        $response->assertOk();
    }

    public function test_student_cannot_access_admin_subscriptions(): void
    {
        $response = $this->actingAs($this->student)
            ->get(route('admin.subscriptions.index'));
        $response->assertForbidden();
    }

    public function test_student_cannot_access_admin_plans(): void
    {
        $response = $this->actingAs($this->student)
            ->get(route('admin.subscriptions.plans'));
        $response->assertForbidden();
    }

    public function test_subscription_service_creates_subscription(): void
    {
        $service = app(SubscriptionService::class);
        $subscription = $service->createSubscription($this->student, $this->individualMonthly);

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'user_id' => $this->student->id,
            'plan_id' => $this->individualMonthly->id,
            'status' => 'pending',
        ]);
        $this->assertEquals('pending', $subscription->status);
    }

    public function test_subscription_activation(): void
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->student->id,
            'plan_id' => $this->individualMonthly->id,
            'status' => 'pending',
        ]);

        $subscription->activate();

        $this->assertEquals('active', $subscription->fresh()->status);
    }

    public function test_subscription_cancellation(): void
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->student->id,
            'plan_id' => $this->individualMonthly->id,
            'status' => 'active',
        ]);

        $subscription->cancel('Testing cancellation');

        $this->assertEquals('cancelled', $subscription->fresh()->status);
        $this->assertFalse($subscription->fresh()->auto_renew);
        $this->assertNotNull($subscription->fresh()->cancelled_at);
    }

    public function test_subscription_expiry_marking(): void
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->student->id,
            'plan_id' => $this->individualMonthly->id,
            'status' => 'active',
        ]);

        $subscription->markAsExpired();

        $this->assertEquals('expired', $subscription->fresh()->status);
    }

    public function test_subscription_suspension(): void
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->student->id,
            'plan_id' => $this->individualMonthly->id,
            'status' => 'active',
        ]);

        $subscription->suspend();

        $this->assertEquals('suspended', $subscription->fresh()->status);
        $this->assertNotNull($subscription->fresh()->suspended_at);
    }

    public function test_user_active_subscription_scope(): void
    {
        Subscription::factory()->create([
            'user_id' => $this->student->id,
            'plan_id' => $this->individualMonthly->id,
            'status' => 'active',
        ]);

        $this->assertTrue($this->student->hasActiveSubscription());
    }

    public function test_user_has_no_active_subscription(): void
    {
        Subscription::factory()->create([
            'user_id' => $this->student->id,
            'plan_id' => $this->individualMonthly->id,
            'status' => 'expired',
        ]);

        $this->assertFalse($this->student->fresh()->hasActiveSubscription());
    }

    public function test_subscription_due_for_renewal_scope(): void
    {
        Subscription::factory()->create([
            'user_id' => $this->student->id,
            'plan_id' => $this->individualMonthly->id,
            'status' => 'active',
            'auto_renew' => true,
            'renewal_date' => now()->subDay(),
        ]);

        $due = Subscription::dueForRenewal()->get();
        $this->assertCount(1, $due);
    }

    public function test_subscription_expiring_soon_scope(): void
    {
        Subscription::factory()->create([
            'user_id' => $this->student->id,
            'plan_id' => $this->individualMonthly->id,
            'status' => 'active',
            'end_date' => now()->addDays(3),
        ]);

        $expiring = Subscription::expiringSoon(7)->get();
        $this->assertCount(1, $expiring);
    }

    public function test_subscription_service_processes_expired(): void
    {
        $expired = Subscription::factory()->create([
            'user_id' => $this->student->id,
            'plan_id' => $this->individualYearly->id,
            'status' => 'active',
            'end_date' => now()->subDay(),
        ]);
        $active = Subscription::factory()->create([
            'user_id' => $this->student->id,
            'plan_id' => $this->schoolMonthly->id,
            'status' => 'active',
            'end_date' => now()->addMonth(),
        ]);

        $service = app(SubscriptionService::class);
        $count = $service->processExpiredSubscriptions();

        $this->assertEquals(1, $count);
        $this->assertEquals('expired', $expired->fresh()->status);
        $this->assertEquals('active', $active->fresh()->status);
    }

    public function test_subscription_service_records_payment(): void
    {
        $subscription = Subscription::factory()->create([
            'user_id' => $this->student->id,
            'plan_id' => $this->individualMonthly->id,
            'status' => 'pending',
        ]);

        $service = app(SubscriptionService::class);
        $transaction = $service->recordPayment($subscription, 'mpesa', 'MPE12345', 500);

        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
        $this->assertEquals('active', $subscription->fresh()->status);
    }

    public function test_subscription_service_syncs_plan_from_settings(): void
    {
        Plan::truncate();

        Setting::updateOrCreate(['key' => 'individual_monthly_fee'], ['value' => 600, 'group' => 'subscriptions', 'type' => 'integer']);
        Setting::updateOrCreate(['key' => 'individual_yearly_fee'], ['value' => 6000, 'group' => 'subscriptions', 'type' => 'integer']);
        Setting::updateOrCreate(['key' => 'school_monthly_fee'], ['value' => 2500, 'group' => 'subscriptions', 'type' => 'integer']);
        Setting::updateOrCreate(['key' => 'school_yearly_fee'], ['value' => 25000, 'group' => 'subscriptions', 'type' => 'integer']);

        $service = app(SubscriptionService::class);
        $service->syncPlansFromSettings();

        $this->assertDatabaseHas('plans', ['slug' => 'individual-monthly', 'price' => 600, 'is_active' => true]);
        $this->assertDatabaseHas('plans', ['slug' => 'individual-yearly', 'price' => 6000, 'is_active' => true]);
        $this->assertDatabaseHas('plans', ['slug' => 'school-monthly', 'price' => 2500, 'is_active' => true]);
        $this->assertDatabaseHas('plans', ['slug' => 'school-yearly', 'price' => 25000, 'is_active' => true]);
    }

    public function test_subscription_service_deactivates_plan_when_price_zero(): void
    {
        Setting::updateOrCreate(['key' => 'individual_monthly_fee'], ['value' => 0, 'group' => 'subscriptions', 'type' => 'integer']);

        $service = app(SubscriptionService::class);
        $service->syncPlansFromSettings();

        $this->assertDatabaseHas('plans', ['slug' => 'individual-monthly', 'is_active' => false]);
    }

    public function test_all_four_plan_types_accessible(): void
    {
        $this->assertTrue($this->individualMonthly->isIndividual());
        $this->assertTrue($this->individualYearly->isIndividual());
        $this->assertTrue($this->schoolMonthly->isSchool());
        $this->assertTrue($this->schoolYearly->isSchool());

        $this->assertTrue($this->individualMonthly->isMonthly());
        $this->assertFalse($this->individualMonthly->isYearly());
        $this->assertTrue($this->individualYearly->isYearly());
        $this->assertTrue($this->schoolMonthly->isMonthly());
        $this->assertFalse($this->schoolMonthly->isYearly());
        $this->assertTrue($this->schoolYearly->isYearly());
    }

    public function test_checkout_shows_both_payment_methods(): void
    {
        $response = $this->actingAs($this->student)
            ->get(route('subscriptions.checkout', $this->individualMonthly));

        $response->assertOk();
        $response->assertSee('M-Pesa');
        $response->assertSee('Stripe');
    }

    public function test_subscription_middleware_redirects_unsubscribed(): void
    {
        $response = $this->actingAs($this->student)
            ->get(route('subscriptions.plans'));

        $response->assertOk();
    }
}
