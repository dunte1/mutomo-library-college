
# OLLMCHS Library Mobile Audit & Completion (Flutter + Laravel)

You are a senior Flutter, Laravel, Mobile Architecture, API Integration, QA, Security and DevOps engineer.

Your job is **NOT** to review only.

Your job is to completely finish the project.

You must continue working until every missing feature has been implemented, wired, tested and verified.

This is a production system.

Never leave placeholders, TODOs, mock data, fake implementations, stubs or partially wired screens.

---

# Project

Backend

* Laravel 13
* Sanctum
* RBAC
* Livewire
* MySQL
* REST API

Apps

Student APK

Lecturer APK

Flutter 3

Dio

Bloc

GoRouter

GetIt

Secure Storage

Firebase Notifications

Biometric Authentication

PDF Reader

QR

Shared Laravel API

---

# Read Everything First

Before making a single change:

Read every file inside

flutter_app/

Read every file inside

flutter_web/

(if still present)

Read every API endpoint.

Read every Bloc.

Read every Repository.

Read every Model.

Read every DTO.

Read every Service.

Read every Screen.

Read every Widget.

Read every Route.

Read every Test.

Read every Theme.

Read every Localization.

Read every Asset.

Read every Notification service.

Read every Auth implementation.

Read every Settings implementation.

Read every biometric implementation.

Read every Offline Sync implementation.

Read every PDF implementation.

Read every Messaging implementation.

Read every Reading Assignment implementation.

Read every Digital Library implementation.

Read every Course implementation.

Read every Book implementation.

Read every API call.

Read every Laravel API controller.

Read every API Resource.

Read every Request validation.

Read every Policy.

Read every Middleware.

Read every Permission.

Read every Role.

Read every Migration.

Read every Seeder.

Do NOT skip files.

---

# Also Read

Read

flutterspecs.md

and compare it against the implementation.

Nothing in the specification should remain unimplemented.

---

# Phase 1

## Full Inventory

Produce a complete inventory.

For every feature identify

Implemented

Partial

Broken

Not Wired

Dead Code

Duplicate

Unused

Stub

Placeholder

Mock

Missing API

Missing UI

Missing Model

Missing Bloc

Missing Repository

Missing Route

Missing Permission

Missing Tests

Missing Assets

Missing Localization

Missing Error States

Missing Empty States

Missing Loading States

Missing Retry States

Missing Offline States

---

# Phase 2

## API Audit

Verify every endpoint.

Every endpoint must

exist

authenticate

authorize

validate

return correct HTTP codes

return consistent JSON

return Resources

return proper pagination

return proper metadata

return proper error responses

return correct data types

No endpoint should return incorrect types such as

double instead of int

string instead of bool

nullable inconsistencies

missing IDs

missing timestamps

missing relationships

Fix every issue.

---

# Phase 3

## Flutter Audit

Inspect every screen.

Every screen must have

Loading

Empty

Error

Retry

Offline

Unauthorized

Forbidden

Session Expired

No Results

No Data

Success

Confirmation

Delete Confirmation

Permission Denied

Image Placeholder

No Internet

Every list

Every card

Every dialog

Every sheet

Every page

Every widget

must support all required states.

---

# Phase 4

## Navigation Audit

Verify

GoRouter

ShellRoute

Drawer

Bottom Navigation

Deep Links

Notifications

Back Navigation

Logout

Session Expiry

Every navigation path.

No broken navigation.

No dead routes.

No unreachable pages.

---

# Phase 5

## Authentication Audit

Verify

Login

Register

Forgot Password

Reset Password

Email Verification

2FA

Biometric Login

Fingerprint

Face ID

Token Refresh

Remember Me

Secure Logout

Session Timeout

Token Revocation

Device Logout

Multi Device Login

Everything must work.

---

# Phase 6

## Settings Audit

Verify every settings page.

Theme

Dark Mode

Language

Notifications

Biometrics

Fingerprint

Face Unlock

2FA

Account

Privacy

Downloads

Cache

Storage

About

Terms

Privacy Policy

Logout

Delete Account

Everything must be wired.

Dark mode must actually apply globally.

Biometrics must actually authenticate.

2FA must actually work.

---

# Phase 7

## Student App

Verify every screen.

Dashboard

Books

Book Details

Search

Categories

Recommendations

New Arrivals

Bookmarks

Downloads

Digital Library

Reader

PDF

Assignments

Assignment Details

Loans

Loan History

Reservations

Renewals

Notifications

Messaging

Inbox

Compose

Reply

Profile

Settings

About

Help

Support

Feedback

Everything must work.

---

# Phase 8

## Lecturer App

Verify every feature.

Dashboard

Assignments

Create Assignment

Edit Assignment

Delete Assignment

Students

Analytics

Digital Library

Recommendations

Messaging

Broadcast

Announcements

Notifications

Profile

Settings

Reports

Everything must work.

---

# Phase 9

## Messaging

Verify

Inbox

Compose

Reply

Reply All

Forward

Delete

Search

Attachments

Read Status

Unread

Broadcast

Push Notifications

Everything must work.

---

# Phase 10

## Digital Library

Verify

Reader

Bookmarks

Highlights

Notes

Progress

Downloads

Offline Reading

Sync

Resume Reading

Recent Reading

Everything must work.

---

# Phase 11

## Offline Mode

Verify

Downloaded Books

Offline Reader

Offline Queue

Offline Assignments

Offline Messaging Drafts

Offline Cache

Conflict Resolution

Automatic Sync

Everything must work.

---

# Phase 12

## Notifications

Verify

Push

Local

Reminder

Assignment

Loan Due

Reservation

Announcement

Message

Broadcast

Deep Links

Everything must work.

---

# Phase 13

## Security Audit

Audit

Sanctum

Bearer Tokens

Secure Storage

Encrypted Preferences

Certificate Pinning

HTTPS Only

Rate Limiting

Password Rules

RBAC

Permission Checks

API Authorization

Input Validation

File Upload Security

Image Upload Validation

PDF Protection

Download Authorization

Expired Tokens

CSRF

XSS

SQL Injection

Mass Assignment

Hidden Fields

Sensitive Logs

Everything must be production ready.

---

# Phase 14

## Performance Audit

Inspect

Rebuilds

Bloc usage

Memory leaks

Image caching

Pagination

Lazy loading

Infinite scrolling

Repository caching

API batching

Database indexes

Slow queries

Duplicate requests

Everything must be optimized.

---

# Phase 15

## UI Audit

Every screen must

match branding

be responsive

work on

Android phones

Small phones

Large phones

Tablets

Landscape

Foldables

Windows Desktop

Flutter Web

No overflow.

No clipped text.

No RenderFlex overflow.

No NavigationRail assertions.

No layout exceptions.

No yellow overflow warnings.

No red screens.

---

# Phase 16

## Testing

Create

Widget Tests

Bloc Tests

Repository Tests

API Tests

Integration Tests

Golden Tests

Navigation Tests

Authentication Tests

Permission Tests

Offline Tests

Notification Tests

Run every test.

Fix until

ALL TESTS PASS.

---

# Phase 17

## Dead Code Audit

Remove

Unused Widgets

Unused Screens

Unused APIs

Unused Services

Unused Models

Unused Bloc

Unused Repositories

Unused Assets

Unused Routes

Unused Packages

Unused Imports

Unused Variables

Unused Methods

No dead code should remain.

---

# Phase 18

## Final Verification

Verify

Every menu

Every card

Every button

Every modal

Every dialog

Every drawer

Every bottom sheet

Every API

Every screen

Every navigation path

Every permission

Every notification

Every download

Every upload

Every PDF

Every biometric

Every token

Every logout

Every session

Everything.

---

# Completion Requirements

Do NOT stop until

✔ every missing screen exists

✔ every screen is wired

✔ every API is wired

✔ every feature works

✔ every test passes

✔ every permission works

✔ every menu works

✔ every navigation works

✔ every loading state exists

✔ every empty state exists

✔ every error state exists

✔ every offline state exists

✔ every dialog works

✔ every repository works

✔ every Bloc works

✔ every API works

✔ every notification works

✔ biometrics work

✔ fingerprint works

✔ 2FA works

✔ dark mode works

✔ offline sync works

✔ no placeholders remain

✔ no stubs remain

✔ no TODOs remain

✔ no dead code remains

✔ project is production ready

---

# Final Report

When everything is complete, generate a comprehensive report containing:

1. Files Added

2. Files Modified

3. Files Removed

4. APIs Fixed

5. Screens Added

6. Screens Fixed

7. Widgets Added

8. Bloc Changes

9. Repository Changes

10. Model Changes

11. Navigation Changes

12. Security Fixes

13. Performance Optimizations

14. Responsive UI Fixes

15. Offline Features

16. Notification Improvements

17. Biometric Improvements

18. 2FA Improvements

19. API Improvements

20. Database Improvements

21. Test Coverage

22. Remaining Issues (if any)

23. Production Readiness Score (0–100%)

24. Confirmation that:

* every feature in `flutterspecs.md` has been verified,
* every implemented feature is fully wired,
* every missing feature has been implemented,
* no placeholder or stub code remains,
* all automated tests pass,
* the Student APK and Lecturer APK are production-ready.
