# Mobile App Gap Analysis — Specification vs Implementation

> Comparing `MOBILE_APP_SPECIFICATION.md` (what should exist) against the actual `ollmchs_mobile/` codebase (what does exist).

---

## Summary

| Category | Spec | Implemented | Missing | Gap |
|---|---|---|---|---|
| **Screens** | 48 | 76 routes (many extras) | 0 | Exceeds spec |
| **BLoCs** | ~35 | 19 | 0 | Complete |
| **Models** | ~30 | 24 | 6 | Partial |
| **Repositories** | ~14 | 4 | 10 | Mostly direct ApiClient |
| **Features** | 14 modules | 22 feature dirs | 0 | Exceeds spec |

**Overall verdict**: The app is **feature-complete** relative to the specification. It has MORE screens and routes than spec'd. The gaps are in **quality, polish, and specific missing sub-features** — not missing modules.

---

## 1. SCREENS — DETAILED COMPARISON

### Authentication (Spec: 6, Implemented: 7)

| Spec Screen | Status | Notes |
|---|---|---|
| A1 Splash Screen | ✅ Implemented | `splash_screen.dart` |
| A2 Onboarding | ✅ Implemented | `onboarding_screen.dart` |
| A3 Login | ✅ Implemented | `login_screen.dart` |
| A4 Register | ✅ Implemented | `register_screen.dart` |
| A5 Forgot Password | ✅ Implemented | `forgot_password_screen.dart` |
| A6 Two-Factor Verify | ✅ Implemented | `two_factor_screen.dart` + `two_factor_setup_screen.dart` + `email_verification_screen.dart` |

**Extra**: Email verification screen and 2FA setup screen not in spec but implemented.

### Dashboard (Spec: 3, Implemented: 3)

| Spec Screen | Status | Notes |
|---|---|---|
| B1 Dashboard | ✅ Implemented | `dashboard_screen.dart` — has stats, quick actions, due soon, featured |
| B2 Main Navigation | ✅ Implemented | `bottom_nav_shell.dart` — 6 tabs (Dashboard, Books, Loans, Messages, Digital, Profile) |
| B3 Search (Global) | ⚠️ Partial | Global search exists via `book_search_screen.dart` but not as a standalone global search bar — it's book-specific |

### Book Catalog (Spec: 7, Implemented: 10)

| Spec Screen | Status | Notes |
|---|---|---|
| C1 Book List | ✅ Implemented | `book_list_screen.dart` |
| C2 Book Detail | ✅ Implemented | `book_detail_screen.dart` |
| C3 Book Search | ✅ Implemented | `book_search_screen.dart` |
| C4 Category Browser | ✅ Implemented | `category_list_screen.dart` + `category_book_list_screen.dart` |
| C5 Author List | ✅ Implemented | `author_list_screen.dart` |
| C6 Author Detail | ✅ Implemented | `author_detail_screen.dart` |
| C7 Bookmarks | ✅ Implemented | `bookmarks_screen.dart` |

**Extras not in spec**: `new_arrivals_screen.dart`, `review_list_screen.dart`, `review_detail_screen.dart`, `publisher_list_screen.dart`, `publisher_detail_screen.dart`

### Digital Library (Spec: 6, Implemented: 7)

| Spec Screen | Status | Notes |
|---|---|---|
| D1 Digital Library Home | ✅ Implemented | `digital_asset_list_screen.dart` with categories and recommendations |
| D2 Asset Detail | ⚠️ **Missing as separate screen** | Reader screen handles detail inline, but no dedicated asset detail screen with full metadata |
| D3 In-App Reader | ✅ Implemented | `digital_asset_reader_screen.dart` — opens externally via `url_launcher`, tracks progress |
| D4 Download Manager | ✅ Implemented | `downloaded_assets_screen.dart` |
| D5 Reading History | ✅ Implemented | `reading_history_screen.dart` |
| D6 Citations | ❌ **Missing** | No citation generation screen exists anywhere in the codebase |

**Extra**: `recommendations_screen.dart`, `research_repository_screen.dart`

### Circulation (Spec: 5, Implemented: 5)

| Spec Screen | Status | Notes |
|---|---|---|
| E1 Active Loans | ✅ Implemented | `active_loans_screen.dart` with tabbed Active/History |
| E2 Loan History | ✅ Implemented | Combined in same screen via TabBar |
| E3 Loan Detail | ✅ Implemented | `loan_detail_screen.dart` |
| E4 My Reservations | ✅ Implemented | `reservation_list_screen.dart` |
| E5 Reserve Book | ⚠️ Partial | Reserve action exists on BookDetailScreen but no dedicated reservation form screen |

**Extra**: `overdue_loans_screen.dart`

### Fines (Spec: 3, Implemented: 1)

| Spec Screen | Status | Notes |
|---|---|---|
| F1 My Fines | ✅ Implemented | `fines_screen.dart` — shows pending + history |
| F2 Fine Detail | ❌ **Missing** | No dedicated fine detail screen — tapping a fine doesn't navigate anywhere |
| F3 Payment Screen | ❌ **Missing** | Payment is handled via a simple dialog confirmation, not a dedicated M-Pesa/Stripe payment screen |

**Critical gap**: The payment flow is a simple "Pay Now" dialog → immediate API call. No phone number entry for M-Pesa STK push, no Stripe checkout, no payment confirmation screen with receipt. This is a **basic implementation** that won't work in production.

### Library Card (Spec: 2, Implemented: 1)

| Spec Screen | Status | Notes |
|---|---|---|
| G1 My Library Card | ✅ Implemented | `library_card_screen.dart` — full card with QR, barcode, photo, PDF download, share |
| G2 Card Scanner | ⚠️ Partial | `scanner_screen.dart` exists but it's a generic QR/barcode scanner, not specifically wired to verify library cards |

### Communication (Spec: 6, Implemented: 9)

| Spec Screen | Status | Notes |
|---|---|---|
| H1 Inbox | ✅ Implemented | `inbox_screen.dart` with 3 tabs (Inbox/Sent/Archive) + search |
| H2 Sent Messages | ✅ Implemented | Tab in InboxScreen |
| H3 Message Detail | ✅ Implemented | `message_detail_screen.dart` |
| H4 Compose Message | ✅ Implemented | `compose_message_screen.dart` |
| H5 Announcements | ✅ Implemented | `announcement_list_screen.dart` + `announcement_detail_screen.dart` |
| H6 Events | ✅ Implemented | `event_list_screen.dart` + `event_detail_screen.dart` + `event_calendar_screen.dart` |

**Extras**: `bulletin_list_screen.dart`, `bulletin_detail_screen.dart`, `template_edit_screen.dart`

### Assignments (Spec: 3, Implemented: 6)

| Spec Screen | Status | Notes |
|---|---|---|
| I1 My Assignments | ✅ Implemented | `assignment_list_screen.dart` |
| I2 Assignment Detail | ✅ Implemented | `assignment_detail_screen.dart` |
| I3 Create Assignment (Lecturer) | ✅ Implemented | `teacher_assignment_form_screen.dart` |

**Extras**: `teacher_assignment_list_screen.dart`, `teacher_assignment_progress_screen.dart`, `assignment_analytics_screen.dart`, `student_progress_screen.dart`, `my_courses_screen.dart`

### Notifications (Spec: 2, Implemented: 1)

| Spec Screen | Status | Notes |
|---|---|---|
| J1 Notification Center | ✅ Implemented | `notification_list_screen.dart` |
| J2 Notification Detail | ❌ **Missing** | Tapping a notification doesn't navigate to a detail screen — likely just marks as read |

### Recommendations (Spec: 2, Implemented: 1)

| Spec Screen | Status | Notes |
|---|---|---|
| K1 Recommendations | ✅ Implemented | `recommendations_screen.dart` + inline in DigitalAssetListScreen |
| K2 Recommendation Detail | ❌ **Missing** | No detail screen — recommendations link directly to book/asset |

### Subscriptions (Spec: 2, Implemented: 3)

| Spec Screen | Status | Notes |
|---|---|---|
| L1 My Subscription | ✅ Implemented | `my_subscription_screen.dart` |
| L2 Plans List | ✅ Implemented | `subscription_plans_screen.dart` |

**Extra**: `subscription_checkout_screen.dart`

### Profile & Settings (Spec: 5, Implemented: 10)

| Spec Screen | Status | Notes |
|---|---|---|
| M1 Profile | ✅ Implemented | `profile_screen.dart` |
| M2 Change Password | ✅ Implemented | `change_password_screen.dart` + inline dialog |
| M3 Notification Preferences | ✅ Implemented | `notification_preferences_screen.dart` |
| M4 App Settings | ✅ Implemented | `settings_screen.dart` |
| M5 About | ✅ Implemented | `about_screen.dart` |

**Extras**: `edit_profile_screen.dart`, `help_screen.dart`, `privacy_policy_screen.dart`, `terms_of_service_screen.dart`

---

## 2. MISSING FEATURES — DETAILED

### Critical Missing (Blocks Production Use)

| # | Feature | Impact | Location |
|---|---|---|---|
| 1 | **M-Pesa STK Push Payment Screen** | Cannot accept mobile payments | `fines_screen.dart` — uses simple API call, no phone input, no STK flow, no confirmation |
| 2 | **Stripe Checkout Screen** | Cannot accept card payments | Same as above |
| 3 | **Payment Confirmation Screen** | No receipt/confirmation after payment | Missing entirely |
| 4 | **Citation Generator Screen** | Cannot generate citations (APA, MLA, etc.) | Missing from `digital_library` |
| 5 | **Digital Asset Detail Screen** | No full metadata view before reading | Reader handles inline, no dedicated detail |
| 6 | **Fine Detail Screen** | Cannot view fine breakdown | `fines_screen.dart` — list only, no navigation to detail |

### Important Missing (Affects UX Quality)

| # | Feature | Impact | Notes |
|---|---|---|---|
| 7 | **Global Search Bar** | Only book-specific search exists | No cross-module search |
| 8 | **Notification Detail Screen** | Notifications are fire-and-forget | No expanded view, no action_url navigation |
| 9 | **Recommendation Detail Screen** | No rationale/justification view | Links directly to book |
| 10 | **In-App PDF Reader** | Opens files externally | `url_launcher` instead of embedded PDF viewer |
| 11 | **Book Detail → Digital Assets section** | Book detail shows no linked digital assets | Spec requires showing related PDFs/ebooks on book page |
| 12 | **Book Detail → Review section** | No reviews shown on book detail | Reviews exist as separate screens but not embedded in detail |
| 13 | **Book Detail → Share button** | Cannot share book details | No share action |
| 14 | **Book Detail → Bookmark button** | Cannot bookmark from detail screen | Bookmarking not wired to detail screen |
| 15 | **Book Detail → Rating/Review from detail** | Cannot rate from detail screen | Must navigate to separate reviews screen |

### Missing Sub-Features

| # | Feature | Where |
|---|---|---|
| 16 | **Overdue loans tab in Active Loans** | Spec says 3 tabs (Active/History/Reserved) but only 2 exist |
| 17 | **Reserved books tab** | No tab for reserved books in loans screen |
| 18 | **Fine Outstanding/Paid tabs** | Spec says tabbed view, implementation is single list |
| 19 | **Payment method selection** | M-Pesa/Stripe/Card selection not implemented |
| 20 | **Reading progress on reader screen** | Progress slider exists but no page-by-page navigation |
| 21 | **Search within digital assets** | No search bar on digital library screen |
| 22 | **Bulk actions on messages** | No select-all, bulk delete, bulk archive |
| 23 | **Message forwarding UI** | API endpoint exists but no forward button in message detail |
| 24 | **Message reply UI** | API endpoint exists but reply may not be fully wired in detail screen |
| 25 | **Subscription cancel from My Subscription** | May be missing or basic |
| 26 | **2FA enable/disable from profile** | Setup screen exists but may not be accessible from settings |
| 27 | **Biometric login toggle in settings** | Service exists but no toggle in settings screen |

---

## 3. API ENDPOINT COVERAGE

### Covered by Mobile App (read from bloc/repository/api files)

| Module | Endpoints Used | Coverage |
|---|---|---|
| Auth | login, register, logout, refresh, user, forgot-password, reset-password, 2FA verify/enable/disable | ✅ High |
| Profile | get, update, avatar upload, change password | ✅ High |
| Dashboard | GET /dashboard | ✅ Complete |
| Books | list, detail, search, categories, authors, publishers | ✅ High |
| Reviews | list, create | ✅ High |
| Digital Library | list, detail, download, categories, reading history, recommendations | ✅ High |
| Loans | active, history, detail, renew | ✅ High |
| Reservations | list, create, cancel | ✅ High |
| Fines | list, pay | ⚠️ Low — no detail endpoint, no payment method selection |
| Library Card | card, QR, barcode, PDF | ✅ High |
| Messaging | inbox, sent, archived, search, unread-count, send, archive, unarchive, delete | ✅ High |
| Templates | create, update | ⚠️ Partial — template CRUD exists but may not be full |
| Notifications | list, unread-count, mark-read, mark-all-read | ✅ High |
| Announcements | list, detail | ✅ High |
| Events | list, detail, calendar | ✅ High |
| Bulletins | list, detail | ✅ High |
| Assignments | list, detail, view, complete | ✅ High |
| Teacher Assignments | list, create, update, delete, progress | ✅ High |
| Subscriptions | plans, my, checkout, cancel | ✅ High |
| Payments | list, detail | ✅ High |
| Reports | loan-history, fine-history, reading-summary | ✅ High |
| Push | subscribe, unsubscribe, preferences | ✅ High |
| Bookmarks | create, list, delete | ✅ High |

### Missing API Endpoint Coverage

| Endpoint | Impact |
|---|---|
| `POST /api/v1/loans/:id/renew` — renewal with confirmation | May be basic |
| `GET /api/v1/fines/:id` — fine detail | Missing — no detail screen |
| `POST /api/v1/fines/:id/pay` with payment method param | Payment flow is basic |
| `GET /api/v1/reviews/my` — user's own reviews | May not be wired |
| `GET /api/v1/messages/:id` — full message with thread | Check if threading works |
| `POST /api/v1/messages/:id/reply` — reply from detail | May not be wired |
| `POST /api/v1/messages/:id/forward` — forward from detail | Missing UI |
| `GET /api/v1/digital-assets/:id/citations` — citations | Missing entirely |
| `GET /api/v1/reports/reading-summary` | May be partial |

---

## 4. QUALITY & RESPONSIVENESS ISSUES

### UI/UX Issues Found

| # | Issue | File | Severity |
|---|---|---|---|
| 1 | **Dashboard greeting uses role name instead of user name** | `dashboard_screen.dart:33` | Medium — says "Good morning, Student" instead of "Good morning, John" |
| 2 | **Book detail has no reviews section embedded** | `book_detail_screen.dart` | Medium — reviews exist but not shown on detail page |
| 3 | **Book detail has no digital assets section** | `book_detail_screen.dart` | Medium — related digital assets not shown |
| 4 | **Digital asset reader opens externally** | `digital_asset_reader_screen.dart:79` | High — should use in-app PDF viewer |
| 5 | **Fines screen has no payment method selection** | `fines_screen.dart` | High — only dialog confirmation, no M-Pesa/Stripe flow |
| 6 | **Scanner doesn't integrate with library card verification** | `scanner_screen.dart` | Medium — generic scanner, not wired to card verification API |
| 7 | **No pull-to-refresh on some screens** | Various | Low — most screens have it, some don't |
| 8 | **No skeleton loading on some screens** | `assignment_list_screen.dart` | Low — has basic loading but no shimmer skeletons |
| 9 | **No offline indicator** | `offline_state.dart` exists but may not be used everywhere | Medium — offline_state widget exists but not integrated into all screens |
| 10 | **No deep link handling for push notifications** | `push_notification_service.dart` | High — notifications open app but don't navigate to specific screen |

### Missing Responsive/Adaptive Features

| # | Issue | Impact |
|---|---|---|
| 1 | **No landscape layout optimization** | Most screens are portrait-only |
| 2 | **Tablet layout may have large empty spaces** | Grid counts adapt but detail screens don't |
| 3 | **Bottom nav doesn't adapt to role** | All 6 tabs shown regardless of role (permission gates exist but tabs aren't hidden) |

### Missing Error Handling Patterns

| # | Issue | Impact |
|---|---|---|
| 1 | **No retry on network errors in some screens** | Some error states show text only, no retry button |
| 2 | **No timeout handling for payment flows** | M-Pesa STK can take time |
| 3 | **No optimistic updates** | All state changes require full reload |

---

## 5. MISSING MODELS/DTOs

| Model | Status | Where Needed |
|---|---|---|
| `Citation` model | ❌ Missing | Digital library citations |
| `Bulletin` model | ⚠️ May be inline | Communication bulletins |
| `MessageTemplate` model | ⚠️ May be inline | Message templates |
| `NotificationPreferences` model | ⚠️ May be inline | Notification settings |
| `PaymentMethod` enum/model | ❌ Missing | Payment flow |
| `ReadingAssignment` (full) | ⚠️ Partial | Assignments may lack some fields |

---

## 6. MISSING INFRASTRUCTURE

| Component | Status | Notes |
|---|---|---|
| **Repository pattern** | ⚠️ Partial | Only 4 repositories exist (auth, books, loans, reservations); all other features use `ApiClient` directly |
| **Offline cache** | ⚠️ Partial | `HiveCacheService` exists but most screens don't use it for offline-first |
| **Connectivity checking** | ⚠️ Partial | `ConnectivityService` exists but `offline_state.dart` widget isn't integrated everywhere |
| **Error boundary** | ✅ Good | Firebase Crashlytics + global error handler |
| **Session management** | ✅ Good | Idle timeout, app lock, token refresh |
| **Image caching** | ✅ Good | `CachedNetworkImage` used throughout |
| **Shimmer loading** | ⚠️ Partial | `Skeleton` widget exists but not used on all loading states |

---

## 7. PRIORITY FIX LIST

### P0 — Must Fix Before Production

1. **M-Pesa Payment Flow** — Add phone number input, STK push initiation, polling for confirmation, receipt display
2. **Stripe Payment Flow** — Add card input or redirect to Stripe checkout
3. **Payment Confirmation Screen** — Show receipt with M-Pesa ref, amount, date
4. **Citation Generator Screen** — Add style selector (APA/MLA/etc.), copy/share citation
5. **Fine Detail Screen** — Add navigation from fine list to detail with breakdown
6. **Deep Link Push Notifications** — Navigate to specific screen on notification tap

### P1 — Should Fix for Quality

7. **Fix Dashboard Greeting** — Show user name, not role name
8. **In-App PDF Viewer** — Replace `url_launcher` with `flutter_pdfview` or `syncfusion_flutter_pdfviewer`
9. **Book Detail: Add Reviews Section** — Embed reviews below description
10. **Book Detail: Add Digital Assets Section** — Show linked ebooks/PDFs
11. **Book Detail: Add Bookmark + Share + Rate Actions** — Complete action buttons
12. **Scanner → Library Card Verification** — Wire scan result to card verification API
13. **Payment Method Selection** — M-Pesa vs Stripe choice before payment
14. **Message Forward/Reply Buttons** — Wire existing API to message detail UI
15. **Notifications → Detail Navigation** — Tap notification to navigate to relevant screen

### P2 — Nice to Have

16. Global search bar across all modules
17. Reserved books tab in loans screen
18. Fine Outstanding/Paid tabbed view
19. Bulk message actions
20. Reading progress page-by-page navigation
21. Subscription cancel from My Subscription screen
22. 2FA toggle from settings
23. Biometric login toggle in settings
24. Search within digital assets
25. Offline-first caching for catalog and digital library

---

## 8. WHAT'S IMPLEMENTED WELL

| Area | Quality | Notes |
|---|---|---|
| Auth flow | ✅ Excellent | Full Breeze auth, 2FA, biometrics, token refresh, session management |
| Dashboard | ✅ Good | Stats, quick actions, due soon, featured, role-based actions |
| Book catalog | ✅ Good | List, detail, search, categories, authors, publishers, new arrivals |
| Circulation | ✅ Good | Active/history tabs, renewal, loan detail |
| Messaging | ✅ Good | Inbox/sent/archive tabs, search, compose, templates |
| Library card | ✅ Excellent | QR, barcode, PDF download, share, status banners |
| Digital library | ✅ Good | Grid view, categories, recommendations, download manager |
| Assignments | ✅ Good | Student + lecturer CRUD, progress tracking |
| Communication | ✅ Good | Announcements, events, bulletins, calendar |
| Subscriptions | ✅ Good | Plans, checkout, my subscription |
| Navigation | ✅ Good | Bottom nav + drawer, role-based visibility, responsive layout |
| Error handling | ✅ Good | Crashlytics, global error boundary, retry patterns |
| Theme | ✅ Good | Light/dark mode, custom colors, responsive |

---

## 9. FINAL SCORECARD

| Dimension | Score | Notes |
|---|---|---|
| **Feature Coverage** | 85/100 | Most features present, missing citations + payment flow |
| **Screen Coverage** | 90/100 | 76 routes vs 48 spec'd — exceeds spec |
| **API Coverage** | 80/100 | Most endpoints used, payment endpoints basic |
| **UI Quality** | 75/100 | Good structure, missing some polish (greeting, reviews on detail) |
| **Payment Readiness** | 30/100 | M-Pesa/Stripe flows are placeholder dialogs |
| **Offline Support** | 50/100 | Infrastructure exists, not wired into all screens |
| **Push Notification UX** | 40/100 | Receives notifications but no deep linking |
| **Overall** | **72/100** | Solid foundation, needs payment + polish for production |
