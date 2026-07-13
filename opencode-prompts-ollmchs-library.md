# OLLMCHS Library App — opencode Fix & Production-Readiness Prompt Pack

How to use this: run each phase as its own opencode session/prompt, in order. Don't skip the "Report" step in each phase — make opencode show you the findings before it touches code. Don't start Phase N+1 until Phase N's tests pass. Paste each block as-is.

---

## Phase 0 — Full Codebase Audit (no code changes)

```
You are auditing a Flutter + Laravel project called "OLLMCHS Library". There are two Flutter apps (Student App, Lecturer App) sharing one Laravel backend. Do NOT write or modify any code in this phase — this is a read-only audit.

Produce a written report covering:

1. PROJECT STRUCTURE
   - Map the Flutter project(s): folders, state management approach (Bloc/Provider/Riverpod/GetX/etc.), routing setup, DI setup.
   - Map the Laravel backend: modules/controllers/routes relevant to Library, Members, Loans, Reservations, Digital Library, Messaging, Payments, Notifications, Auth.
   - List every API route currently defined vs. every endpoint the Flutter app actually calls. Flag mismatches (frontend calling routes that don't exist, or backend routes with no frontend caller).

2. MODEL / SERIALIZATION AUDIT
   - Grep every `fromJson` / model class in the Flutter app. For each field typed `int` or `int?`, check whether the corresponding Laravel API resource/response actually returns an int or a string. List every field where a JSON string is being cast directly to int (this is the root cause of "type 'String' is not a subtype of type 'int?' in type cast" seen in Categories, New Arrivals, Authors, Publishers screens).

3. STUBS AND DEAD FEATURES
   - Search for TODO, FIXME, placeholder, "not implemented", mock data, hardcoded fake responses, or UI elements with empty/no-op onPressed handlers.
   - Confirm status of: M-Pesa STK push (frontend + backend/Daraja integration), Library Card generation (QR/barcode + backend Member linkage), biometric login (local_auth wiring + platform config), Messaging tab/screen, Two-Factor Authentication, push notifications (FCM), offline caching (Hive/SQLite), background sync.

4. KNOWN BUG ROOT-CAUSE CONFIRMATION
   - Confirm root cause of the Library Card 404: "No query results for model [App\Modules\Members\Models\Member]". Check how the authenticated user resolves to a Member record (relationship, seeder data, route model binding, or missing `member_id` on the user).
   - Confirm the M-Pesa checkout screen's real behavior: is the STK push request actually sent to Safaricom Daraja, is there a callback route, is there any polling/webhook to confirm payment status, or is the progress bar and success toast fully client-side/fake?

5. FEATURE-SPEC GAP ANALYSIS
   - I will paste the full feature specification document for both apps below. Cross-reference every listed feature/screen against actual implemented code. Output a table: Feature | Spec'd | Implemented | Partially Implemented | Missing | Notes.

6. PRODUCTION-READINESS GAPS
   - Error handling/retry patterns, loading/skeleton states, empty states, token refresh/session expiry handling, secure storage usage, crash reporting/analytics hooks, accessibility, network status handling.

Output format: a single markdown report, organized by the 6 sections above. Do not fix anything yet. End with a prioritized punch list ordered by (a) severity/user-facing breakage, (b) effort, (c) dependency order (e.g. backend Member-linkage bug should be fixed before Library Card frontend work depends on it).

--- PASTE FEATURE SPEC DOCUMENT BELOW ---
[paste the full "OLLMCHS Library Flutter mobile app" feature spec doc here]
```

**Gate before Phase 1:** Read the report. Confirm the priority order makes sense for your timeline. Adjust Phase 1–7 order below if needed based on what opencode actually finds.

---

## Phase 1 — Fix the type-cast crash (Categories / New Arrivals / Authors / Publishers / etc.)

```
Context: Phase 0 audit identified that "type 'String' is not a subtype of type 'int?' in type cast" errors are caused by Flutter models casting API-returned string values directly to int in fromJson().

Task:
1. List every model class affected (from your Phase 0 findings).
2. For each affected field, implement a safe parser instead of a direct cast — e.g. a shared `parseInt(dynamic value)` / `parseIntOrNull(dynamic value)` utility that handles String, int, double, and null inputs, and use it in every fromJson() instead of `json['x'] as int` / `json['x'] as int?`.
3. Do NOT silently swallow genuinely malformed data — if a value can't be parsed, throw a descriptive error identifying the field name and raw value, so future API contract breaks are loud in logs, not silent nulls.
4. Apply this fix consistently across all affected models, not just the 4 screens shown in the bug reports — grep the whole models directory for the same anti-pattern.
5. If feasible, also flag on the backend side: which API resources are returning integers as strings (likely a Laravel Resource/serialization issue, e.g. casting attributes to string, or DB driver returning numeric strings). Fixing the backend to return correct types is the more correct long-term fix — do both if time allows, but the frontend must be defensive regardless.

Tests to write/run after this fix:
- Unit tests for the new safe-parse utility: valid int, valid int-as-string, null, empty string, malformed string, double string.
- Unit tests (or widget tests with mocked API responses) for each affected model's fromJson(), specifically feeding it string-typed numeric fields to confirm no crash.
- Manual verification: navigate to Browse Categories, New Arrivals, Authors, Publishers screens against a real/staging API and confirm they load without the type-cast error.

Report back: which models were fixed, which backend endpoints (if any) were also corrected, and full test output.
```

---

## Phase 2 — Fix Library Card generation (backend Member linkage + frontend QR/barcode)

```
Context: Library Card screen fails with "Failed to load library card: ServerException: No query results for model [App\Modules\Members\Models\Member]. (status: 404)". This means the authenticated user isn't resolving to a Member record on the backend.

Task:
1. Investigate how a logged-in Student/Lecturer user is supposed to resolve to a Member model — check the relationship (User hasOne/belongsTo Member?), the route/controller handling the library-card endpoint, and route-model-binding logic. Identify why it's failing: missing seed data, missing foreign key on creation (e.g. Member never created at registration time), wrong ID being passed, or broken relationship.
2. Fix the backend so that:
   a. Every Student/Lecturer registration automatically creates/links a Member record.
   b. The library-card endpoint correctly resolves the Member for the currently authenticated user and returns a proper 404 with a clear message only if a Member genuinely doesn't exist (not as a default failure mode).
   c. Add a data migration/backfill for existing users who registered before this fix, so nobody is stuck without a Member record.
3. On the backend, ensure the library-card endpoint returns everything the frontend needs to render: member ID, membership type, expiry date, a unique code/payload for QR generation, and photo/name/department if not already available client-side.
4. On the frontend, implement:
   a. QR code generation (use a package like `qr_flutter`) rendering the member's unique code/payload.
   b. Barcode generation if required by spec (e.g. `barcode_widget`).
   c. "Download PDF" — generate a shareable/printable card as PDF (this app already has a pdf skill pattern elsewhere if used; otherwise use `pdf` + `printing` packages).
   d. "Share" — use `share_plus` to share the QR/PDF.
   e. "Verify" — if this is meant to let library staff verify a card (e.g. scan and check membership status/expiry), implement the corresponding endpoint + screen.
5. Handle edge cases in the UI: expired membership, suspended membership, pending payment (tie into Phase 3's M-Pesa fix) — show correct state instead of just success/404.

Tests:
- Backend: feature tests for the library-card endpoint — authenticated user with existing Member returns 200 with correct payload; user without Member is auto-backfilled or returns a clear actionable error (not a generic 404); unauthenticated request returns 401.
- Backend: test the registration flow creates a Member record.
- Frontend: widget test rendering the Library Card screen with a mocked successful response, confirming QR renders.
- Frontend: widget test for the error/expired/pending states.
- Manual end-to-end: log in as a real test student, load Library Card, confirm QR/barcode renders, PDF downloads, share works.

Report back: root cause found, backend fix details, migration/backfill approach, and full test output.
```

---

## Phase 3 — Implement real M-Pesa STK Push (Daraja) end-to-end

```
Context: Checkout screen UI (plan summary, M-Pesa/Card selector, phone number field, progress indicator, "Subscribed successfully" toast) exists but the STK push is not actually implemented — it's a mocked success path.

Task — Backend (Laravel, Daraja API):
1. Set up/confirm Safaricom Daraja API credentials handling (Consumer Key, Consumer Secret, Shortcode, Passkey) via .env config — never hardcoded.
2. Implement:
   a. OAuth token generation (cached until near expiry).
   b. STK Push request endpoint: accepts amount, phone number, account reference, description; calls Daraja's `/mpesa/stkpush/v1/processrequest`; returns `CheckoutRequestID` to the frontend immediately.
   c. Callback endpoint (public route Safaricom hits) to receive payment confirmation/failure, verify the payload, update the payment/subscription record, and trigger any post-payment action (e.g. activate membership, unlock plan).
   d. A status-check endpoint (`stkpushquery`) the frontend can poll, for cases where the callback is delayed or the user wants to check "did it go through" without waiting on a webhook alone.
   e. Idempotency: ensure duplicate callbacks or retried STK pushes don't double-charge/double-activate.
   f. Logging of all Daraja requests/responses for debugging failed payments (sanitize sensitive data in logs).
3. Add a `payments`/`transactions` table (if not present) recording: user, amount, phone, checkout_request_id, status (pending/success/failed/cancelled/timeout), raw callback payload, timestamps.

Task — Frontend (Flutter):
1. Replace the fake progress bar / instant "Subscribed successfully" flow with:
   a. On submit: call the STK push endpoint, show a real "Check your phone" loading state.
   b. Poll the status-check endpoint every few seconds (with a sane timeout, e.g. 60–90s) OR listen for a push/socket event if you have one, until status resolves to success/failed/timeout.
   c. Handle all outcomes distinctly in the UI: success (show real confirmation, unlock the feature), user cancelled on phone, insufficient funds, timeout (STK expired without action), network/API error — each with a clear message and retry option.
   d. Validate the phone number format before submitting (Safaricom-format normalization, e.g. 07XXXXXXXX / 2547XXXXXXXX / +2547XXXXXXXX all handled).
2. Wire the "Card" payment option — either implement it for real if you have a card processor, or hide/disable it with a "coming soon" state rather than leaving it as a dead selectable button.

Tests:
- Backend: unit test OAuth token caching. Feature tests for STK push endpoint (mock Daraja HTTP calls — don't hit the real sandbox in CI) covering success request, failed request, invalid phone. Feature test for the callback endpoint handling success payload, failure payload, and malformed/replayed payload (idempotency). Feature test for the status-check endpoint.
- Frontend: widget tests for each checkout outcome state (loading, success, cancelled, timeout, error) using a mocked API client.
- Manual test: run against Safaricom's sandbox with a real test MSISDN, confirm the actual phone prompt appears, confirm both a completed and a cancelled STK push resolve correctly in the app.

Report back: what was previously stubbed vs. what's now real, sandbox test evidence, and full automated test output.
```

---

## Phase 4 — Fix biometric login (fingerprint + face unlock)

```
Context: The Settings screen's biometric login toggle/button does nothing.

Task:
1. Confirm `local_auth` (or equivalent) is a real dependency, not just referenced in the spec doc. Add it if missing.
2. Implement platform config:
   - Android: required permissions/manifest entries, `USE_BIOMETRIC`/`USE_FINGERPRINT`, and MainActivity extending FragmentActivity if required by the package version.
   - iOS: Face ID usage description in Info.plist, and any required entitlements.
3. Implement the actual flow:
   a. Settings toggle: check device biometric capability (`canCheckBiometrics` / `isDeviceSupported`), if unsupported show a disabled state with an explanation instead of a dead button.
   b. On enabling: prompt biometric auth to confirm identity, then securely store a flag/token (using secure storage, not shared prefs) enabling biometric login.
   c. On login screen: if biometric login is enabled for this device/account, offer the fingerprint/face icon; on tap, trigger biometric prompt, and on success exchange the stored secure credential/token for a session the same way normal login does (don't bypass auth — biometric should unlock a securely stored refresh token or trigger a silent re-auth, not fake a login).
   d. Handle failure/cancellation/lockout (too many failed attempts) gracefully with fallback to password login.
   e. On disabling biometric login (or logout), clear the securely stored credential.
4. Confirm this works in both the Student and Lecturer apps consistently.

Tests:
- Widget test: Settings screen shows correct enabled/disabled/unsupported states based on mocked `local_auth` capability responses.
- Widget test: login screen shows/hides the biometric option correctly based on stored preference.
- Unit test: secure storage read/write/clear logic for the biometric-linked credential.
- Manual test: on a real device/emulator with biometrics configured, enable biometric login in Settings, log out, log back in via fingerprint/face, confirm it lands on the correct authenticated session. Also test the "cancel" and "failed attempt" paths.

Report back: what was missing (package, platform config, or logic), and full test output plus a short device-test note (device/emulator used).
```

---

## Phase 5 — Activate the Messaging tab

```
Context: The Messaging tab in bottom navigation is inactive/dead — spec requires Inbox, Sent, Archive, Compose, Reply, Reply All, Forward, Attachments, Search, Unread Badge (Student), and Direct Students / Course Broadcast / Templates (Lecturer).

Task:
1. Confirm backend endpoints exist for: list inbox/sent/archive, get single message thread, compose/send message, reply/reply-all/forward, attachment upload/download, mark read/unread, unread count. Implement any missing ones (Laravel routes + controllers + form requests + policies so students can't message arbitrary other students if that's a restriction in your spec, and lecturers can broadcast to their own courses only).
2. Implement the frontend Messaging tab for real:
   a. Inbox/Sent/Archive tabs with pull-to-refresh, pagination/infinite scroll, skeleton loading, empty states.
   b. Compose screen with recipient picker (student→librarian/lecturer, lecturer→students/course), subject/body, attachment picker.
   c. Thread view with reply/reply-all/forward actions and attachment rendering/download.
   d. Search within messages.
   e. Unread badge on the bottom nav icon wired to a real unread count (poll or push-driven).
   f. Lecturer-specific: course broadcast composer, message templates (save/reuse canned messages).
3. Wire push notifications for new messages if FCM is otherwise set up (tie into your notifications system rather than building a separate one).

Tests:
- Backend: feature tests for send/reply/forward/mark-read/unread-count endpoints, and authorization tests (student can't broadcast to a course, can't read another student's private thread, lecturer can only broadcast to their assigned courses).
- Frontend: widget tests for inbox list rendering, compose validation (empty recipient/body blocked), and unread badge updating after marking a message read.
- Manual end-to-end: send a message from a lecturer test account to a student test account, confirm it appears in the student's inbox, reply, confirm it appears in the lecturer's inbox, confirm unread badge decremented after reading.

Report back: endpoints added, screens implemented, and full test output.
```

---

## Phase 6 — Full feature-spec gap closure

```
Context: Using the Phase 0 gap-analysis table (Feature | Spec'd | Implemented | Partial | Missing), work through every item marked "Missing" or "Partial" that wasn't already covered in Phases 1–5.

Task:
1. Re-confirm the Phase 0 table is still accurate (some items may have shifted after Phases 1–5).
2. Group remaining gaps by module (e.g. Onboarding, Auth/2FA, Dashboard, Book Catalog, Digital Library/PDF Reader, Reading Assignments, Recommendations, Loans, Reservations, Downloads/Offline, Notifications, Events, Announcements, Lecturer-specific: Course management, Assignment analytics, Reports export).
3. For each group, implement missing backend endpoints and frontend screens/logic to match the spec, following the same pattern used in prior phases: real data wiring, proper loading/empty/error states, no hardcoded/mock data left behind.
4. Pay particular attention to items likely to be stubbed based on typical scaffolds: Two-Factor Authentication (Authenticator app TOTP setup + recovery codes + trusted device logic), PDF reader with progress tracking/highlights/notes, offline download storage + sync, AI/recommendation logic (even a simple rules-based "same category/department" recommender is fine if a full ML pipeline isn't in scope — but it must not be hardcoded fake data), and Lecturer analytics/report export (PDF/Excel).
5. Do not silently skip anything — if something is genuinely out of scope for this pass, list it explicitly as "deferred" with a reason, rather than leaving a half-built dead button.

Tests:
- For each module fixed, backend feature tests covering the core CRUD/business logic and authorization.
- For each module fixed, at least one Flutter widget test proving the screen renders real (mocked) API data correctly, plus tests for its empty/error states.
- Regression pass: re-run the full existing test suite (backend + frontend) to confirm nothing from Phases 1–5 broke.

Report back: updated gap table (should now show everything Implemented or explicitly Deferred with reason), and full test output.
```

---

## Phase 7 — Production hardening pass

```
Context: Final pass before this is genuinely production-ready across both Student and Lecturer apps.

Task:
1. Error handling & resilience: audit every API call site — confirm consistent error handling (network timeout, 401/403/404/422/500), retry-with-backoff where appropriate, and user-facing messages that are actionable, not raw exception text (the app currently shows raw error strings like "type 'String' is not a subtype..." directly in the UI — replace all raw exception surfaces with friendly messages, logging the raw error internally instead).
2. Session/token handling: confirm JWT/Sanctum token refresh works transparently, expired sessions redirect to login cleanly without losing user context, and logout revokes the token server-side, not just clears local state.
3. Offline & sync: confirm Hive/SQLite caching actually persists the data it claims to (dashboard, book catalog, downloads), and background sync reconciles cleanly without duplicate/stale data.
4. Push notifications: confirm FCM registration, topic/device-token handling on login/logout (don't keep sending pushes to a device after logout or account switch), and deep-linking from notification tap into the right screen.
5. Security: confirm secure storage (not SharedPreferences) is used for tokens/credentials, biometric-linked secrets, and any sensitive cached data. Confirm API base URLs/secrets aren't hardcoded in a way that leaks in the APK. Confirm 2FA recovery codes are stored hashed server-side.
6. Crash reporting/analytics: confirm a crash reporting tool (e.g. Firebase Crashlytics/Sentry) is actually wired and firing, not just listed as a dependency.
7. Accessibility: dynamic text scaling doesn't break layouts, screen-reader labels exist on key interactive elements (buttons, form fields, nav items).
8. App update checks / maintenance mode / server connectivity checks (mentioned in your splash screen spec) — confirm these are real checks against a real backend endpoint/version config, not hardcoded to always pass.
9. Build/release readiness: confirm separate Student/Lecturer app IDs and build flavors, proper app icons/splash assets, versioning strategy, and that debug logging/print statements are stripped from release builds.

Tests:
- Simulate: expired token mid-session (confirm silent refresh or clean redirect to login), airplane mode during an API call (confirm friendly offline message, not a crash), killing and reopening the app while a download/sync is in progress (confirm no data corruption).
- Confirm crash reporting captures a deliberately thrown test exception in a debug build pointed at your Crashlytics/Sentry project.
- Run a full regression of the automated test suite (backend + frontend) one final time and report total pass/fail counts.
- Manual full-app walkthrough checklist (both apps): splash → onboarding → register → login → biometric login → 2FA → dashboard → browse/search/borrow → digital library/PDF reader → reading assignment (lecturer creates, student completes) → messaging → library card → M-Pesa checkout → notifications → settings → logout. Note any friction or bugs found.

Report back: full hardening findings, fixes applied, final regression test results, and the manual walkthrough checklist results (pass/fail per step).
```

---

## Notes on using this pack

- **Order matters.** Phase 2 (Library Card) depends on backend Member-linkage being fixed first — if opencode's Phase 0 report shows a different root cause than expected, adjust Phase 2's prompt accordingly before running it.
- **Don't run Phase 3 (M-Pesa) against production Daraja credentials during testing** — use Safaricom's sandbox until Phase 7's final walkthrough.
- **Feed each phase's "Report back" output into the next phase's prompt as context** if you're running these in separate opencode sessions, so it doesn't rediscover the same things twice.
- If opencode's context window can't hold the whole feature spec doc in Phase 0, split it into Student App spec and Lecturer App spec as two separate audit sub-runs, then merge the gap tables yourself before Phase 6.
