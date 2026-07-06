# Mobile App Implementation Plan

## Summary
The mobile app already has a solid foundation for member-facing library workflows, including authentication, dashboard, catalog browsing, loans, reservations, fines, library card, digital library, messaging, notifications, and profile/settings. The biggest gaps are in areas that exist in the backend but do not yet have dedicated mobile screens or navigation flows.

## Current Status by Feature

### Fully implemented / usable
These flows are present as screens and wired into the router and app shell:
- Authentication: splash, onboarding, login, register, forgot password, two-factor, two-factor setup
- Dashboard
- Book catalog: list, detail, search
- Loans: active loans, loan history
- Reservations
- Fines
- Library card
- Digital library: asset list and reader
- Messaging: inbox and message detail
- Notifications
- Profile and settings

### Partially implemented / needs refinement
These exist but still need product-level refinement or deeper integration:
- Settings: strong UI, but some flows are more basic than the backend capabilities suggest
- Profile: basic profile display and password change, but avatar upload and richer preferences are not fully surfaced
- Digital library: asset list and reader exist, but reading history, recommendations, and category filtering are mostly backend-ready but not fully surfaced in the UI
- Messaging: inbox/detail screens exist, but compose/reply/delete workflows and better state handling are not fully implemented
- Book detail: reserve action exists, but a richer loan/reservation workflow is not fully integrated

### Missing or not yet implemented
These are not present as mobile screens or navigation flows, even though the backend exposes them:
- Assignments module screens
- Subscription plans / subscription management screens
- Announcements / bulletin / event screens
- Reports and analytics screens
- Payment / receipt / invoice screens
- Admin-style management screens for fines, circulation, and library operations
- Search/filter by category/author/publisher more deeply across the app
- Advanced reading experience for digital assets (in-app viewer, progress tracking, offline support)
- Push notification settings and deep-link handling beyond basic UI

## Recommended Implementation Phases

### Phase 1 — Core member experience polish
Focus on the features that already exist but need stronger completion.
1. Improve profile management
   - Avatar upload
   - Editable profile fields
   - Notification preferences
2. Expand digital library experience
   - Reading history screen
   - Recommendations section
   - Category chips and filtering
   - Better in-app reader experience
3. Improve messaging flow
   - Compose message screen
   - Reply flow
   - Mark read / delete actions
4. Strengthen reservation and loan UX
   - Better success/error feedback
   - Renewal history and due date reminders

### Phase 2 — Missing screens from backend modules
Add screens for modules already exposed by the backend.
1. Assignments screens
   - Student assignments list
   - Assignment detail
   - Submission state and due dates
2. Subscriptions screens
   - Plans list
   - Current subscription overview
   - Checkout flow
3. Communication screens
   - Announcements list/detail
   - Events list/detail
   - Bulletins list/detail
4. Finance screens
   - Payment history
   - Receipts / invoices
   - Fine payment flow
5. Reports screens
   - Reading reports
   - Loan reports
   - Fines reports

### Phase 3 — Advanced mobile experience
Increase completeness and quality for production readiness.
1. Offline support
   - Cached books and assets
   - Offline reading fallback
2. Deep linking and notifications
   - Open notifications to the relevant screen
   - Handle push content routing
3. Accessibility and performance
   - Better empty/error/loading states
   - Larger touch targets and screen-reader labels
4. Testing and QA
   - Widget tests for key flows
   - Integration tests for auth, loans, reservations, and profile

## Suggested Screen Priority

### High priority
- Assignment screens
- Subscription screens
- Announcement/event screens
- Reading history and recommendations
- Compose/reply messaging screens
- Receipt/invoice/payment screens

### Medium priority
- Reports and analytics screens
- Advanced digital library reader
- Richer profile settings
- Subscription checkout flow

### Lower priority
- Admin-style management screens
- Extended reporting dashboards
- Advanced offline experiences

## Recommended File/Feature Structure
Add the following feature folders under the Flutter app:
- features/assignments
- features/subscriptions
- features/communication
- features/finance
- features/reports

## Implementation Order
1. Create missing feature folders and screen skeletons
2. Add repository and bloc layers for each feature
3. Wire routes in the router
4. Add navigation entries from dashboard/profile/settings
5. Connect to backend API endpoints
6. Add loading/error/empty states and tests

## Immediate Next Step
The best next step is to implement the first missing feature set in this order:
1. Assignments screens
2. Subscriptions screens
3. Communication screens

That sequence gives the app the most visible value while staying aligned with the backend modules that already exist.
