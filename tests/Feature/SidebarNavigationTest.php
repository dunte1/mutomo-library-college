<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@ollmchs.ac.ke')->first()
            ?? User::factory()->create(['email' => 'admin@ollmchs.ac.ke']);
        if (! $this->admin->hasRole('super-admin')) {
            $this->admin->assignRole('super-admin');
        }
    }

    // ------------------------------------------------------------------
    // Helper: every route name in the sidebar must actually exist
    // ------------------------------------------------------------------

    private static function staffSidebarRoutes(): array
    {
        return [
            // Dashboard
            'dashboard',

            // Catalog
            'catalog.books.index',
            'catalog.categories',
            'catalog.authors',
            'catalog.publishers',
            'catalog.inventory',
            'catalog.new-arrivals',
            'catalog.books.bulk-upload',

            // Circulation
            'circulation.index',
            'circulation.issue',
            'circulation.return',
            'circulation.reservations',
            'circulation.waitlists',
            'circulation.fines',
            'circulation.override-due-dates',

            // Members
            'members.index',
            'members.cards',
            'members.requests',
            'members.suspensions',

            // Digital Library
            'digital-library.index',
            'digital-library.upload',
            'digital-library.recommendations',
            'assignments.teacher',
            'digital-library.downloads',
            'digital-library.categories',

            // Finance
            'finance.index',
            'finance.transactions',
            'finance.fines',
            'finance.collect-payments',
            'finance.invoices',
            'finance.receipts',
            'finance.refunds',
            'finance.analytics',
            'finance.reports',

            // Subscriptions
            'admin.subscriptions.index',
            'admin.subscriptions.plans',

            // Reports
            'reports.dashboard',
            'reports.catalog',
            'reports.circulation',
            'reports.members',
            'reports.digital-library',

            // Communication & Engagement
            'communication.announcements.index',
            'communication.bulletins.index',
            'communication.events.index',
            'communication.messages.index',
            'communication.messages.create',
            'communication.broadcasts',
            'communication.templates.index',
            'communication.messages.logs',

            // Notifications
            'notifications.index',

            // Programs
            'settings.programs',

            // Administration > Settings
            'settings.index',
            'settings.general',
            'settings.circulation',
            'settings.digital-library',
            'settings.email',
            'settings.localization',
            'settings.appearance',
            'settings.ai-settings',
            'settings.subscriptions',
            'settings.auth-carousel',
            'settings.landing-page',
            'settings.features',
            'settings.why-choose-us',
            'settings.testimonials',
            'settings.newsletter-subscribers',
            'settings.maintenance',

            // Administration > User Management
            'settings.users',
            'settings.roles',
            'settings.access-levels',
            'settings.departments',

            // Administration > Security
            'settings.security.dashboard',
            'settings.audit-logs',
            'settings.system-logs',

            // Administration > System
            'settings.system-health',
            'settings.backup',
            'settings.notifications',
            'settings.queue-monitor',
            'settings.cache',
            'settings.storage',
        ];
    }

    private static function patronSidebarRoutes(): array
    {
        return [
            'dashboard',
            'catalog.books.index',
            'digital-library.index',
            'digital-library.recommendations',
            'circulation.index',
            'circulation.my-reservations',
            'assignments.my',
            'subscriptions.my',
            'profile',
        ];
    }

    // ------------------------------------------------------------------
    // Test: every sidebar route name exists
    // ------------------------------------------------------------------

    public function test_all_staff_sidebar_route_names_exist(): void
    {
        foreach (self::staffSidebarRoutes() as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Staff sidebar route '{$routeName}' does not exist"
            );
        }
    }

    public function test_all_patron_sidebar_route_names_exist(): void
    {
        foreach (self::patronSidebarRoutes() as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Patron sidebar route '{$routeName}' does not exist"
            );
        }
    }

    // ------------------------------------------------------------------
    // Test: every sidebar route generates a non-empty, correct URL
    // ------------------------------------------------------------------

    public function test_staff_sidebar_routes_generate_correct_urls(): void
    {
        $expectedUrls = [
            'dashboard' => '/dashboard',

            'catalog.books.index' => '/catalog/books',
            'catalog.categories' => '/catalog/categories',
            'catalog.authors' => '/catalog/authors',
            'catalog.publishers' => '/catalog/publishers',
            'catalog.inventory' => '/catalog/inventory',
            'catalog.new-arrivals' => '/catalog/new-arrivals',
            'catalog.books.bulk-upload' => '/catalog/books/bulk-upload',

            'circulation.index' => '/circulation',
            'circulation.issue' => '/circulation/issue',
            'circulation.return' => '/circulation/return',
            'circulation.reservations' => '/circulation/reservations',
            'circulation.waitlists' => '/circulation/waitlists',
            'circulation.fines' => '/circulation/fines',
            'circulation.override-due-dates' => '/circulation/override-due-dates',

            'members.index' => '/members',
            'members.cards' => '/members/cards',
            'members.requests' => '/members/requests',
            'members.suspensions' => '/members/suspensions',

            'digital-library.index' => '/digital-library',
            'digital-library.upload' => '/digital-library/upload',
            'digital-library.recommendations' => '/digital-library/recommendations',
            'assignments.teacher' => '/assignments/teacher',
            'digital-library.downloads' => '/digital-library/downloads',
            'digital-library.categories' => '/digital-library/categories',

            'finance.index' => '/finance',
            'finance.transactions' => '/finance/transactions',
            'finance.fines' => '/finance/fines',
            'finance.collect-payments' => '/finance/collect-payments',
            'finance.invoices' => '/finance/invoices',
            'finance.receipts' => '/finance/receipts',
            'finance.refunds' => '/finance/refunds',
            'finance.analytics' => '/finance/analytics',
            'finance.reports' => '/finance/reports',

            'admin.subscriptions.index' => '/admin/subscriptions',
            'admin.subscriptions.plans' => '/admin/subscriptions/plans',

            'reports.dashboard' => '/reports',
            'reports.catalog' => '/reports/catalog',
            'reports.circulation' => '/reports/circulation',
            'reports.members' => '/reports/members',
            'reports.digital-library' => '/reports/digital-library',

            'communication.announcements.index' => '/communication/announcements',
            'communication.bulletins.index' => '/communication/bulletins',
            'communication.events.index' => '/communication/events',
            'communication.messages.index' => '/communication/messages',
            'communication.messages.create' => '/communication/messages/create',
            'communication.broadcasts' => '/communication/broadcasts',
            'communication.templates.index' => '/communication/templates',
            'communication.messages.logs' => '/communication/messages/logs',

            'notifications.index' => '/notifications',

            'settings.programs' => '/settings/programs',

            'settings.index' => '/settings',
            'settings.general' => '/settings/general',
            'settings.circulation' => '/settings/circulation',
            'settings.digital-library' => '/settings/digital-library',
            'settings.email' => '/settings/email',
            'settings.localization' => '/settings/localization',
            'settings.appearance' => '/settings/appearance',
            'settings.ai-settings' => '/settings/ai-settings',
            'settings.subscriptions' => '/settings/subscriptions',
            'settings.auth-carousel' => '/settings/auth-carousel',
            'settings.landing-page' => '/settings/landing-page',
            'settings.features' => '/settings/features',
            'settings.why-choose-us' => '/settings/why-choose-us',
            'settings.testimonials' => '/settings/testimonials',
            'settings.newsletter-subscribers' => '/settings/newsletter-subscribers',
            'settings.maintenance' => '/settings/maintenance',

            'settings.users' => '/settings/users',
            'settings.roles' => '/settings/roles',
            'settings.access-levels' => '/settings/access-levels',
            'settings.departments' => '/settings/departments',

            'settings.security.dashboard' => '/settings/security/dashboard',
            'settings.audit-logs' => '/settings/audit-logs',
            'settings.system-logs' => '/settings/system-logs',

            'settings.system-health' => '/settings/system-health',
            'settings.backup' => '/settings/backup',
            'settings.notifications' => '/settings/notifications',
            'settings.queue-monitor' => '/settings/queue-monitor',
            'settings.cache' => '/settings/cache',
            'settings.storage' => '/settings/storage',
        ];

        foreach ($expectedUrls as $routeName => $expectedUri) {
            $generatedUri = route($routeName, [], false);
            $this->assertEquals(
                $expectedUri,
                $generatedUri,
                "Route '{$routeName}' generated URI '{$generatedUri}' but expected '{$expectedUri}'"
            );
        }
    }

    // ------------------------------------------------------------------
    // Test: sidebar links use `wire:navigate` (SPA navigation)
    // ------------------------------------------------------------------

    public function test_sidebar_renders_wire_navigate_links(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $html = $response->content();

        // Staff section links should have wire:navigate
        $staffLinks = [
            route('dashboard'),
            route('catalog.books.index'),
            route('catalog.categories'),
            route('catalog.authors'),
            route('catalog.publishers'),
            route('circulation.index'),
            route('circulation.issue'),
            route('circulation.return'),
            route('circulation.reservations'),
            route('circulation.waitlists'),
            route('circulation.fines'),
            route('circulation.override-due-dates'),
            route('members.index'),
            route('members.cards'),
            route('digital-library.index'),
            route('digital-library.upload'),
            route('digital-library.recommendations'),
            route('assignments.teacher'),
            route('finance.index'),
            route('finance.transactions'),
            route('finance.fines'),
            route('finance.collect-payments'),
            route('finance.invoices'),
            route('finance.receipts'),
            route('finance.refunds'),
            route('finance.analytics'),
            route('finance.reports'),
            route('settings.programs'),
            route('settings.general'),
            route('settings.users'),
            route('settings.roles'),
            route('settings.access-levels'),
            route('settings.departments'),
            route('settings.security.dashboard'),
            route('settings.audit-logs'),
            route('settings.system-logs'),
            route('settings.backup'),
            route('settings.notifications'),
        ];

        foreach ($staffLinks as $url) {
            $this->assertStringContainsString(
                'wire:navigate',
                $html,
                "Sidebar link to '{$url}' is missing wire:navigate attribute"
            );
        }
    }

    // ------------------------------------------------------------------
    // Test: no sidebar route redirects to dashboard
    // ------------------------------------------------------------------

    public function test_no_sidebar_route_redirects_to_dashboard(): void
    {
        $dashboardUrl = route('dashboard', [], false);

        $routesToCheck = array_merge(
            self::staffSidebarRoutes(),
            self::patronSidebarRoutes()
        );

        foreach ($routesToCheck as $routeName) {
            $uri = route($routeName, ['id' => 1, 'asset' => 1, 'plan' => 1, 'invoice' => 1, 'receipt' => 1, 'member' => 1], false);
            $response = $this->actingAs($this->admin)->get($uri);

            if ($response->isRedirect()) {
                $redirectUrl = $response->headers->get('Location');
                $this->assertFalse(
                    str_contains($redirectUrl ?? '', $dashboardUrl),
                    "Route '{$routeName}' ({$uri}) redirects to Dashboard ({$redirectUrl})"
                );
            }
        }
    }

    // ------------------------------------------------------------------
    // Test: every staff sidebar route is accessible (200/302/403)
    // ------------------------------------------------------------------

    public function test_all_staff_sidebar_routes_are_accessible(): void
    {
        $routesWithParams = [
            'dashboard' => [],
            'catalog.books.index' => [],
            'catalog.categories' => [],
            'catalog.authors' => [],
            'catalog.publishers' => [],
            'catalog.inventory' => [],
            'catalog.new-arrivals' => [],
            'catalog.books.bulk-upload' => [],
            'circulation.index' => [],
            'circulation.issue' => [],
            'circulation.return' => [],
            'circulation.reservations' => [],
            'circulation.waitlists' => [],
            'circulation.fines' => [],
            'circulation.override-due-dates' => [],
            'members.index' => [],
            'members.cards' => [],
            'members.requests' => [],
            'members.suspensions' => [],
            'digital-library.index' => [],
            'digital-library.upload' => [],
            'digital-library.recommendations' => [],
            'assignments.teacher' => [],
            'digital-library.downloads' => [],
            'digital-library.categories' => [],
            'finance.index' => [],
            'finance.transactions' => [],
            'finance.fines' => [],
            'finance.collect-payments' => [],
            'finance.invoices' => [],
            'finance.receipts' => [],
            'finance.refunds' => [],
            'finance.analytics' => [],
            'finance.reports' => [],
            'admin.subscriptions.index' => [],
            'admin.subscriptions.plans' => [],
            'reports.dashboard' => [],
            'reports.catalog' => [],
            'reports.circulation' => [],
            'reports.members' => [],
            'reports.digital-library' => [],
            'communication.announcements.index' => [],
            'communication.bulletins.index' => [],
            'communication.events.index' => [],
            'communication.messages.index' => [],
            'communication.messages.create' => [],
            'communication.broadcasts' => [],
            'communication.templates.index' => [],
            'communication.messages.logs' => [],
            'notifications.index' => [],
            'settings.programs' => [],
            'settings.index' => [],
            'settings.general' => [],
            'settings.circulation' => [],
            'settings.digital-library' => [],
            'settings.email' => [],
            'settings.localization' => [],
            'settings.appearance' => [],
            'settings.ai-settings' => [],
            'settings.subscriptions' => [],
            'settings.auth-carousel' => [],
            'settings.landing-page' => [],
            'settings.features' => [],
            'settings.why-choose-us' => [],
            'settings.testimonials' => [],
            'settings.newsletter-subscribers' => [],
            'settings.maintenance' => [],
            'settings.users' => [],
            'settings.roles' => [],
            'settings.access-levels' => [],
            'settings.departments' => [],
            'settings.security.dashboard' => [],
            'settings.audit-logs' => [],
            'settings.system-logs' => [],
            'settings.system-health' => [],
            'settings.backup' => [],
            'settings.notifications' => [],
            'settings.queue-monitor' => [],
            'settings.cache' => [],
            'settings.storage' => [],
        ];

        foreach ($routesWithParams as $routeName => $params) {
            $uri = route($routeName, $params, false);
            $response = $this->actingAs($this->admin)->get($uri);
            $status = $response->status();
            $this->assertTrue(
                in_array($status, [200, 302, 403]),
                "Staff sidebar route '{$routeName}' ({$uri}) returned status {$status} (expected 200, 302, or 403)"
            );
        }
    }

    // ------------------------------------------------------------------
    // Test: every patron sidebar route is accessible
    // ------------------------------------------------------------------

    public function test_all_patron_sidebar_routes_are_accessible(): void
    {
        $patron = User::factory()->create();
        if (! $patron->hasRole('student')) {
            $patron->assignRole('student');
        }

        $routesWithParams = [
            'dashboard' => [],
            'catalog.books.index' => [],
            'digital-library.index' => [],
            'digital-library.recommendations' => [],
            'circulation.index' => [],
            'circulation.my-reservations' => [],
            'assignments.my' => [],
            'subscriptions.my' => [],
            'profile' => [],
        ];

        foreach ($routesWithParams as $routeName => $params) {
            $uri = route($routeName, $params, false);
            $response = $this->actingAs($patron)->get($uri);
            $status = $response->status();
            $this->assertTrue(
                in_array($status, [200, 302, 403]),
                "Patron sidebar route '{$routeName}' ({$uri}) returned status {$status} (expected 200, 302, or 403)"
            );
        }
    }

    // ------------------------------------------------------------------
    // Test: sidebar contains all expected section labels
    // ------------------------------------------------------------------

    public function test_sidebar_renders_all_section_labels(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $html = $response->content();

        $sectionLabels = [
            'Catalog',
            'Circulation',
            'Members',
            'Digital Library',
            'Finance',
            'Subscriptions',
            'Reports',
            'Communication',
            'Programs',
            'Administration',
            'Settings',
            'User Management',
            'Security',
            'System',
        ];

        foreach ($sectionLabels as $label) {
            $this->assertStringContainsString(
                $label,
                $html,
                "Sidebar is missing section label: '{$label}'"
            );
        }
    }

    // ------------------------------------------------------------------
    // Test: sidebar renders all expected menu item text
    // ------------------------------------------------------------------

    public function test_sidebar_renders_all_menu_item_labels(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $html = $response->content();

        $menuLabels = [
            'Dashboard',
            'Books Catalog',
            'Categories',
            'Authors',
            'Publishers',
            'Inventory',
            'New Arrivals',
            'Import Books',
            'Export Books',
            'Overview',
            'Issue Book',
            'Return Book',
            'Renew Book',
            'Reservations',
            'Waitlists',
            'Fines',
            'Borrows / Loans',
            'Override Due Dates',
            'Members List',
            'Library Cards',
            'Membership Requests',
            'Suspensions',
            'Assets',
            'Upload Asset',
            'Recommendations',
            'Reading Assignments',
            'Downloads',
            'Digital Categories',
            'Transactions',
            'Collect Payments',
            'Invoices',
            'Receipts',
            'Refunds',
            'Analytics',
            'All Subscriptions',
            'Plans',
            'Catalog Reports',
            'Circulation Reports',
            'Member Reports',
            'Digital Library Reports',
            'Finance Reports',
            'Scheduled Reports',
            'Announcements',
            'Bulletins',
            'Events',
            'Inbox',
            'Compose Message',
            'Broadcast Messages',
            'Message Templates',
            'Message Logs',
            'Send Notifications',
            'Notification Logs',
            'Programs',
            'General',
            'Circulation',
            'Digital Library',
            'Email',
            'Localization',
            'Appearance',
            'AI Settings',
            'Subscription Pricing',
            'Auth Carousel',
            'Landing Page',
            'Features',
            'Why Choose Us',
            'Testimonials',
            'Newsletter Subscribers',
            'Maintenance',
            'Users',
            'Roles',
            'Access Levels',
            'Departments',
            'Security Dashboard',
            'Audit Logs',
            'System Logs',
            'System Health',
            'Backup',
            'Notification Settings',
            'Queue Monitor',
            'Cache Management',
            'Storage Manager',
            'Logout',
        ];

        foreach ($menuLabels as $label) {
            $this->assertStringContainsString(
                $label,
                $html,
                "Sidebar is missing menu item label: '{$label}'"
            );
        }
    }

    // ------------------------------------------------------------------
    // Test: no sidebar route generates the dashboard URL (except dashboard itself)
    // ------------------------------------------------------------------

    public function test_no_sidebar_route_collides_with_dashboard_url(): void
    {
        $dashboardUrl = route('dashboard', [], false);

        $routes = array_filter(
            self::staffSidebarRoutes(),
            fn ($r) => $r !== 'dashboard'
        );

        foreach ($routes as $routeName) {
            $generatedUri = route($routeName, ['id' => 1, 'asset' => 1, 'plan' => 1, 'invoice' => 1, 'receipt' => 1, 'member' => 1], false);
            $this->assertNotEquals(
                $dashboardUrl,
                $generatedUri,
                "Route '{$routeName}' generates the same URL as Dashboard ({$generatedUri})"
            );
        }
    }

    // ------------------------------------------------------------------
    // Test: conditional routes (Route::has guards) actually exist
    // ------------------------------------------------------------------

    public function test_conditional_sidebar_routes_exist_in_application(): void
    {
        $conditionalRoutes = [
            'catalog.inventory',
            'catalog.new-arrivals',
            'members.requests',
            'members.suspensions',
            'digital-library.downloads',
            'digital-library.categories',
            'admin.subscriptions.index',
            'admin.subscriptions.plans',
            'reports.dashboard',
            'reports.catalog',
            'reports.circulation',
            'reports.members',
            'reports.digital-library',
            'communication.messages.index',
            'communication.messages.create',
            'communication.broadcasts',
            'communication.templates.index',
            'communication.messages.logs',
            'notifications.index',
            'settings.system-health',
            'settings.queue-monitor',
            'settings.cache',
            'settings.storage',
        ];

        foreach ($conditionalRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Conditional sidebar route '{$routeName}' is missing (sidebar uses Route::has guard but route is not defined)"
            );
        }
    }

    // ------------------------------------------------------------------
    // Test: sidebar has logout button
    // ------------------------------------------------------------------

    public function test_sidebar_renders_logout_button(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $html = $response->content();

        $this->assertStringContainsString('wire:click="logout"', $html, 'Sidebar is missing logout button with wire:click="logout"');
        $this->assertStringContainsString('Logout', $html, 'Sidebar is missing logout text');
    }

    // ------------------------------------------------------------------
    // Test: navigation role structure exists in sidebar
    // ------------------------------------------------------------------

    public function test_sidebar_role_structures_exist(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $html = $response->content();

        // Staff navigation should contain Administration and Settings sections
        $this->assertStringContainsString('Administration', $html, 'Sidebar is missing staff Administration section');
        $this->assertStringContainsString('Settings', $html, 'Sidebar is missing staff Settings section');
        $this->assertStringContainsString('User Management', $html, 'Sidebar is missing staff User Management section');
        $this->assertStringContainsString('Security', $html, 'Sidebar is missing staff Security section');
        $this->assertStringContainsString('System', $html, 'Sidebar is missing staff System section');
    }

    // ------------------------------------------------------------------
    // Test: sidebar link text matches expected menu item names
    // ------------------------------------------------------------------

    public function test_sidebar_links_have_correct_href_attributes(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $html = $response->content();

        $linkChecks = [
            ['text' => 'Books Catalog', 'url' => route('catalog.books.index')],
            ['text' => 'Categories', 'url' => route('catalog.categories')],
            ['text' => 'Authors', 'url' => route('catalog.authors')],
            ['text' => 'Publishers', 'url' => route('catalog.publishers')],
            ['text' => 'Issue Book', 'url' => route('circulation.issue')],
            ['text' => 'Return Book', 'url' => route('circulation.return')],
            ['text' => 'Reservations', 'url' => route('circulation.reservations')],
            ['text' => 'Waitlists', 'url' => route('circulation.waitlists')],
            ['text' => 'Fines', 'url' => route('circulation.fines')],
            ['text' => 'Override Due Dates', 'url' => route('circulation.override-due-dates')],
            ['text' => 'Members List', 'url' => route('members.index')],
            ['text' => 'Library Cards', 'url' => route('members.cards')],
            ['text' => 'Assets', 'url' => route('digital-library.index')],
            ['text' => 'Upload Asset', 'url' => route('digital-library.upload')],
            ['text' => 'Recommendations', 'url' => route('digital-library.recommendations')],
            ['text' => 'Transactions', 'url' => route('finance.transactions')],
            ['text' => 'Invoices', 'url' => route('finance.invoices')],
            ['text' => 'Receipts', 'url' => route('finance.receipts')],
            ['text' => 'Refunds', 'url' => route('finance.refunds')],
            ['text' => 'Analytics', 'url' => route('finance.analytics')],
            ['text' => 'Announcements', 'url' => route('communication.announcements.index')],
            ['text' => 'Bulletins', 'url' => route('communication.bulletins.index')],
            ['text' => 'Events', 'url' => route('communication.events.index')],
            ['text' => 'Programs', 'url' => route('settings.programs')],
            ['text' => 'General', 'url' => route('settings.general')],
            ['text' => 'Email', 'url' => route('settings.email')],
            ['text' => 'Localization', 'url' => route('settings.localization')],
            ['text' => 'Appearance', 'url' => route('settings.appearance')],
            ['text' => 'Users', 'url' => route('settings.users')],
            ['text' => 'Roles', 'url' => route('settings.roles')],
            ['text' => 'Access Levels', 'url' => route('settings.access-levels')],
            ['text' => 'Departments', 'url' => route('settings.departments')],
            ['text' => 'Security Dashboard', 'url' => route('settings.security.dashboard')],
            ['text' => 'Audit Logs', 'url' => route('settings.audit-logs')],
            ['text' => 'System Logs', 'url' => route('settings.system-logs')],
            ['text' => 'Backup', 'url' => route('settings.backup')],
        ];

        foreach ($linkChecks as ['text' => $text, 'url' => $url]) {
            $this->assertStringContainsString(
                'href="'.$url.'"',
                $html,
                "Sidebar link for '{$text}' does not point to expected URL '{$url}'"
            );
        }
    }

    // ------------------------------------------------------------------
    // Test: sidebar has no duplicate route names (within each role section)
    // ------------------------------------------------------------------

    public function test_staff_sidebar_route_names_are_unique(): void
    {
        $staffRoutes = self::staffSidebarRoutes();
        $uniqueRoutes = array_unique($staffRoutes);
        $this->assertCount(
            count($uniqueRoutes),
            $staffRoutes,
            'Staff sidebar contains duplicate route names'
        );
    }

    public function test_patron_sidebar_route_names_are_unique(): void
    {
        $patronRoutes = self::patronSidebarRoutes();
        $uniqueRoutes = array_unique($patronRoutes);
        $this->assertCount(
            count($uniqueRoutes),
            $patronRoutes,
            'Patron sidebar contains duplicate route names'
        );
    }

    // ------------------------------------------------------------------
    // Test: sub-menu group labels are rendered
    // ------------------------------------------------------------------

    public function test_sidebar_subgroup_labels_are_rendered(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $html = $response->content();

        $subgroupLabels = [
            'Messaging',
            'Notifications',
        ];

        foreach ($subgroupLabels as $label) {
            $this->assertStringContainsString(
                $label,
                $html,
                "Sidebar is missing sub-group label: '{$label}'"
            );
        }
    }

    // ------------------------------------------------------------------
    // Test: Library Cards route is correctly wired (not members.index)
    // ------------------------------------------------------------------

    public function test_library_cards_route_is_wired_correctly(): void
    {
        $libraryCardsUrl = route('members.cards', [], false);
        $membersIndexUrl = route('members.index', [], false);

        $this->assertNotEquals(
            $membersIndexUrl,
            $libraryCardsUrl,
            'Library Cards route should not point to Members List URL'
        );

        $this->assertStringStartsWith(
            '/members/cards',
            $libraryCardsUrl,
            'Library Cards route should start with /members/cards'
        );
    }

    // ------------------------------------------------------------------
    // Test: sidebar contains aria-label for accessibility
    // ------------------------------------------------------------------

    public function test_sidebar_has_aria_label(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $html = $response->content();

        $this->assertStringContainsString(
            'aria-label="Main navigation"',
            $html,
            'Sidebar aside element is missing aria-label="Main navigation"'
        );
    }

    // ------------------------------------------------------------------
    // Test: sidebar nav element has aria-label
    // ------------------------------------------------------------------

    public function test_sidebar_nav_has_aria_label(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $html = $response->content();

        $this->assertStringContainsString(
            'aria-label="Sidebar links"',
            $html,
            'Sidebar nav element is missing aria-label="Sidebar links"'
        );
    }

    // ------------------------------------------------------------------
    // Test: sidebar supports collapse toggle
    // ------------------------------------------------------------------

    public function test_sidebar_has_collapse_toggle(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertStatus(200);

        $html = $response->content();

        $this->assertStringContainsString(
            'data-collapsed',
            $html,
            'Sidebar is missing data-collapsed attribute for collapse toggle'
        );

        $this->assertStringContainsString(
            'aria-label="Toggle sidebar"',
            $html,
            'Sidebar collapse button is missing aria-label="Toggle sidebar"'
        );
    }
}
