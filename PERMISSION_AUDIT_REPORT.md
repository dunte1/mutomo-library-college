# Permission System Audit Report

**Date:** July 2, 2026
**Auditor:** MiMo Code Agent
**Scope:** Role-Based Permissions & Dynamic Navigation

---

## Executive Summary

The application uses **Spatie Laravel Permission v7.4+** with **103 permissions** across 17 groups and **10 roles**. The permission system is well-architected with route-level middleware, Blade `@can` directives, and Spatie's automatic Gate registration. The audit found and fixed several security gaps, primarily around hardcoded role checks, missing permission checks on dashboard buttons, and unprotected sidebar sections.

---

## Verified Components (Working Correctly)

### Route-Level Middleware
All module routes have proper `permission:xxx` middleware:
- **Catalog:** view-books, create-books, edit-books, view-authors, create-authors, edit-authors, view-publishers, create-publishers, edit-publishers, view-inventory, view-new-arrivals
- **Circulation:** view-circulation, borrow-books, return-books, renew-books, manage-reservations, manage-waitlists, override-due-dates
- **Members:** view-members, create-members, edit-members, suspend-members, manage-membership-requests, view-library-cards
- **Digital Library:** view-digital-assets, upload-digital-assets, download-digital-assets, view-recommendations, view-digital-categories
- **Finance:** view-financial-reports, view-transactions, manage-fines, generate-reports, view-analytics, collect-payments, generate-invoices, process-refunds, generate-receipts
- **Communication:** manage-announcements, manage-bulletins, manage-events, view-messages, send-messages, manage-templates, view-message-logs, manage-broadcasts, view-communication-analytics
- **Settings:** can:manage-settings, can:view-audit-logs, can:view-system-logs, can:manage-ai-settings, can:manage-maintenance, can:view-system-health, can:view-queue-monitor, can:manage-cache, can:view-storage
- **Subscriptions:** manage-subscriptions
- **Assignments:** create-assignments, view-assignments
- **Reports:** view-reports

### Sidebar Navigation
All sidebar items are wrapped in `@can` directives for individual permissions. The sidebar correctly shows/hides items based on user permissions.

### Blade Authorization
Views use `@can`, `@canany`, and `@cannot` directives for button visibility:
- book-list.blade.php: `@can('create-books')` around Add Book button
- book-show.blade.php: `@can('edit-books')` around Edit button
- member-list.blade.php: `@can('create-members')` around Add Member, `@can('edit-members')` around Edit
- member-show.blade.php: `@can('delete-members')` around Delete button
- library-card.blade.php: `@can('manage-library-cards')` around card management
- digital-category-list.blade.php: `@can('manage-digital-categories')`
- system-health.blade.php: `@can('manage-system-optimization')`
- storage-manager.blade.php: `@can('manage-storage')`
- message-show.blade.php: `@can('reply-messages')`, `@can('reply-all-messages')`, `@can('forward-messages')`

### API Authorization
All API routes use `auth:sanctum` middleware. Staff-facing routes have `permission:xxx` middleware. Personal data routes filter by `auth()->id()` for ownership.

### Permission Cache Clearing
Properly done in: RolesAndPermissionsSeeder, RoleList, RoleForm, UserForm, MemberService

### Role Management Protection
super-admin and admin roles cannot be deleted or renamed (enforced in RoleList.php and RoleForm.php)

---

## Fixed Components

### Phase 1: Critical Security

#### 1. Dashboard Hardcoded Role Check (FIXED)
**File:** `resources/views/dashboard.blade.php:15`
**Before:** `$isStaff = $user->hasAnyRole(['super-admin', 'admin', 'librarian', 'assistant-librarian', 'finance-officer'])`
**After:** `$isStaff = $user->hasAnyPermission([...])` - checks for write/manage permissions instead of role names
**Impact:** Users with custom roles now see the correct dashboard based on their permissions.

#### 2. Dashboard Quick Actions Without Permission Checks (FIXED)
**File:** `resources/views/dashboard.blade.php:169-192`
**Before:** "Add New Book", "Issue Book", "Return Book", "Generate Report" buttons visible to all staff
**After:** Each button wrapped in `@can('create-books')`, `@can('borrow-books')`, `@can('return-books')`, `@can('generate-reports')`

#### 3. Dashboard Mobile FAB Without Permission Checks (FIXED)
**File:** `resources/views/dashboard.blade.php:284-320`
**Before:** Mobile FAB buttons visible to all staff
**After:** Each FAB item wrapped in appropriate `@can` directive

#### 4. Dashboard Stat Cards Without Permission Checks (FIXED)
**File:** `resources/views/dashboard.blade.php:48-98`
**Before:** All stat cards visible to all staff regardless of permissions
**After:** "Total Books" wrapped in `@can('view-books')`, "Active Borrows"/"Overdue Books" in `@can('view-circulation')`, "Registered Members" in `@can('view-members')`

#### 5. Notification Route Missing Middleware (FIXED)
**File:** `app/Modules/Notifications/Routes/web.php:10`
**Before:** `/notifications` route had no permission middleware
**After:** Added `permission:view-notification-logs` middleware

### Phase 2: Sidebar & Dashboard

#### 6. Communication Section Header Visible to All Staff (FIXED)
**Files:** `sidebar.blade.php:454`, `nav-items.blade.php:359`
**Before:** "Communication & Engagement" group label always visible to staff
**After:** Wrapped in `@canany(['manage-announcements', 'manage-bulletins', 'manage-events', 'view-messages', 'send-notifications', 'view-notification-logs', 'manage-broadcasts', 'manage-templates', 'view-message-logs'])`

#### 7. Reports Sub-Items Missing Permission Checks (FIXED)
**Files:** `sidebar.blade.php:408-435`, `nav-items.blade.php:320-343`
**Before:** Catalog Reports, Circulation Reports, Member Reports, Digital Library Reports only checked `Route::has()` but not `@can`
**After:** Each wrapped in `@can('view-reports')`

### Phase 3: Livewire Component Authorization

#### 8. Write-Operation Livewire Components Without Internal Authorization (FIXED)
Added `abort_unless()` checks in `mount()` for 9 key components:
- **BookForm:** `create-books` (new) / `edit-books` (edit)
- **MemberForm:** `create-members` (new) / `edit-members` (edit)
- **RoleForm:** `manage-settings`
- **UserForm:** `manage-settings`
- **MessageForm:** `send-messages`
- **AnnouncementForm:** `manage-announcements`
- **TemplateForm:** `manage-templates`
- **PlanForm:** `manage-pricing`
- **TeacherAssignments:** `create-assignments`

### Phase 5: Mobile

#### 9. Mobile Permission Helper Naming Mismatch (FIXED)
**File:** `ollmchs_mobile/lib/core/helpers/permission_helper.dart`
**Before:** Used snake_case permission names (access_dashboard, borrow_books, etc.) that didn't match server
**After:** Updated to use server-side kebab-case names (view-dashboard, view-books, etc.) with all 103 permissions and 10 roles

---

## Remaining Items (Low Priority / Informational)

### No Policy Classes
The application has no Policy classes. All authorization is handled by Spatie's permission system via route middleware and Blade directives. This is acceptable for the current architecture but could be enhanced with Policies for more granular ownership checks in the future.

### Hardcoded Role Methods (Acceptable Usage)
The following hardcoded role methods are used for business logic, not authorization:
- `isStudent()` / `isLecturer()` in BorrowingService - determines borrow duration (14/30 days)
- `isSuperAdmin()` in CheckSubscriptionStatus/SubscriptionEnforcementService - subscription exemption
- `isSuperAdmin()` in UserList - prevents non-super-admin from deactivating super-admin

These are appropriate uses of role checks for business rules that are inherently tied to role type.

### Dashboard `@role` Layout Selection
The sidebar uses `@role('super-admin|admin|librarian|...')` to determine which sidebar layout renders (staff vs patron). This is a layout concern, not an authorization concern - the `@can` directives inside handle all authorization. Users with custom roles not in the list would see no sidebar, which is a design limitation but not a security issue.

### API Routes for Personal Data
Several API routes for personal data (notifications, announcements, events, assignments, subscriptions) don't have permission middleware because all authenticated users need access to their own data. The controllers enforce ownership via `auth()->id()` filters.

---

## Permission Audit Summary

| Category | Permissions | Used In Routes | Used In Sidebar | Used In Views | Status |
|----------|-------------|----------------|-----------------|---------------|--------|
| Dashboard | 2 | ✓ | ✓ | ✓ | ✅ |
| Catalog | 21 | ✓ | ✓ | ✓ | ✅ |
| Circulation | 8 | ✓ | ✓ | ✓ | ✅ |
| Members | 13 | ✓ | ✓ | ✓ | ✅ |
| Digital Library | 8 | ✓ | ✓ | ✓ | ✅ |
| Finance | 8 | ✓ | ✓ | ✓ | ✅ |
| Notifications | 3 | ✓ | ✓ | ✓ | ✅ |
| Reports | 4 | ✓ | ✓ | ✓ | ✅ |
| Settings | 8 | ✓ | ✓ | ✓ | ✅ |
| Communication | 12 | ✓ | ✓ | ✓ | ✅ |
| Library Cards | 2 | ✓ | ✓ | ✓ | ✅ |
| System Health | 2 | ✓ | ✓ | ✓ | ✅ |
| System | 4 | ✓ | ✓ | ✓ | ✅ |
| AI Features | 2 | ✓ | ✓ | ✓ | ✅ |
| Subscriptions | 4 | ✓ | ✓ | ✓ | ✅ |
| Assignments | 3 | ✓ | ✓ | ✓ | ✅ |

**Total: 103 permissions across 17 groups - All verified as used.**

---

## Files Modified

1. `resources/views/dashboard.blade.php` - Fixed role check, added @can to buttons/stat cards/FAB
2. `resources/views/livewire/layout/sidebar.blade.php` - Added @canany to Communication header, @can to Reports sub-items
3. `resources/views/components/layout/nav-items.blade.php` - Same sidebar fixes
4. `app/Modules/Notifications/Routes/web.php` - Added permission middleware
5. `app/Modules/Catalog/Livewire/BookForm.php` - Added authorization in mount()
6. `app/Modules/Members/Livewire/MemberForm.php` - Added authorization in mount()
7. `app/Modules/Settings/Livewire/RoleForm.php` - Added authorization in mount()
8. `app/Modules/Settings/Livewire/UserForm.php` - Added authorization in mount()
9. `app/Modules/Communication/Livewire/MessageForm.php` - Added authorization in mount()
10. `app/Modules/Communication/Livewire/AnnouncementForm.php` - Added authorization in mount()
11. `app/Modules/Communication/Livewire/TemplateForm.php` - Added authorization in mount()
12. `app/Modules/Subscriptions/Livewire/Admin/PlanForm.php` - Added authorization in mount()
13. `app/Modules/Assignments/Livewire/TeacherAssignments.php` - Added authorization in mount()
14. `ollmchs_mobile/lib/core/helpers/permission_helper.dart` - Updated to match server permissions

---

## Recommendations

1. **Consider adding Policy classes** for more granular ownership checks, especially for messages, notifications, and assignments
2. **Add a `delete-messages` permission** - currently deletion is checked via ownership or `manage-broadcasts`
3. **Consider replacing `@role` layout selection** with a permission-based helper that checks for any write/manage permission
4. **Add integration tests** for permission changes - test that removing a permission immediately hides the corresponding UI elements
5. **Consider adding a permission audit command** that checks for unused permissions in the database vs codebase
