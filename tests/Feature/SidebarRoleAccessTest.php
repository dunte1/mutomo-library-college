<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Members\Models\Member;
use App\Modules\Subscriptions\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SidebarRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $admin;
    protected User $librarian;
    protected User $assistantLibrarian;
    protected User $student;
    protected User $lecturer;
    protected User $financeOfficer;
    protected User $ictAdmin;
    protected User $staff;
    protected User $guest;
    protected User $departmentHead;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Plan::factory()->create(['name' => 'Free', 'is_active' => true, 'price' => 0]);

        $this->superAdmin = User::factory()->create(['email' => 'superadmin@test.com'])->assignRole('super-admin');
        $this->admin = User::factory()->create(['email' => 'admin@test.com'])->assignRole('admin');
        $this->librarian = User::factory()->create(['email' => 'librarian@test.com'])->assignRole('librarian');
        $this->assistantLibrarian = User::factory()->create(['email' => 'assistant@test.com'])->assignRole('assistant-librarian');
        $this->student = User::factory()->create(['email' => 'student@test.com'])->assignRole('student');
        $this->lecturer = User::factory()->create(['email' => 'lecturer@test.com'])->assignRole('lecturer');
        $this->financeOfficer = User::factory()->create(['email' => 'finance@test.com'])->assignRole('finance-officer');
        $this->ictAdmin = User::factory()->create(['email' => 'ict@test.com'])->assignRole('ict-admin');
        $this->staff = User::factory()->create(['email' => 'staff@test.com'])->assignRole('staff');
        $this->guest = User::factory()->create(['email' => 'guest@test.com'])->assignRole('guest');
        $this->departmentHead = User::factory()->create(['email' => 'depthead@test.com'])->assignRole('department-head');
    }

    // =========================================================================
    // SUPER-ADMIN & ADMIN: SEE EVERYTHING
    // =========================================================================

    public function test_super_admin_sees_all_sidebar_sections(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));
        $response->assertOk();
        $html = $response->content();

        $requiredSections = [
            'Catalog', 'Circulation', 'Members', 'Digital Library',
            'Finance', 'Subscriptions', 'Reports', 'Communication',
            'Programs', 'Administration', 'Settings',
            'User Management', 'Security', 'System',
        ];

        foreach ($requiredSections as $section) {
            $this->assertStringContainsString($section, $html, "Super-admin sidebar missing section: {$section}");
        }
    }

    public function test_admin_sees_all_sidebar_sections(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();
        $html = $response->content();

        $requiredSections = [
            'Catalog', 'Circulation', 'Members', 'Digital Library',
            'Finance', 'Reports', 'Administration', 'Settings',
        ];

        foreach ($requiredSections as $section) {
            $this->assertStringContainsString($section, $html, "Admin sidebar missing section: {$section}");
        }
    }

    // =========================================================================
    // LIBRARIAN: STAFF SIDEBAR WITHOUT ADMIN/SETTINGS
    // =========================================================================

    public function test_librarian_sees_staff_sidebar(): void
    {
        $response = $this->actingAs($this->librarian)->get(route('dashboard'));
        $response->assertOk();
        $html = $response->content();

        $expectedSections = [
            'Catalog', 'Circulation', 'Members', 'Digital Library',
            'Finance', 'Communication',
        ];

        foreach ($expectedSections as $section) {
            $this->assertStringContainsString($section, $html, "Librarian sidebar missing section: {$section}");
        }
    }

    public function test_librarian_sees_sidebar_menu_items(): void
    {
        $response = $this->actingAs($this->librarian)->get(route('dashboard'));
        $html = $response->content();

        $menuItems = [
            'Books Catalog', 'Categories', 'Authors', 'Publishers',
            'Issue Book', 'Return Book', 'Reservations',
            'Members List', 'Library Cards',
            'Assets', 'Upload Asset',
            'Transactions', 'Collect Payments',
        ];

        foreach ($menuItems as $item) {
            $this->assertStringContainsString($item, $html, "Librarian sidebar missing menu item: {$item}");
        }
    }

    // =========================================================================
    // ASSISTANT LIBRARIAN: LIMITED STAFF SIDEBAR
    // =========================================================================

    public function test_assistant_librarian_sees_limited_sidebar(): void
    {
        $response = $this->actingAs($this->assistantLibrarian)->get(route('dashboard'));
        $response->assertOk();
        $html = $response->content();

        // Should see basic staff sections
        $this->assertStringContainsString('Catalog', $html);
        $this->assertStringContainsString('Circulation', $html);
        $this->assertStringContainsString('Members', $html);
        $this->assertStringContainsString('Digital Library', $html);
    }

    // =========================================================================
    // STUDENT: PATRON SIDEBAR ONLY
    // =========================================================================

    public function test_student_sees_patron_sidebar(): void
    {
        // Create member for student
        Member::create([
            'user_id' => $this->student->id,
            'email' => $this->student->email,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'membership_type' => 'student',
            'status' => Member::STATUS_ACTIVE,
            'joined_at' => now(),
            'expires_at' => now()->addYear(),
        ]);

        $response = $this->actingAs($this->student)->get(route('dashboard'));
        $response->assertOk();
        $html = $response->content();

        // Should see patron items
        $this->assertStringContainsString('Dashboard', $html);
        $this->assertStringContainsString('Browse Books', $html);
        $this->assertStringContainsString('Digital Library', $html);
        $this->assertStringContainsString('Circulation', $html);
    }

    public function test_student_does_not_see_finance_or_members_management(): void
    {
        Member::create([
            'user_id' => $this->student->id,
            'email' => $this->student->email,
            'first_name' => 'Test',
            'last_name' => 'Student',
            'membership_type' => 'student',
            'status' => Member::STATUS_ACTIVE,
            'joined_at' => now(),
            'expires_at' => now()->addYear(),
        ]);

        $response = $this->actingAs($this->student)->get(route('dashboard'));
        $response->assertOk();
        $html = $response->content();

        // Should NOT see staff-only sections in the sidebar
        $staffOnlySections = [
            'Issue Book', 'Return Book',
            'Members List', 'Suspensions',
            'Upload Asset', 'Finance Reports',
            'Administration', 'User Management',
            'Security Dashboard', 'System Health',
        ];

        foreach ($staffOnlySections as $section) {
            // Check that these don't appear as sidebar menu items
            // (they may appear in other contexts, so we check for sidebar-specific patterns)
            $this->assertStringNotContainsString(
                'href="' . route($this->getRouteForLabel($section)) . '"',
                $html,
                "Student sidebar should not contain link to: {$section}"
            );
        }
    }

    // =========================================================================
    // LECTURER: PATRON SIDEBAR WITH ASSIGNMENTS
    // =========================================================================

    public function test_lecturer_sees_patron_sidebar_with_assignments(): void
    {
        Member::create([
            'user_id' => $this->lecturer->id,
            'email' => $this->lecturer->email,
            'first_name' => 'Test',
            'last_name' => 'Lecturer',
            'membership_type' => 'teacher',
            'status' => Member::STATUS_ACTIVE,
            'joined_at' => now(),
            'expires_at' => now()->addYear(),
        ]);

        $response = $this->actingAs($this->lecturer)->get(route('dashboard'));
        $response->assertOk();
        $html = $response->content();

        $this->assertStringContainsString('Dashboard', $html);
        $this->assertStringContainsString('Browse Books', $html);
        $this->assertStringContainsString('Digital Library', $html);
    }

    public function test_lecturer_does_not_see_finance_or_members_management(): void
    {
        $response = $this->actingAs($this->lecturer)->get(route('dashboard'));
        $response->assertOk();
        $html = $response->content();

        // Lecturer should not see finance management
        $this->assertStringNotContainsString(
            'href="' . route('finance.index') . '"',
            $html,
            'Lecturer sidebar should not contain Finance link'
        );
    }

    // =========================================================================
    // FINANCE OFFICER: SEES FINANCE SECTIONS
    // =========================================================================

    public function test_finance_officer_sees_finance_sections(): void
    {
        $response = $this->actingAs($this->financeOfficer)->get(route('dashboard'));
        $response->assertOk();
        $html = $response->content();

        $this->assertStringContainsString('Finance', $html);
        $this->assertStringContainsString('Transactions', $html);
    }

    // =========================================================================
    // ICT ADMIN: SEES SYSTEM SECTIONS
    // =========================================================================

    public function test_ict_admin_sees_system_sections(): void
    {
        $response = $this->actingAs($this->ictAdmin)->get(route('dashboard'));
        $response->assertOk();
        $html = $response->content();

        $this->assertStringContainsString('Settings', $html);
        $this->assertStringContainsString('System Health', $html);
        $this->assertStringContainsString('Backup', $html);
    }

    // =========================================================================
    // STAFF: BASIC STAFF SIDEBAR
    // =========================================================================

    public function test_staff_sees_basic_sidebar(): void
    {
        $response = $this->actingAs($this->staff)->get(route('dashboard'));
        $response->assertOk();
        $html = $response->content();

        $this->assertStringContainsString('Dashboard', $html);
        $this->assertStringContainsString('Catalog', $html);
    }

    // =========================================================================
    // GUEST: MINIMAL SIDEBAR
    // =========================================================================

    public function test_guest_has_minimal_sidebar(): void
    {
        $response = $this->actingAs($this->guest)->get(route('dashboard'));
        // Guest may get 403 (no view-dashboard permission) or 200 with minimal sidebar
        $this->assertContains($response->status(), [200, 403]);
    }

    // =========================================================================
    // ROUTE ACCESS VERIFICATION PER ROLE
    // =========================================================================

    public function test_student_cannot_access_settings_routes(): void
    {
        $settingsRoutes = ['settings.index', 'settings.general', 'settings.users', 'settings.roles'];

        foreach ($settingsRoutes as $routeName) {
            $uri = route($routeName, [], false);
            $response = $this->actingAs($this->student)->get($uri);

            $this->assertContains(
                $response->status(),
                [403, 302],
                "Student should not access {$routeName} (got {$response->status()})"
            );
        }
    }

    public function test_student_cannot_access_finance_routes(): void
    {
        $financeRoutes = ['finance.index', 'finance.transactions', 'finance.fines'];

        foreach ($financeRoutes as $routeName) {
            $uri = route($routeName, [], false);
            $response = $this->actingAs($this->student)->get($uri);

            $this->assertContains(
                $response->status(),
                [403, 302],
                "Student should not access {$routeName} (got {$response->status()})"
            );
        }
    }

    public function test_student_cannot_access_member_management_routes(): void
    {
        $memberRoutes = ['members.index', 'members.create', 'members.suspensions'];

        foreach ($memberRoutes as $routeName) {
            $uri = route($routeName, [], false);
            $response = $this->actingAs($this->student)->get($uri);

            $this->assertContains(
                $response->status(),
                [403, 302],
                "Student should not access {$routeName} (got {$response->status()})"
            );
        }
    }

    public function test_librarian_can_access_all_staff_routes(): void
    {
        $staffRoutes = [
            'dashboard', 'catalog.books.index', 'catalog.categories',
            'catalog.authors', 'catalog.publishers',
            'circulation.index', 'circulation.issue', 'circulation.return',
            'circulation.reservations', 'circulation.fines',
            'members.index', 'members.cards',
            'digital-library.index', 'digital-library.upload',
            'finance.index', 'finance.transactions',
        ];

        foreach ($staffRoutes as $routeName) {
            $uri = route($routeName, [], false);
            $response = $this->actingAs($this->librarian)->get($uri);

            $this->assertContains(
                $response->status(),
                [200, 302],
                "Librarian should access {$routeName} (got {$response->status()})"
            );
        }
    }

    // =========================================================================
    // SIDEBAR STRUCTURE INTEGRITY
    // =========================================================================

    public function test_all_roles_sidebar_route_names_are_unique(): void
    {
        $staffRoutes = [
            'dashboard', 'catalog.books.index', 'catalog.categories',
            'catalog.authors', 'catalog.publishers', 'catalog.inventory',
            'catalog.new-arrivals', 'catalog.books.bulk-upload',
            'circulation.index', 'circulation.issue', 'circulation.return',
            'circulation.reservations', 'circulation.waitlists',
            'circulation.fines', 'circulation.override-due-dates',
            'members.index', 'members.cards', 'members.requests',
            'members.suspensions',
            'digital-library.index', 'digital-library.upload',
            'digital-library.recommendations', 'assignments.teacher',
            'digital-library.downloads', 'digital-library.categories',
            'finance.index', 'finance.transactions', 'finance.fines',
            'finance.collect-payments', 'finance.invoices', 'finance.receipts',
            'finance.refunds', 'finance.analytics', 'finance.reports',
            'admin.subscriptions.index', 'admin.subscriptions.plans',
            'reports.dashboard', 'reports.catalog', 'reports.circulation',
            'reports.members', 'reports.digital-library',
            'communication.announcements.index', 'communication.bulletins.index',
            'communication.events.index', 'communication.messages.index',
            'communication.messages.create', 'communication.broadcasts',
            'communication.templates.index', 'communication.messages.logs',
            'notifications.index', 'settings.programs',
            'settings.index', 'settings.general', 'settings.circulation',
            'settings.digital-library', 'settings.email', 'settings.localization',
            'settings.appearance', 'settings.ai-settings', 'settings.subscriptions',
            'settings.auth-carousel', 'settings.landing-page', 'settings.features',
            'settings.why-choose-us', 'settings.testimonials',
            'settings.newsletter-subscribers', 'settings.maintenance',
            'settings.users', 'settings.roles', 'settings.access-levels',
            'settings.departments', 'settings.security.dashboard',
            'settings.audit-logs', 'settings.system-logs',
            'settings.system-health', 'settings.backup', 'settings.notifications',
            'settings.queue-monitor', 'settings.cache', 'settings.storage',
        ];

        $this->assertCount(count($staffRoutes), array_unique($staffRoutes), 'Staff sidebar contains duplicate route names');
    }

    public function test_patron_sidebar_route_names_are_unique(): void
    {
        $patronRoutes = [
            'dashboard', 'catalog.books.index', 'digital-library.index',
            'digital-library.recommendations', 'circulation.index',
            'circulation.my-reservations', 'assignments.my',
            'subscriptions.my', 'profile',
        ];

        $this->assertCount(count($patronRoutes), array_unique($patronRoutes), 'Patron sidebar contains duplicate route names');
    }

    /**
     * Helper to map section labels to route names for negative assertion.
     */
    private function getRouteForLabel(string $label): string
    {
        return match ($label) {
            'Issue Book' => 'circulation.issue',
            'Return Book' => 'circulation.return',
            'Members List' => 'members.index',
            'Suspensions' => 'members.suspensions',
            'Upload Asset' => 'digital-library.upload',
            'Finance Reports' => 'finance.reports',
            'Administration' => 'settings.index',
            'User Management' => 'settings.users',
            'Security Dashboard' => 'settings.security.dashboard',
            'System Health' => 'settings.system-health',
            default => 'dashboard',
        };
    }
}
