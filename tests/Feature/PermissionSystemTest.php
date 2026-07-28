<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Members\Models\Member;
use App\Modules\Subscriptions\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PermissionSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Plan::factory()->create(['name' => 'Free', 'is_active' => true, 'price' => 0]);

        $this->admin = User::where('email', 'admin@ollmchs.ac.ke')->first()
            ?? User::factory()->create(['email' => 'admin@ollmchs.ac.ke']);
        if (! $this->admin->hasRole('super-admin')) {
            $this->admin->assignRole('super-admin');
        }
    }

    // =========================================================================
    // PERMISSION DEFINITIONS: Verify all used permissions exist in the seeder
    // =========================================================================

    public function test_all_route_permissions_are_defined(): void
    {
        $requiredPermissions = [
            // Dashboard
            'view-dashboard', 'view-analytics',
            // Catalog
            'view-books', 'create-books', 'edit-books', 'delete-books',
            'view-categories', 'create-categories', 'edit-categories', 'delete-categories',
            'view-authors', 'create-authors', 'edit-authors', 'delete-authors',
            'view-publishers', 'create-publishers', 'edit-publishers', 'delete-publishers',
            'import-books', 'export-books', 'view-inventory', 'manage-inventory', 'view-new-arrivals',
            // Circulation
            'borrow-books', 'return-books', 'renew-books', 'view-borrows',
            'manage-reservations', 'manage-waitlists', 'view-circulation', 'override-due-dates',
            // Members
            'view-members', 'create-members', 'edit-members', 'delete-members',
            'suspend-members', 'reinstate-members', 'manage-membership-requests',
            'view-departments', 'manage-departments', 'view-programs', 'manage-programs', 'clear-members',
            // Digital Library
            'view-digital-assets', 'upload-digital-assets', 'edit-digital-assets',
            'delete-digital-assets', 'download-digital-assets', 'manage-access-levels',
            'view-digital-categories', 'manage-digital-categories',
            // Finance
            'view-fines', 'manage-fines', 'collect-payments', 'view-transactions',
            'generate-invoices', 'generate-receipts', 'process-refunds', 'view-financial-reports',
            // Notifications
            'send-notifications', 'manage-templates', 'view-notification-logs',
            // Reports
            'view-reports', 'generate-reports', 'export-reports', 'schedule-reports',
            // Settings
            'manage-settings', 'manage-roles', 'manage-permissions',
            'view-audit-logs', 'clear-audit-logs', 'manage-backups',
            'view-system-logs', 'manage-maintenance',
            // Communication
            'manage-announcements', 'manage-events', 'manage-bulletins',
            'send-messages', 'view-messages', 'reply-messages', 'reply-all-messages',
            'forward-messages', 'manage-broadcasts', 'view-message-logs',
            'manage-scheduled-messages', 'view-communication-analytics',
            // Library Cards
            'view-library-cards', 'manage-library-cards',
            // System Health
            'view-system-health', 'manage-system-optimization',
            // System
            'view-queue-monitor', 'manage-cache', 'view-storage', 'manage-storage',
            // AI
            'view-recommendations', 'manage-ai-settings',
            // Subscriptions
            'manage-subscriptions', 'view-subscriptions', 'manage-pricing', 'process-subscription-payments',
            // Assignments
            'create-assignments', 'view-assignments', 'complete-assignments',
        ];

        foreach ($requiredPermissions as $permission) {
            $found = Permission::where('name', $permission)->where('guard_name', 'web')->exists();
            $this->assertTrue($found, "Permission '{$permission}' is not defined in the permissions table");
        }
    }

    public function test_role_form_loads_all_permissions(): void
    {
        Livewire::actingAs($this->admin)
            ->test(\App\Modules\Settings\Livewire\RoleForm::class)
            ->assertOk();

        $permissionCount = Permission::where('guard_name', 'web')->count();
        $this->assertGreaterThan(80, $permissionCount, 'System should have at least 80 permissions');
    }

    // =========================================================================
    // ROLE ASSIGNMENTS: Verify each role has appropriate permissions
    // =========================================================================

    public function test_librarian_has_override_due_dates(): void
    {
        $librarian = User::factory()->create()->assignRole('librarian');
        $this->assertTrue(
            $librarian->hasPermissionTo('override-due-dates'),
            'Librarian should have override-due-dates permission'
        );
    }

    public function test_librarian_has_manage_templates(): void
    {
        $librarian = User::factory()->create()->assignRole('librarian');
        $this->assertTrue(
            $librarian->hasPermissionTo('manage-templates'),
            'Librarian should have manage-templates permission'
        );
    }

    public function test_librarian_has_manage_subscriptions(): void
    {
        $librarian = User::factory()->create()->assignRole('librarian');
        $this->assertTrue(
            $librarian->hasPermissionTo('manage-subscriptions'),
            'Librarian should have manage-subscriptions permission'
        );
    }

    public function test_assistant_librarian_has_view_circulation(): void
    {
        $assistant = User::factory()->create()->assignRole('assistant-librarian');
        $this->assertTrue(
            $assistant->hasPermissionTo('view-circulation'),
            'Assistant librarian should have view-circulation permission'
        );
    }

    public function test_assistant_librarian_has_renew_books(): void
    {
        $assistant = User::factory()->create()->assignRole('assistant-librarian');
        $this->assertTrue(
            $assistant->hasPermissionTo('renew-books'),
            'Assistant librarian should have renew-books permission'
        );
    }

    public function test_assistant_librarian_has_manage_fines(): void
    {
        $assistant = User::factory()->create()->assignRole('assistant-librarian');
        $this->assertTrue(
            $assistant->hasPermissionTo('manage-fines'),
            'Assistant librarian should have manage-fines permission'
        );
    }

    public function test_assistant_librarian_has_view_transactions(): void
    {
        $assistant = User::factory()->create()->assignRole('assistant-librarian');
        $this->assertTrue(
            $assistant->hasPermissionTo('view-transactions'),
            'Assistant librarian should have view-transactions permission'
        );
    }

    public function test_assistant_librarian_has_send_messages(): void
    {
        $assistant = User::factory()->create()->assignRole('assistant-librarian');
        $this->assertTrue(
            $assistant->hasPermissionTo('send-messages'),
            'Assistant librarian should have send-messages permission'
        );
    }

    public function test_student_has_limited_permissions(): void
    {
        $student = User::factory()->create()->assignRole('student');

        $this->assertTrue($student->hasPermissionTo('view-books'));
        $this->assertTrue($student->hasPermissionTo('view-digital-assets'));
        $this->assertTrue($student->hasPermissionTo('view-recommendations'));
        $this->assertTrue($student->hasPermissionTo('view-library-cards'));
        $this->assertTrue($student->hasPermissionTo('view-assignments'));
        $this->assertTrue($student->hasPermissionTo('complete-assignments'));

        $this->assertFalse($student->hasPermissionTo('create-books'));
        $this->assertFalse($student->hasPermissionTo('manage-fines'));
        $this->assertFalse($student->hasPermissionTo('manage-settings'));
        $this->assertFalse($student->hasPermissionTo('override-due-dates'));
    }

    public function test_ict_admin_has_system_permissions(): void
    {
        $ictAdmin = User::factory()->create()->assignRole('ict-admin');

        $this->assertTrue($ictAdmin->hasPermissionTo('manage-settings'));
        $this->assertTrue($ictAdmin->hasPermissionTo('view-audit-logs'));
        $this->assertTrue($ictAdmin->hasPermissionTo('clear-audit-logs'));
        $this->assertTrue($ictAdmin->hasPermissionTo('manage-backups'));
        $this->assertTrue($ictAdmin->hasPermissionTo('view-system-logs'));
        $this->assertTrue($ictAdmin->hasPermissionTo('manage-maintenance'));
        $this->assertTrue($ictAdmin->hasPermissionTo('view-system-health'));
        $this->assertTrue($ictAdmin->hasPermissionTo('manage-system-optimization'));
        $this->assertTrue($ictAdmin->hasPermissionTo('view-queue-monitor'));
        $this->assertTrue($ictAdmin->hasPermissionTo('manage-cache'));
        $this->assertTrue($ictAdmin->hasPermissionTo('view-storage'));
        $this->assertTrue($ictAdmin->hasPermissionTo('manage-storage'));
    }

    public function test_finance_officer_has_finance_permissions(): void
    {
        $finance = User::factory()->create()->assignRole('finance-officer');

        $this->assertTrue($finance->hasPermissionTo('view-fines'));
        $this->assertTrue($finance->hasPermissionTo('manage-fines'));
        $this->assertTrue($finance->hasPermissionTo('collect-payments'));
        $this->assertTrue($finance->hasPermissionTo('view-transactions'));
        $this->assertTrue($finance->hasPermissionTo('generate-invoices'));
        $this->assertTrue($finance->hasPermissionTo('generate-receipts'));
        $this->assertTrue($finance->hasPermissionTo('process-refunds'));
        $this->assertTrue($finance->hasPermissionTo('view-financial-reports'));
        $this->assertTrue($finance->hasPermissionTo('manage-subscriptions'));
        $this->assertTrue($finance->hasPermissionTo('manage-pricing'));
    }

    public function test_guest_has_minimal_permissions(): void
    {
        $guest = User::factory()->create()->assignRole('guest');

        $this->assertTrue($guest->hasPermissionTo('view-books'));
        $this->assertFalse($guest->hasPermissionTo('view-dashboard'));
        $this->assertFalse($guest->hasPermissionTo('view-members'));
        $this->assertFalse($guest->hasPermissionTo('manage-settings'));
    }

    // =========================================================================
    // PERMISSION CHANGES TAKE EFFECT IMMEDIATELY
    // =========================================================================

    public function test_adding_permission_takes_effect_immediately(): void
    {
        $librarian = User::factory()->create()->assignRole('librarian');

        // Initially should NOT have manage-settings
        $this->assertFalse($librarian->hasPermissionTo('manage-settings'));

        // Add the permission via RoleForm
        $role = Role::where('name', 'librarian')->first();
        $manageSettings = Permission::where('name', 'manage-settings')->first();

        Livewire::actingAs($this->admin)
            ->test(\App\Modules\Settings\Livewire\RoleForm::class, ['id' => $role->id])
            ->set('selectedPermissions', array_merge(
                $role->permissions->pluck('id')->map(fn ($id) => (string) $id)->toArray(),
                [(string) $manageSettings->id]
            ))
            ->call('save');

        // Refresh the permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Verify the user now has the permission
        $librarian->refresh();
        $this->assertTrue(
            $librarian->hasPermissionTo('manage-settings'),
            'After adding manage-settings to librarian role, user should have it immediately'
        );
    }

    public function test_removing_permission_takes_effect_immediately(): void
    {
        $librarian = User::factory()->create()->assignRole('librarian');

        // Initially should have view-books
        $this->assertTrue($librarian->hasPermissionTo('view-books'));

        // Remove view-books via RoleForm
        $role = Role::where('name', 'librarian')->first();
        $newPermissions = $role->permissions
            ->filter(fn ($p) => $p->name !== 'view-books')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        Livewire::actingAs($this->admin)
            ->test(\App\Modules\Settings\Livewire\RoleForm::class, ['id' => $role->id])
            ->set('selectedPermissions', $newPermissions)
            ->call('save');

        // Refresh the permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Verify the user no longer has the permission
        $librarian->refresh();
        $this->assertFalse(
            $librarian->hasPermissionTo('view-books'),
            'After removing view-books from librarian role, user should not have it'
        );
    }

    public function test_new_role_permissions_work_immediately(): void
    {
        // Create a new role
        Livewire::actingAs($this->admin)
            ->test(\App\Modules\Settings\Livewire\RoleForm::class)
            ->set('name', 'test-custom-role')
            ->set('guard_name', 'web')
            ->set('selectedPermissions', [
                (string) Permission::where('name', 'view-books')->first()->id,
                (string) Permission::where('name', 'view-members')->first()->id,
            ])
            ->call('save');

        $user = User::factory()->create()->assignRole('test-custom-role');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertTrue($user->hasPermissionTo('view-books'));
        $this->assertTrue($user->hasPermissionTo('view-members'));
        $this->assertFalse($user->hasPermissionTo('manage-settings'));
    }

    public function test_permission_check_on_route_enforces_new_permissions(): void
    {
        $librarian = User::factory()->create()->assignRole('librarian');

        // Librarian should be able to access circulation override-due-dates
        $response = $this->actingAs($librarian)->get(route('circulation.override-due-dates'));
        $this->assertContains($response->status(), [200, 302]);

        // Remove the permission
        $role = Role::where('name', 'librarian')->first();
        $newPermissions = $role->permissions
            ->filter(fn ($p) => $p->name !== 'override-due-dates')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        Livewire::actingAs($this->admin)
            ->test(\App\Modules\Settings\Livewire\RoleForm::class, ['id' => $role->id])
            ->set('selectedPermissions', $newPermissions)
            ->call('save');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Librarian should now be denied
        $response = $this->actingAs($librarian)->get(route('circulation.override-due-dates'));
        $this->assertContains($response->status(), [403, 302]);
    }

    public function test_user_toggle_active_respects_permissions(): void
    {
        $user = User::factory()->create(['is_active' => true])->assignRole('librarian');

        // User should be able to access dashboard while active
        $response = $this->actingAs($user)->get(route('dashboard'));
        $this->assertContains($response->status(), [200, 302]);

        // Deactivate user
        Livewire::actingAs($this->admin)
            ->test(\App\Modules\Settings\Livewire\UserList::class)
            ->call('toggleActive', $user->id);

        $user->refresh();
        $this->assertFalse($user->is_active);
    }

    public function test_role_delete_removes_permissions(): void
    {
        $role = Role::create(['name' => 'temp-role', 'guard_name' => 'web']);
        $role->syncPermissions(['view-books', 'view-members']);

        $user = User::factory()->create()->assignRole('temp-role');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertTrue($user->hasPermissionTo('view-books'));

        // Delete the role
        Livewire::actingAs($this->admin)
            ->test(\App\Modules\Settings\Livewire\RoleList::class)
            ->call('delete', $role->id);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $user->refresh();

        $this->assertFalse($user->hasPermissionTo('view-books'));
    }

    // =========================================================================
    // ROUTE BLOCKING: Verify unauthorized users get 403
    // =========================================================================

    public function test_unauthenticated_user_redirected_from_dashboard(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_settings(): void
    {
        $student = User::factory()->create()->assignRole('student');

        $response = $this->actingAs($student)->get(route('settings.index'));
        $this->assertEquals(403, $response->status());
    }

    public function test_student_cannot_access_circulation_admin(): void
    {
        $student = User::factory()->create()->assignRole('student');

        $response = $this->actingAs($student)->get(route('circulation.override-due-dates'));
        $this->assertEquals(403, $response->status());
    }

    public function test_student_cannot_access_finance(): void
    {
        $student = User::factory()->create()->assignRole('student');

        $response = $this->actingAs($student)->get(route('finance.transactions'));
        $this->assertEquals(403, $response->status());
    }

    public function test_student_cannot_access_members(): void
    {
        $student = User::factory()->create()->assignRole('student');

        $response = $this->actingAs($student)->get(route('members.index'));
        $this->assertEquals(403, $response->status());
    }

    public function test_librarian_can_access_circulation(): void
    {
        $librarian = User::factory()->create()->assignRole('librarian');

        $response = $this->actingAs($librarian)->get(route('circulation.override-due-dates'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_librarian_can_access_catalog(): void
    {
        $librarian = User::factory()->create()->assignRole('librarian');

        $response = $this->actingAs($librarian)->get(route('catalog.books.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    // =========================================================================
    // LIVEWIRE COMPONENT AUTHORIZATION
    // =========================================================================

    public function test_route_middleware_blocks_unauthorized_settings(): void
    {
        $student = User::factory()->create()->assignRole('student');

        $response = $this->actingAs($student)->get(route('settings.index'));
        $this->assertEquals(403, $response->status());
    }

    public function test_livewire_catalog_form_requires_permission(): void
    {
        $student = User::factory()->create()->assignRole('student');

        Livewire::actingAs($student)
            ->test(\App\Modules\Catalog\Livewire\AuthorForm::class)
            ->assertForbidden();
    }

    public function test_livewire_finance_dashboard_requires_permission(): void
    {
        $student = User::factory()->create()->assignRole('student');

        Livewire::actingAs($student)
            ->test(\App\Modules\Finance\Livewire\FinanceDashboard::class)
            ->assertForbidden();
    }

    public function test_route_middleware_blocks_unauthorized_livewire_routes(): void
    {
        $student = User::factory()->create()->assignRole('student');

        $response = $this->actingAs($student)->get(route('members.index'));
        $this->assertEquals(403, $response->status());

        $response = $this->actingAs($student)->get(route('circulation.issue'));
        $this->assertEquals(403, $response->status());

        $response = $this->actingAs($student)->get(route('finance.index'));
        $this->assertEquals(403, $response->status());
    }

    // =========================================================================
    // SESSION INVALIDATION ON ROLE CHANGE
    // =========================================================================

    public function test_role_change_event_listener_is_registered(): void
    {
        $events = app('events');
        $this->assertTrue(
            $events->hasListeners(\Spatie\Permission\Events\RoleAttachedEvent::class),
            'RoleAttachedEvent should have listeners registered'
        );
        $this->assertTrue(
            $events->hasListeners(\Spatie\Permission\Events\RoleDetachedEvent::class),
            'RoleDetachedEvent should have listeners registered'
        );
        $this->assertTrue(
            $events->hasListeners(\Spatie\Permission\Events\PermissionAttachedEvent::class),
            'PermissionAttachedEvent should have listeners registered'
        );
        $this->assertTrue(
            $events->hasListeners(\Spatie\Permission\Events\PermissionDetachedEvent::class),
            'PermissionDetachedEvent should have listeners registered'
        );
    }

    public function test_invalidate_sessions_listener_is_queued(): void
    {
        $listener = new \App\Listeners\InvalidateSessionsOnRoleChange();
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $listener);
    }

    // =========================================================================
    // BATCH 4: MANAGE-INVENTORY, PROCESS-SUBSCRIPTION-PAYMENTS, MANAGE-SCHEDULED-MESSAGES
    // =========================================================================

    public function test_manage_inventory_gates_status_changes(): void
    {
        $userWithout = User::factory()->create()->assignRole('student');
        $userWith = User::factory()->create();
        $userWith->givePermissionTo('manage-inventory');

        $category = \App\Modules\Catalog\Models\Category::create(['name' => 'Test Cat', 'slug' => 'test-cat-inv']);
        $publisher = \App\Modules\Catalog\Models\Publisher::create(['name' => 'Test Pub', 'slug' => 'test-pub-inv']);
        $book = \App\Modules\Catalog\Models\Book::create([
            'title' => 'Test Book for Inventory',
            'slug' => 'test-book-inv-' . uniqid(),
            'isbn' => '978-' . rand(1000000000, 9999999999),
            'category_id' => $category->id,
            'publisher_id' => $publisher->id,
            'publication_year' => 2024,
            'language' => 'en',
            'is_active' => true,
        ]);
        $copy = \App\Modules\Catalog\Models\BookCopy::create([
            'book_id' => $book->id,
            'barcode' => 'BC-' . uniqid(),
            'rfid_tag' => 'RFID-' . uniqid(),
            'status' => 'available',
            'condition' => 'good',
        ]);

        Livewire::actingAs($userWithout)
            ->test(\App\Modules\Catalog\Livewire\InventoryList::class)
            ->call('markDamaged', $copy->id)
            ->assertForbidden();

        Livewire::actingAs($userWith)
            ->test(\App\Modules\Catalog\Livewire\InventoryList::class)
            ->call('markDamaged', $copy->id)
            ->assertDispatched('notify');

        $copy->refresh();
        $this->assertEquals('damaged', $copy->status);
    }

    public function test_inventory_route_requires_view_inventory(): void
    {
        $student = User::factory()->create()->assignRole('student');
        $response = $this->actingAs($student)->get(route('catalog.inventory'));
        $this->assertEquals(403, $response->status());
    }

    public function test_process_subscription_payments_gates_confirmation(): void
    {
        Plan::factory()->create(['name' => 'Test', 'is_active' => true]);
        $userWithout = User::factory()->create()->assignRole('student');
        $userWith = User::factory()->create();
        $userWith->givePermissionTo('process-subscription-payments');

        $sub = \App\Modules\Subscriptions\Models\Subscription::factory()->create(['status' => 'pending']);

        Livewire::actingAs($userWithout)
            ->test(\App\Modules\Subscriptions\Livewire\Admin\SubscriptionList::class)
            ->call('processPayment', $sub->id)
            ->assertForbidden();

        Livewire::actingAs($userWith)
            ->test(\App\Modules\Subscriptions\Livewire\Admin\SubscriptionList::class)
            ->call('processPayment', $sub->id)
            ->assertDispatched('notify');

        $sub->refresh();
        $this->assertEquals('active', $sub->status);
    }

    public function test_manage_scheduled_messages_gates_cancel(): void
    {
        $userWithout = User::factory()->create()->assignRole('student');
        $userWith = User::factory()->create();
        $userWith->givePermissionTo('manage-scheduled-messages');

        $msg = \Database\Factories\MessageFactory::new()->create([
            'sender_id' => $userWith->id,
            'status' => \App\Modules\Communication\Models\Message::STATUS_SCHEDULED,
            'scheduled_at' => now()->addDay(),
        ]);

        Livewire::actingAs($userWithout)
            ->test(\App\Modules\Communication\Livewire\MessageList::class)
            ->call('cancelScheduled', $msg->id)
            ->assertForbidden();

        Livewire::actingAs($userWith)
            ->test(\App\Modules\Communication\Livewire\MessageList::class)
            ->call('cancelScheduled', $msg->id)
            ->assertDispatched('notify');
    }

    public function test_librarian_has_manage_inventory(): void
    {
        $librarian = User::factory()->create()->assignRole('librarian');
        $this->assertTrue(
            $librarian->hasPermissionTo('manage-inventory'),
            'Librarian should have manage-inventory permission'
        );
    }

    public function test_assistant_librarian_lacks_manage_scheduled_messages(): void
    {
        $assistant = User::factory()->create()->assignRole('assistant-librarian');
        $this->assertFalse(
            $assistant->hasPermissionTo('manage-scheduled-messages'),
            'Assistant librarian should not have manage-scheduled-messages permission'
        );
    }

    public function test_librarian_has_manage_scheduled_messages(): void
    {
        $librarian = User::factory()->create()->assignRole('librarian');
        $this->assertTrue(
            $librarian->hasPermissionTo('manage-scheduled-messages'),
            'Librarian should have manage-scheduled-messages permission'
        );
    }

    public function test_finance_officer_lacks_process_subscription_payments(): void
    {
        $finance = User::factory()->create()->assignRole('finance-officer');
        $this->assertFalse(
            $finance->hasPermissionTo('process-subscription-payments'),
            'Finance officer should not have process-subscription-payments permission'
        );
    }

    public function test_student_lacks_manage_inventory(): void
    {
        $student = User::factory()->create()->assignRole('student');
        $this->assertFalse($student->hasPermissionTo('manage-inventory'));
        $this->assertFalse($student->hasPermissionTo('process-subscription-payments'));
        $this->assertFalse($student->hasPermissionTo('manage-scheduled-messages'));
    }
}
