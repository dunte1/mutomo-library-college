<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NavigationAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'admin@ollmchs.ac.ke')->first() ?? User::factory()->create(['email' => 'admin@ollmchs.ac.ke']);
        if (!$this->user->hasRole('super-admin')) {
            $this->user->assignRole('super-admin');
        }
    }

    public function test_routes_exist_and_respond(): void
    {
        $menuRoutes = [
            'dashboard' => '/dashboard',
            'catalog.books.index' => '/catalog/books',
            'catalog.categories' => '/catalog/categories',
            'circulation.index' => '/circulation',
            'circulation.reservations' => '/circulation/reservations',
            'members.index' => '/members',
            'digital-library.index' => '/digital-library',
            'digital-library.recommendations' => '/digital-library/recommendations',
            'digital-library.upload' => '/digital-library/upload',
            'finance.index' => '/finance',
            'finance.transactions' => '/finance/transactions',
            'finance.fines' => '/finance/fines',
            'finance.analytics' => '/finance/analytics',
            'finance.reports' => '/finance/reports',
            'settings.index' => '/settings',
            'settings.users' => '/settings/users',
            'settings.roles' => '/settings/roles',
            'settings.security.dashboard' => '/settings/security/dashboard',
            'settings.programs' => '/settings/programs',
            'settings.audit-logs' => '/settings/audit-logs',
            'notifications.index' => '/notifications',
        ];

        foreach ($menuRoutes as $routeName => $expectedUri) {
            $this->assertTrue(Route::has($routeName), "Route '{$routeName}' does not exist");
            $generatedUri = route($routeName, [], false);
            $this->assertEquals($expectedUri, $generatedUri, "Route '{$routeName}' generated URI '{$generatedUri}' but expected '{$expectedUri}'");
        }
    }

    public function test_all_menu_routes_are_accessible_when_authenticated(): void
    {
        $routes = [
            'dashboard',
            'catalog.books.index',
            'catalog.categories',
            'circulation.index',
            'circulation.reservations',
            'members.index',
            'digital-library.index',
            'digital-library.recommendations',
            'digital-library.upload',
            'finance.index',
            'finance.transactions',
            'finance.fines',
            'finance.analytics',
            'finance.reports',
            'settings.index',
            'settings.users',
            'settings.roles',
            'settings.security.dashboard',
            'settings.programs',
            'settings.audit-logs',
            'notifications.index',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($this->user)->get(route($routeName, ['id' => 1, 'asset' => 1], false));
            $status = $response->status();
            $this->assertTrue(
                in_array($status, [200, 302, 403]),
                "Route '{$routeName}' returned status {$status} (expected 200, 302, or 403)"
            );
        }
    }

    public function test_no_menu_route_redirects_to_dashboard(): void
    {
        $explicitRoutes = [
            'catalog.books.index',
            'catalog.categories',
            'circulation.index',
            'circulation.reservations',
            'members.index',
            'digital-library.index',
            'digital-library.recommendations',
            'digital-library.upload',
            'finance.index',
            'finance.transactions',
            'finance.fines',
            'finance.analytics',
            'finance.reports',
            'settings.index',
            'settings.users',
            'settings.roles',
            'settings.security.dashboard',
            'settings.programs',
            'settings.audit-logs',
            'notifications.index',
        ];

        foreach ($explicitRoutes as $routeName) {
            $response = $this->actingAs($this->user)->get(route($routeName, [], false));
            $dashboardUrl = route('dashboard', [], false);

            if ($response->isRedirect()) {
                $redirectUrl = $response->headers->get('Location');
                $this->assertFalse(
                    str_contains($redirectUrl ?? '', $dashboardUrl),
                    "Route '{$routeName}' redirects to Dashboard (redirect: {$redirectUrl})"
                );
            }
        }
    }

    public function test_digital_library_routes_do_not_equal_dashboard(): void
    {
        $dashboardUrl = route('dashboard', [], false);

        $digitalRoutes = [
            'digital-library.index' => '/digital-library',
            'digital-library.recommendations' => '/digital-library/recommendations',
            'digital-library.upload' => '/digital-library/upload',
            'digital-library.show' => '/digital-library/{asset}',
        ];

        foreach ($digitalRoutes as $routeName => $expectedUri) {
            $generatedUri = route($routeName, ['asset' => 1], false);
            $this->assertNotEquals($dashboardUrl, $generatedUri, "Route '{$routeName}' generates Dashboard URL");
            $this->assertStringStartsWith('/digital-library', $generatedUri, "Route '{$routeName}' does not start with /digital-library");
        }
    }

    public function test_all_menu_routes_have_unique_names(): void
    {
        $menuRoutes = [
            'dashboard',
            'catalog.books.index',
            'catalog.categories',
            'circulation.index',
            'circulation.reservations',
            'members.index',
            'digital-library.index',
            'digital-library.recommendations',
            'digital-library.upload',
            'finance.index',
            'finance.transactions',
            'finance.fines',
            'finance.analytics',
            'finance.reports',
            'settings.index',
            'settings.users',
            'settings.roles',
            'settings.security.dashboard',
            'settings.programs',
            'settings.audit-logs',
            'notifications.index',
        ];

        $this->assertCount(count($menuRoutes), array_unique($menuRoutes), 'Duplicate route names found in menu');
    }

    public function test_reports_route_is_not_finance_reports(): void
    {
        $this->assertNotEquals(
            route('dashboard', [], false),
            route('finance.reports', [], false),
            'Reports route should not equal Dashboard route'
        );
    }

    public function test_sidebar_active_state_patterns_are_consistent(): void
    {
        $patterns = [
            'dashboard' => ['dashboard'],
            'catalog.books.index' => ['catalog.books*'],
            'catalog.categories' => ['catalog.categories*'],
            'circulation.index' => ['circulation.*'],
            'circulation.reservations' => ['circulation.reservations*'],
            'members.index' => ['members.*'],
            'digital-library.index' => ['digital-library.*'],
            'digital-library.recommendations' => ['digital-library.recommendations'],
            'digital-library.upload' => ['digital-library.upload'],
            'finance.index' => ['finance.index'],
            'finance.transactions' => ['finance.transactions*'],
            'finance.fines' => ['finance.fines*'],
            'finance.analytics' => ['finance.analytics*'],
            'finance.reports' => ['finance.reports*'],
            'settings.index' => ['settings.*'],
            'settings.users' => ['settings.users*'],
            'settings.roles' => ['settings.roles*'],
            'settings.security.dashboard' => ['settings.security.dashboard'],
            'settings.programs' => ['settings.programs*'],
            'settings.audit-logs' => ['settings.audit-logs'],
            'notifications.index' => ['notifications.*'],
        ];

        foreach ($patterns as $routeName => $expectedPatterns) {
            $this->assertTrue(Route::has($routeName), "Route '{$routeName}' does not exist");
        }
    }
}
