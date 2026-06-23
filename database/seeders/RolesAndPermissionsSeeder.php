<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionGroups = [
            'Dashboard' => [
                'view-dashboard',
                'view-analytics',
            ],
            'Catalog' => [
                'view-books',
                'create-books',
                'edit-books',
                'delete-books',
                'view-categories',
                'create-categories',
                'edit-categories',
                'delete-categories',
                'view-authors',
                'create-authors',
                'edit-authors',
                'delete-authors',
                'view-publishers',
                'create-publishers',
                'edit-publishers',
                'delete-publishers',
                'import-books',
                'export-books',
                'view-inventory',
                'manage-inventory',
                'view-new-arrivals',
            ],
            'Circulation' => [
                'borrow-books',
                'return-books',
                'renew-books',
                'view-borrows',
                'manage-reservations',
                'manage-waitlists',
                'view-circulation',
                'override-due-dates',
            ],
            'Members' => [
                'view-members',
                'create-members',
                'edit-members',
                'delete-members',
                'suspend-members',
                'reinstate-members',
                'manage-membership-requests',
                'view-departments',
                'manage-departments',
                'view-programs',
                'manage-programs',
                'clear-members',
            ],
            'Digital Library' => [
                'view-digital-assets',
                'upload-digital-assets',
                'edit-digital-assets',
                'delete-digital-assets',
                'download-digital-assets',
                'manage-access-levels',
                'view-digital-categories',
                'manage-digital-categories',
            ],
            'Finance' => [
                'view-fines',
                'manage-fines',
                'collect-payments',
                'view-transactions',
                'generate-invoices',
                'generate-receipts',
                'process-refunds',
                'view-financial-reports',
            ],
            'Notifications' => [
                'send-notifications',
                'manage-templates',
                'view-notification-logs',
            ],
            'Reports' => [
                'view-reports',
                'generate-reports',
                'export-reports',
                'schedule-reports',
            ],
            'Settings' => [
                'manage-settings',
                'manage-roles',
                'manage-permissions',
                'view-audit-logs',
                'clear-audit-logs',
                'manage-backups',
                'view-system-logs',
                'manage-maintenance',
            ],
            'Communication' => [
                'manage-announcements',
                'manage-events',
                'manage-bulletins',
                'send-messages',
                'view-messages',
                'manage-broadcasts',
                'view-message-logs',
                'view-communication-analytics',
            ],
            'Library Cards' => [
                'view-library-cards',
                'manage-library-cards',
            ],
            'System Health' => [
                'view-system-health',
                'manage-system-optimization',
            ],
            'System' => [
                'view-queue-monitor',
                'manage-cache',
                'view-storage',
                'manage-storage',
            ],
            'AI Features' => [
                'view-recommendations',
                'manage-ai-settings',
            ],
            'Subscriptions' => [
                'manage-subscriptions',
                'view-subscriptions',
                'manage-pricing',
                'process-subscription-payments',
            ],
            'Assignments' => [
                'create-assignments',
                'view-assignments',
                'complete-assignments',
            ],
        ];

        $allPermissions = [];
        foreach ($permissionGroups as $group => $permissions) {
            foreach ($permissions as $permission) {
                $allPermissions[] = Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web',
                ]);
            }
        }

        $roles = [
            'super-admin' => $allPermissions,
            'admin' => $allPermissions,
            'librarian' => Permission::whereIn('name', [
                'view-dashboard', 'view-analytics',
                'view-books', 'create-books', 'edit-books', 'import-books', 'export-books',
                'view-inventory', 'manage-inventory', 'view-new-arrivals',
                'view-categories', 'create-categories', 'edit-categories',
                'view-authors', 'create-authors', 'edit-authors',
                'view-publishers', 'create-publishers', 'edit-publishers',
                'borrow-books', 'return-books', 'renew-books', 'view-borrows',
                'manage-reservations', 'manage-waitlists', 'view-circulation',
                'view-members', 'create-members', 'edit-members', 'suspend-members',
                'reinstate-members', 'manage-membership-requests',
                'view-departments', 'view-programs',
                'view-digital-assets', 'upload-digital-assets',
                'edit-digital-assets', 'download-digital-assets',
                'view-digital-categories', 'manage-digital-categories',
                'view-fines', 'manage-fines', 'collect-payments',
                'view-transactions', 'generate-invoices', 'generate-receipts',
                'view-financial-reports',
                'send-notifications',
                'view-reports', 'generate-reports', 'export-reports',
                'manage-announcements', 'manage-events',
                'send-messages', 'view-messages', 'manage-broadcasts',
                'view-message-logs',
                'view-communication-analytics',
                'view-library-cards', 'manage-library-cards',
                'view-system-health', 'manage-system-optimization',
                'view-queue-monitor', 'manage-cache', 'view-storage',
                'view-recommendations',
            ])->get(),
            'assistant-librarian' => Permission::whereIn('name', [
                'view-dashboard',
                'view-books', 'create-books', 'edit-books',
                'view-categories', 'view-authors', 'view-publishers',
                'view-inventory', 'view-new-arrivals',
                'borrow-books', 'return-books', 'view-borrows',
                'manage-reservations',
                'view-members', 'create-members', 'edit-members',
                'view-digital-assets', 'upload-digital-assets', 'download-digital-assets',
                'view-digital-categories',
                'view-fines', 'collect-payments',
                'view-reports',
                'view-library-cards',
            ])->get(),
            'student' => Permission::whereIn('name', [
                'view-dashboard',
                'view-books',
                'view-digital-assets', 'download-digital-assets',
                'view-recommendations',
                'view-library-cards',
                'view-assignments', 'complete-assignments',
            ])->get(),
            'lecturer' => Permission::whereIn('name', [
                'view-dashboard',
                'view-books',
                'view-digital-assets', 'download-digital-assets',
                'view-recommendations',
                'view-library-cards',
                'create-assignments', 'view-assignments',
            ])->get(),
            'finance-officer' => Permission::whereIn('name', [
                'view-dashboard', 'view-analytics',
                'view-fines', 'manage-fines', 'collect-payments',
                'view-transactions', 'generate-invoices', 'generate-receipts',
                'process-refunds', 'view-financial-reports',
                'view-reports', 'generate-reports', 'export-reports',
                'view-members',
                'manage-subscriptions', 'view-subscriptions', 'manage-pricing',
            ])->get(),
            'department-head' => Permission::whereIn('name', [
                'view-dashboard', 'view-analytics',
                'view-books',
                'view-borrows',
                'view-members',
                'view-reports', 'generate-reports', 'export-reports',
                'view-recommendations',
            ])->get(),
            'ict-admin' => Permission::whereIn('name', [
                'view-dashboard', 'manage-settings',
                'view-audit-logs',
                'clear-audit-logs',
                'manage-backups',
                'view-system-logs',
                'manage-maintenance',
                'view-system-health',
                'manage-system-optimization',
                'view-queue-monitor', 'manage-cache', 'view-storage', 'manage-storage',
                'view-reports',
            ])->get(),
            'staff' => Permission::whereIn('name', [
                'view-dashboard',
                'view-books',
                'view-digital-assets', 'download-digital-assets',
                'view-recommendations',
                'view-library-cards',
                'view-assignments',
            ])->get(),
            'guest' => Permission::whereIn('name', [
                'view-books',
            ])->get(),
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
            $role->syncPermissions($permissions);
        }
    }
}
