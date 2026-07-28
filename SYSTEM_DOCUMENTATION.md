# OLLMCHS Library Management System — Complete System Documentation

> **Our Lady of Lourdes Mutomo College of Health Sciences**
> Full-stack library management platform: Laravel web app + Flutter mobile companion

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [Module Map](#2-module-map)
3. [Authentication & Security](#3-authentication--security)
4. [Book Catalog Management](#4-book-catalog-management)
5. [Circulation (Borrowing & Lending)](#5-circulation-borrowing--lending)
6. [Fine Management](#6-fine-management)
7. [Member Management](#7-member-management)
8. [Library Cards](#8-library-cards)
9. [Digital Library](#9-digital-library)
10. [Finance & Payments](#10-finance--payments)
11. [Communication System](#11-communication-system)
12. [Notifications](#12-notifications)
13. [Reading Assignments](#13-reading-assignments)
14. [Subscriptions](#14-subscriptions)
15. [Reports & Analytics](#15-reports--analytics)
16. [Settings & Administration](#16-settings--administration)
17. [Document Verification](#17-document-verification)
18. [API Reference](#18-api-reference)
19. [Mobile App](#19-mobile-app)
20. [Deployment](#20-deployment)
21. [Testing](#21-testing)
22. [Data Model Reference](#22-data-model-reference)

---

## 1. Architecture Overview

### Technology Stack

| Layer | Technology |
|---|---|
| **Backend Framework** | Laravel 13.8 (PHP 8.3) |
| **Frontend** | Livewire 3.6 + Livewire Volt 1.7 (Single-File Components) |
| **CSS** | Tailwind CSS 3 + Alpine.js |
| **Build** | Vite 8 with PWA support (Workbox service worker) |
| **Database** | SQLite (dev) / MySQL 8.0 (production) |
| **Cache/Queue** | Redis 7 |
| **Mobile** | Flutter 3.12 (Dart) — BLoC architecture |
| **Payments** | M-Pesa (Safaricom Daraja API) + Stripe |
| **Auth** | Laravel Breeze + Sanctum + Google2FA (TOTP) |
| **RBAC** | Spatie Laravel Permission 7.4 |
| **Audit** | Spatie Laravel Activity Log 4.12 |
| **PDF** | DomPDF |
| **Export** | PHPSpreadsheet |
| **Push** | Web Push (VAPID via minishlink/web-push) |

### Project Structure

```
ollmchs-library/
├── app/
│   ├── Console/Commands/          # 8 artisan commands
│   ├── Http/
│   │   ├── Controllers/           # Base + health check
│   │   └── Middleware/            # 5 custom middleware
│   ├── Jobs/                      # 4 queued jobs
│   ├── Listeners/                 # 1 event listener
│   ├── Livewire/                  # Global Livewire components
│   ├── Mail/                      # 9 mailable classes
│   ├── Models/                    # 8 core Eloquent models
│   ├── Modules/                   # 14 domain modules
│   ├── Providers/                 # 3 service providers
│   ├── Services/                  # 3 top-level services
│   └── View/                      # Components + composers
├── config/                        # 17 config files
├── database/
│   ├── factories/                 # 13 model factories
│   ├── migrations/                # 64 migration files
│   └── seeders/                   # 12 seeders
├── docker/                        # nginx.conf, supervisord, start.sh
├── ollmchs_mobile/                # Flutter mobile companion
├── resources/
│   ├── css/                       # Tailwind app.css
│   ├── js/                        # app.js + PWA service worker
│   └── views/                     # 34+ Blade components, emails, docs
├── routes/                        # web.php, auth.php, console.php
└── tests/                         # 42 feature + 3 unit tests
```

### Domain Modules

| Module | Responsibility |
|---|---|
| **API** | RESTful API for mobile app (Sanctum, 70+ endpoints) |
| **Assignments** | Teacher-student reading assignments |
| **Auth** | Registration, login, 2FA, password management |
| **Catalog** | Books, authors, publishers, categories, subjects, inventory |
| **Circulation** | Borrowing, returning, renewals, reservations, waitlists |
| **Communication** | Messaging, announcements, bulletins, events, templates |
| **DigitalLibrary** | Digital assets, reading history, recommendations, citations |
| **Finance** | Transactions, invoices, receipts, M-Pesa, Stripe, analytics |
| **Members** | Registration, library cards, membership types |
| **Notifications** | In-app + push + email + WhatsApp |
| **Reports** | Catalog, circulation, member, digital library, financial reports |
| **Settings** | System config, users, roles, departments, programs |
| **Shared** | Traits (Auditable, Searchable), helpers, interfaces |
| **Subscriptions** | Plans, billing, Stripe, enforcement middleware |

---

## 2. Module Map

```
┌─────────────────────────────────────────────────────────────────────┐
│                        OLLMCHS Library System                       │
├──────────┬──────────┬──────────┬──────────┬──────────┬──────────────┤
│  Auth    │ Catalog  │Circulat. │ Members  │ Finance  │ Subscriptions│
│ ─────── │ ─────── │ ─────── │ ─────── │ ─────── │ ─────────── │
│ Login    │ Books    │ Issue    │ Register │ Transact │ Plans        │
│ Register │ Authors  │ Return   │ Cards    │ Invoices │ Billing      │
│ 2FA      │ Categories│ Renew   │ Types    │ Receipts │ Stripe       │
│ Password │ Publishers│ Reserve │ Requests │ M-Pesa   │ Enforcement  │
│ Sessions │ Subjects │ Fines   │ Suspend  │ Refunds  │ Renewals     │
├──────────┼──────────┴──────────┼──────────┴──────────┼──────────────┤
│ Digital  │    Communication   │     Notifications   │   Reports    │
│ Library  │ ────────────────── │ ─────────────────── │ ──────────── │
│ Assets   │ Messages           │ In-app              │ Catalog      │
│ Download │ Announcements      │ Email (9 classes)   │ Circulation  │
│ Reader   │ Bulletins          │ WhatsApp            │ Members      │
│ History  │ Events             │ Push (VAPID)        │ Digital Lib  │
│ Recommd  │ Templates          │ SMS                 │ Financial    │
│ Cite     │ Broadcasts         │ Prefs               │ Document Vrf │
├──────────┴──────────┬─────────┴──────────┬──────────┴──────────────┤
│    Assignments      │      Settings      │    Document Verification │
│ ─────────────────── │ ────────────────── │ ──────────────────────── │
│ Teacher → Student   │ General            │ QR code generation      │
│ Books / Digital     │ Circulation        │ Public lookup           │
│ Status tracking     │ Security/2FA       │ Revocation support      │
│ Group assignments   │ Backup/Maintenance │ Verification count      │
│                     │ Audit/System logs  │                          │
└─────────────────────┴────────────────────┴─────────────────────────┘
```

---

## 3. Authentication & Security

### Registration Flow

1. User visits `/register` (standard) or `/register/student` (student-specific)
2. Fills in: name, email, phone, password, department, program, admission/employee number
3. Email verification sent via `VerifyEmailController`
4. After verification, account enters **Pending Approval** state
5. Admin activates account → user can log in
6. Password confirmation required every 3 hours for sensitive actions

### Two-Factor Authentication (2FA)

- **Setup**: User enables 2FA via Google2FA (TOTP)
- **Secret**: Encrypted and stored on User model
- **Recovery codes**: Generated on enable, stored encrypted
- **Enforcement**: `TwoFactorMiddleware` redirects to verification page
- **Verification**: Validated against session until explicit logout
- **Disable**: Requires password confirmation + current TOTP code

### Role-Based Access Control (10 Roles)

| Role | Book Access | Borrow Limit | Circulation | Finance | Settings | Reports |
|---|---|---|---|---|---|---|
| **super-admin** | Full | 100 | Full | Full | Full | Full |
| **admin** | Full | 20 | Full | Full | Full | Full |
| **librarian** | Full | 15 | Full | Limited | Limited | Limited |
| **assistant-librarian** | Full | 10 | Limited | No | No | Limited |
| **finance-officer** | View | 0 | No | Full | No | Financial |
| **ict-admin** | View | 0 | No | No | Full | System |
| **department-head** | View | 0 | No | No | No | Read-only |
| **lecturer** | View | 10 | View | No | No | No |
| **student** | View | 5 | Self | No | No | No |
| **staff** | View | 5 | Self | No | No | No |
| **guest** | View | 0 | No | No | No | No |

### Session Management

- **Idle timeout**: 30 minutes (configurable via `EnsureSessionIdleTimeout` middleware)
- **Max concurrent sessions**: 5 (oldest tokens revoked automatically)
- **Role/permission change**: All other sessions invalidated via event listener
- **Token refresh**: Sanctum tokens auto-refresh on active use
- **Cleanup**: Scheduled artisan command removes expired tokens

### Security Headers (`SecurityHeadersMiddleware`)

- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- Referrer-Policy: strict-origin-when-cross-origin
- Permissions-Policy: camera=(), microphone=(), geolocation=()
- HSTS: enabled in production
- Content-Security-Policy: production CSP

### Subscription Enforcement (`CheckSubscriptionStatus`)

- Capability-gated access: view, borrow, upload, download, messaging
- Super-admins bypass subscription checks
- Handles: active, trial, grace period, expired states
- Configurable per capability in `config/subscription.php`

---

## 4. Book Catalog Management

### Core Entities

**Book** — The central catalog entry
- ISBN, title, subtitle, slug, description, language, pages
- Publication year, edition, volume, series
- Cover image, condition, status
- Dewey Decimal, LC classification
- Tags, featured flag, active status
- Relationships: many-to-many Authors, Subjects; has-many BookCopy, DigitalAsset, BookReview

**BookCopy** — Physical copies of a book
- Barcode (auto-generated), RFID tag, shelf location
- Status: available, borrowed, reserved, maintenance, lost, damaged
- Condition: new, good, fair, poor, damaged
- Acquired date, price, notes

**Author** — Book authors
- Name, slug, biography, birth/death date, nationality, photo, website

**Category** — Hierarchical classification
- Self-referencing parent/children tree
- Sort order for display ordering

**Publisher** — Book publishers
- Name, slug, address, phone, email, website

**Subject** — Subject tags
- Many-to-many with books

### Features

- **Full-text search**: Cross-database (MySQL full-text + SQLite LIKE fallback via `Searchable` trait)
- **Bulk upload**: CSV import via `BookBulkUploadJob`
- **Barcode generation**: Auto-generates unique barcodes for book copies
- **New arrivals**: Auto-sorted by `acquired_at` date
- **Featured books**: Curated by admin
- **Book reviews**: User ratings + written reviews with approval workflow
- **Inventory tracking**: Per-copy status, condition, shelf location
- **Duplicate detection**: ISBN-based deduplication on import

---

## 5. Circulation (Borrowing & Lending)

### Borrowing Flow

1. **Issue**: Librarian scans book barcode → selects patron → sets due date
2. **Validation**: Checks book availability, patron status, borrow limits
3. **Record created**: `BorrowRecord` with `borrowed_at`, `due_at`, `status: active`
4. **Book copy updated**: status → `borrowed`

### Return Flow

1. **Return**: Librarian scans barcode → patron identified from active borrow
2. **Record updated**: `returned_at` set, `status: returned`
3. **Book copy updated**: status → `available`
4. **Fine assessment**: If overdue, fine auto-calculated and created

### Renewal Flow

1. **Patron requests renewal** (self-service or librarian)
2. **Validation**: Checks `renewal_count < max_renewals`, book not reserved by others
3. **Record updated**: `renewed_at` set, `due_at` extended, `renewal_count` incremented
4. **Notification**: Renewal reminder sent

### Reservation System

1. **Reserve**: Patron reserves an unavailable book
2. **Notification**: When book returned, patron notified
3. **Expiration**: Unclaimed reservations expire after configurable period
4. **Waitlist**: Multiple patrons can queue for same book

### Status Tracking

| Status | Meaning |
|---|---|
| `active` | Currently borrowed |
| `returned` | Successfully returned |
| `overdue` | Past due date, not yet returned |
| `lost` | Marked as lost by librarian |
| `damaged` | Returned with damage (may also have fine) |

### Automated Tasks

- `circulation:check-overdue` — Marks overdue borrows, assesses fines, sends notifications
- `circulation:assess-overdue-fines` — Batch fine assessment for all active overdue records
- `circulation:expire-reservations` — Expires old unclaimed reservations

---

## 6. Fine Management

### Fine Types & Rates (Configurable in `config/fines.php`)

| Type | Default Rate | Description |
|---|---|---|
| **overdue** | KES 50/day | Per day past due date |
| **lost** | KES 1,500 | Flat fee for lost books |
| **damage** | KES 500 | Fee for damaged returns |

### Fine Lifecycle

1. **Assessment**: Auto-created on overdue return or manual librarian action
2. **Status tracking**: pending → paid / waived / disputed
3. **Partial payments**: `paid_amount` tracks cumulative payments
4. **Waiving**: Librarians can waive with reason and notes
5. **Dispute tracking**: Fines can be disputed and reviewed

### Payment Methods

- Cash
- M-Pesa (Safaricom Daraja API)
- Stripe
- Bank transfer
- Card
- Cheque

### Collection Features

- Fine collection dashboard with balance tracking
- Payment confirmation via email
- Receipt generation for each payment
- Refund management for overpayments
- Financial reporting on fine revenue

---

## 7. Member Management

### Member Registration

- **Auto-generated IDs**: `MEM-XXXXX` format
- **Membership types**: student, teacher, staff, external
- **Statuses**: active, suspended, expired, inactive, cleared
- **Required data**: Name, email, phone, DOB, address, gender, ID number, admission/employee number, department, program

### Member Lifecycle

1. **Registration**: Via web form or bulk CSV import
2. **Approval**: Admin/librarian approves membership
3. **Active period**: Membership valid until `expires_at`
4. **Renewal**: Renewal process extends membership
5. **Suspension**: Admin can suspend for violations
6. **Expiry**: Auto-detected by `subscriptions:check-expiry` command

### Membership Request Workflow

1. Member submits request
2. Admin reviews (approve/reject)
3. If approved: member created, library card issued
4. Notification sent to member

---

## 8. Library Cards

### Card Data

- **Card number**: Auto-generated
- **QR code**: Encodes verification URL for public scanning
- **Barcode**: Machine-readable card identifier
- **Passport photo**: User-uploaded or from registration
- **Status**: active, expired, lost, replaced

### Card Lifecycle

| Action | Result |
|---|---|
| **Issue** | New card created, linked to member |
| **Expire** | Card marked expired on `expires_at` |
| **Lose** | Card marked lost, replacement issued |
| **Replace** | Old card → `replaced_by` points to new card |
| **Download** | PDF with QR code and barcode generated |

### Public Verification

- URL: `/verify/card/{cardNumber}`
- Scans QR code → shows member name, photo, card status, validity
- API endpoint: `/api/v1/library-card` with QR/barcode/PDF endpoints

---

## 9. Digital Library

### Digital Asset Types

- eBooks (EPUB, MOBI)
- PDFs (documents, papers)
- Lecture notes
- Journals
- Research papers
- Videos
- Audio files
- Presentations
- Datasets

### Access Control

| Level | Visibility |
|---|---|
| **public** | Everyone (including guests) |
| **restricted** | Authenticated users only |
| **private** | Only uploader + admins |

### Permission Controls

- **Allow download**: Toggle per asset
- **Allow printing**: Toggle per asset
- **Download rate limiting**: 100 downloads/hour per user
- **Download logging**: Every download tracked with IP, user agent, throttling status

### In-Browser Reader

- PDF reader (embedded viewer)
- Reading progress tracking
- Last page memory
- Duration tracking

### Reading History

- Tracks: user, asset, start time, completion, progress percentage, last page, duration
- Persists across sessions
- Powers recommendation engine

### Recommendation Engine

Five recommendation strategies:
1. **Similar books**: Based on category, author, subjects of read books
2. **History-based**: Books similar to user's reading history
3. **Department popular**: Most borrowed in user's department
4. **New arrivals**: Recently added to catalog
5. **Personalized**: Combined scoring across all signals

### Citation Generation

Supported styles:
- **APA** (American Psychological Association)
- **MLA** (Modern Language Association)
- **Chicago**
- **Harvard**
- **Vancouver**
- **IEEE**

Generates formatted citation text for any digital asset or book.

### Smart Tagging

- `SmartTagService` auto-generates relevant tags from content metadata
- Improves discoverability and search

---

## 10. Finance & Payments

### Transaction System

- **Auto-generated numbers**: `TXN-YYYYMMDD-XXXXXX`
- **Types**: fine_payment, subscription_payment, membership_fee, deposit, refund
- **Status tracking**: pending → completed / failed / refunded
- **Payment methods**: cash, mpesa, stripe, bank_transfer, card, cheque

### Invoice System

- **Invoice numbers**: `INV-YYYYMMDD-XXXXXX`
- **PDF generation**: DomPDF with school branding
- **Email delivery**: Send invoice directly to member
- **Due date tracking**: Overdue invoices flagged
- **Status**: draft → issued → paid → overdue

### Receipt System

- **Receipt numbers**: `RCT-YYYYMMDD-XXXXXX`
- **PDF generation**: Branded receipt with transaction details
- **Auto-generated** on each successful payment
- **Downloadable** and **emailable**

### M-Pesa Integration

- **Safaricom Daraja API** (STK Push)
- **Sandbox/production** toggle in `config/mpesa.php`
- **Callback webhook**: `/api/v1/mpesa/callback`
- **Validation webhook**: `/api/v1/mpesa/validation`
- **Flow**: Initiate STK push → user enters PIN → callback received → transaction recorded
- **Status polling**: For async confirmation

### Stripe Integration

- **Checkout sessions**: For subscription payments
- **Webhook handling**: `/api/v1/stripe/webhook`
- **Subscription management**: Create, update, cancel via Stripe
- **Event logging**: All webhook events stored in `webhook_logs`

### Financial Reports

- Daily transaction summaries
- Revenue by payment method
- Outstanding balances
- Refund tracking
- Subscription revenue
- Exportable to PDF/CSV

---

## 11. Communication System

### Internal Messaging

| Type | Description |
|---|---|
| **direct** | One-to-one message |
| **group** | To multiple selected recipients |
| **broadcast** | To all users |
| **department** | To all users in a department |
| **program** | To all users in a program |

### Message Features

- **Priorities**: low, normal, high
- **Scheduling**: Send at future date/time
- **Threading**: Parent message → replies
- **Forwarding**: Forward messages to other users
- **Archiving**: Archive/unarchive messages
- **Read tracking**: Read time, delivery status
- **File attachments**: Multiple files per message
- **Templates**: Reusable message templates with variable substitution

### Announcements

- Title, content, type (info, warning, emergency)
- Status: draft, published, archived
- Publish/expires dates
- Created by admin/librarian

### Bulletins

- Department-specific notices
- Status: draft, published
- Department-scoped visibility

### Events

- Title, description, location, start/end dates
- Event types: workshop, seminar, book_fair, meeting, other
- Status: upcoming, ongoing, completed, cancelled

### Templates

- Reusable message templates
- Variable substitution: `{{name}}`, `{{date}}`, etc.
- Category organization
- CRUD management

### Analytics

- Message delivery rates
- Read rates by recipient
- Event engagement tracking
- Communication frequency reports

---

## 12. Notifications

### In-App Notifications (`InAppNotification`)

| Type | Trigger |
|---|---|
| `overdue` | Book becomes overdue |
| `due_reminder` | Approaching due date |
| `hold_available` | Reserved book returned |
| `fine_assessed` | New fine created |
| `reservation` | Reservation confirmed |
| `return_reminder` | Approaching due date |
| `system` | System announcements |

### Email Notifications (9 Mailable Classes)

| Mail Class | Purpose |
|---|---|
| `DueDateReminder` | Reminder before book due date |
| `ExpirationNotice` | Membership/subscription expiring |
| `NewReadingAssignment` | Teacher assigns reading |
| `NotificationMail` | Generic notification wrapper |
| `OverdueNotice` | Book is overdue |
| `PaymentConfirmation` | Payment received |
| `RenewalReminder` | Subscription renewal due |
| `SubscriptionActivation` | Subscription activated |
| `WelcomeCredentials` | New user welcome with credentials |

### WhatsApp Integration

- `WhatsAppService` sends messages via WhatsApp Business API
- Used for: due reminders, overdue notices, payment confirmations
- Fallback to email if WhatsApp delivery fails

### Push Notifications (Web Push / VAPID)

- VAPID key-based push via `minishlink/web-push`
- `PushSubscription` model stores browser push endpoints
- `PushNotificationService` handles broadcast and targeted pushes
- User preferences for push notification types
- API endpoints: subscribe, unsubscribe, manage subscriptions

### Notification Preferences

- Per-user toggles for each notification channel
- Per-type preferences (which types via which channel)

---

## 13. Reading Assignments

### Assignment Flow

1. **Teacher creates assignment**: Selects student(s), book or digital asset, sets due date
2. **Student notified**: Email + in-app notification
3. **Student views assignment**: Marks as viewed
4. **Progress tracking**: Status updates as student works through material
5. **Completion**: Student marks complete, timestamp recorded

### Assignment Types

| Field | Options |
|---|---|
| **type** | assignment, recommendation |
| **status** | pending, in_progress, completed, overdue, cancelled |

### Features

- Group assignments by program or department
- Book or digital asset targets
- Teacher dashboard for tracking student progress
- Student dashboard for viewing assignments
- Overdue detection and notification
- Completion statistics

---

## 14. Subscriptions

### Plan Structure

| Field | Description |
|---|---|
| **name** | Plan display name |
| **type** | individual, school |
| **billing_cycle** | monthly, yearly |
| **price** | Cost amount |
| **currency** | KES, USD |
| **features** | JSON array of capabilities |
| **trial_period_days** | Free trial length |
| **grace_period_days** | Grace period after expiry |

### Subscription Lifecycle

1. **Trial**: Free trial period (configurable days)
2. **Active**: Paid subscription, full access
3. **Grace period**: Subscription expired, grace period active
4. **Suspended**: Grace period expired, limited access
5. **Cancelled**: User-initiated cancellation
6. **Expired**: Final state after all periods

### Capability-Gated Access

Subscription enforces access to capabilities:
- `view` — Browse catalog and digital assets
- `borrow` — Borrow physical books
- `upload` — Upload digital assets
- `download` — Download digital assets
- `messaging` — Send/receive messages

### Payment Integration

- Stripe Checkout for subscription payments
- M-Pesa as alternative payment
- Webhook-based status updates
- Auto-renewal option

### Admin Revenue Dashboard

- Active subscriptions count
- Revenue by period
- MRR/ARR metrics
- Churn tracking
- Plan popularity

---

## 15. Reports & Analytics

### Catalog Reports

- Book inventory summary
- Books by category/author/publisher
- New additions over time
- Most popular books
- Available vs. borrowed copies

### Circulation Reports

- Borrowing activity summary
- Overdue book list with patron details
- Fine collection summary
- Renewal frequency
- Reservation statistics
- Peak borrowing periods

### Member Reports

- Member registration trends
- Active vs. inactive members
- Membership type distribution
- Department-wise breakdown
- Borrowing patterns per member

### Digital Library Reports

- Download statistics by asset
- Reading completion rates
- Most viewed assets
- Storage usage
- Upload trends

### Financial Reports

- Daily transaction summary
- Revenue by payment method
- Outstanding fines
- Subscription revenue
- Refund summary
- Monthly/yearly comparisons

### Export Formats

- **PDF**: Branded reports with charts (DomPDF)
- **CSV**: Raw data export (PHPSpreadsheet)
- **Scheduled**: Async generation via `GenerateReportJob`

### Document Verification Reports

- Verification count per document
- Audit trail of all verifications
- Revoked document tracking

---

## 16. Settings & Administration

### General Settings

- Site name, tagline, logo
- Contact information
- Timezone, locale
- School branding (colors, header/footer)

### Circulation Settings

- Default loan period (days)
- Maximum renewals allowed
- Fine rates (daily, lost, damage)
- Reservation expiry period
- Grace period for returns

### Digital Library Settings

- Maximum upload size
- Allowed file types
- Download rate limits
- Storage path configuration

### Notification Settings

- Email configuration (SMTP)
- WhatsApp API credentials
- Push notification VAPID keys
- Notification scheduling preferences

### Security Settings

- 2FA enforcement toggle
- Session idle timeout
- Max concurrent sessions
- Password policy (min length, complexity)
- IP whitelist/blacklist

### Appearance Settings

- Theme selection (light/dark)
- Custom CSS
- Logo and favicon
- Color scheme

### System Management

- **Audit log viewer**: Browse all activity logs
- **System log viewer**: Application and error logs
- **Queue monitor**: View queued/failed jobs
- **Cache management**: Clear specific cache stores
- **Storage management**: Monitor disk usage
- **Backup settings**: Automated backup configuration
- **Maintenance mode**: Toggle site maintenance

### User Management

- User CRUD with role assignment
- Bulk user import
- Active/inactive toggle
- Session management per user

### Role Management

- Create/edit/delete roles
- Assign granular permissions
- Role hierarchy enforcement
- Permission audit trail

### Department & Program Management

- Department CRUD (name, code, description)
- Program CRUD (linked to departments)
- User assignment to departments/programs

### Landing Page Management

- Hero section content
- Featured content
- Features list
- Why choose us section
- Testimonials
- Newsletter subscribers
- Auth carousel images
- SEO meta tags

---

## 17. Document Verification

### How It Works

1. **Document generated**: System creates a document (report, certificate, receipt)
2. **Verification record**: `DocumentVerification` created with unique `DOC-YYMMDD-XXXXXXXX` ID
3. **QR code generated**: Encodes verification URL
4. **Verification tracking**: Count incremented on each lookup

### Verification Flow

1. Anyone visits `/verify/document/{id?}`
2. Enters document ID or scans QR code
3. System shows: document title, type, generation date, verification count
4. Admin can **revoke** documents (marks as revoked, blocks verification)

### Supported Document Types

- Reports (catalog, circulation, financial)
- Certificates
- Membership documents
- Any generated PDF

---

## 18. API Reference

### Base URL

```
/api/v1
```

### Authentication

- **Token-based**: Laravel Sanctum
- **Header**: `Authorization: Bearer {token}`
- **Rate limiting**: Configurable per endpoint

### Endpoint Groups

#### Public (No Auth)

| Method | Endpoint | Description |
|---|---|---|
| POST | `/login` | User login |
| POST | `/register` | User registration |
| POST | `/forgot-password` | Password reset request |
| POST | `/reset-password` | Password reset |
| POST | `/2fa/verify` | 2FA verification |
| GET | `/books/search` | Search books |
| GET | `/vapid-key` | Get VAPID public key |
| POST | `/mpesa/callback` | M-Pesa callback |
| POST | `/mpesa/validation` | M-Pesa validation |
| POST | `/stripe/webhook` | Stripe webhook |

#### Authenticated

**Auth & Profile**
| Method | Endpoint | Description |
|---|---|---|
| POST | `/logout` | Logout |
| GET | `/user` | Current user |
| POST | `/refresh` | Refresh token |
| POST | `/change-password` | Change password |
| GET | `/profile` | Get profile |
| PUT | `/profile` | Update profile |
| POST | `/profile/avatar` | Upload avatar |

**2FA**
| Method | Endpoint | Description |
|---|---|---|
| POST | `/2fa/enable` | Enable 2FA |
| POST | `/2fa/verify-setup` | Verify 2FA setup |
| POST | `/2fa/disable` | Disable 2FA |

**Dashboard**
| Method | Endpoint | Description |
|---|---|---|
| GET | `/dashboard` | Dashboard stats |

**Catalog** (Read-only)
| Method | Endpoint | Description |
|---|---|---|
| GET | `/books` | List books |
| GET | `/books/{id}` | Book detail |
| GET | `/categories` | List categories |
| GET | `/authors` | List authors |
| GET | `/publishers` | List publishers |

**Circulation**
| Method | Endpoint | Description |
|---|---|---|
| GET | `/loans/active` | Active loans |
| GET | `/loans/history` | Loan history |
| GET | `/loans/overdue` | Overdue loans |
| POST | `/loans/issue` | Issue book |
| POST | `/loans/return` | Return book |
| POST | `/loans/renew` | Renew loan |
| GET | `/reservations` | My reservations |
| POST | `/reservations` | Create reservation |
| DELETE | `/reservations/{id}` | Cancel reservation |
| GET | `/fines` | My fines |
| POST | `/fines/{id}/pay` | Pay fine |

**Library Card**
| Method | Endpoint | Description |
|---|---|---|
| GET | `/library-card` | My card |
| GET | `/library-card/qr` | QR code image |
| GET | `/library-card/barcode` | Barcode image |
| GET | `/library-card/pdf` | Card as PDF |

**Digital Library**
| Method | Endpoint | Description |
|---|---|---|
| GET | `/digital-assets` | List assets |
| GET | `/digital-assets/{id}` | Asset detail |
| POST | `/digital-assets/{id}/download` | Download asset |
| GET | `/digital-categories` | List categories |
| GET | `/reading-history` | My reading history |
| GET | `/recommendations` | Get recommendations |

**Messaging**
| Method | Endpoint | Description |
|---|---|---|
| GET | `/messages/inbox` | Inbox |
| GET | `/messages/sent` | Sent messages |
| GET | `/messages/unread-count` | Unread count |
| POST | `/messages/send` | Send message |
| POST | `/messages/{id}/reply` | Reply |
| POST | `/messages/{id}/forward` | Forward |
| POST | `/messages/{id}/archive` | Archive |
| GET | `/messages/search` | Search messages |
| GET | `/templates` | List templates |
| POST | `/templates` | Create template |
| PUT | `/templates/{id}` | Update template |
| DELETE | `/templates/{id}` | Delete template |
| POST | `/templates/{id}/apply` | Apply template |

**Notifications**
| Method | Endpoint | Description |
|---|---|---|
| GET | `/notifications` | List notifications |
| POST | `/notifications/{id}/read` | Mark read |
| POST | `/notifications/read-all` | Mark all read |
| GET | `/notifications/unread-count` | Unread count |

**Push Notifications**
| Method | Endpoint | Description |
|---|---|---|
| POST | `/push/subscribe` | Subscribe |
| DELETE | `/push/unsubscribe` | Unsubscribe |
| DELETE | `/push/unsubscribe-all` | Unsubscribe all |
| GET | `/push/subscriptions` | List subscriptions |
| GET | `/push/preferences` | Get preferences |
| PUT | `/push/preferences` | Update preferences |

**Content**
| Method | Endpoint | Description |
|---|---|---|
| GET | `/announcements` | List announcements |
| GET | `/events` | List events |
| GET | `/bulletins` | List bulletins |

**Assignments**
| Method | Endpoint | Description |
|---|---|---|
| GET | `/assignments` | My assignments (student) |
| POST | `/assignments/{id}/view` | Mark viewed |
| POST | `/assignments/{id}/complete` | Mark completed |
| GET | `/teacher/assignments` | Teacher's assignments |
| POST | `/teacher/assignments` | Create assignment |
| PUT | `/teacher/assignments/{id}` | Update assignment |
| DELETE | `/teacher/assignments/{id}` | Delete assignment |
| GET | `/teacher/assignments/{id}/students` | View student progress |

**Subscriptions**
| Method | Endpoint | Description |
|---|---|---|
| GET | `/subscription/plans` | List plans |
| GET | `/subscription/my` | My subscription |
| POST | `/subscription/store` | Create subscription |
| POST | `/subscription/cancel` | Cancel subscription |

**Payments**
| Method | Endpoint | Description |
|---|---|---|
| GET | `/payments` | Payment history |
| GET | `/payments/{id}` | Payment detail |

**Reviews**
| Method | Endpoint | Description |
|---|---|---|
| GET | `/reviews` | List reviews |
| POST | `/reviews` | Create review |
| GET | `/reviews/{id}` | Review detail |
| GET | `/reviews/my` | My reviews |

**Programs & Departments**
| Method | Endpoint | Description |
|---|---|---|
| GET | `/programs` | List programs |
| GET | `/departments` | List departments |
| GET | `/students` | List students |

**Reports**
| Method | Endpoint | Description |
|---|---|---|
| GET | `/reports/reading-summary` | Reading summary |
| GET | `/reports/loan-history` | Loan history report |
| GET | `/reports/fine-history` | Fine history report |

---

## 19. Mobile App

### Technology

- **Framework**: Flutter 3.12 (Dart)
- **Architecture**: BLoC (flutter_bloc) + Clean Architecture
- **Routing**: go_router
- **Networking**: Dio
- **Local Storage**: Hive + flutter_secure_storage
- **QR/Barcode**: mobile_scanner + qr_flutter
- **Biometrics**: local_auth
- **Firebase**: core, crashlytics, messaging
- **Notifications**: firebase_messaging + flutter_local_notifications

### Feature Modules (22)

| Module | Description |
|---|---|
| `assignments` | View and complete reading assignments |
| `auth` | Login, registration, 2FA |
| `authors` | Browse authors |
| `bookmarks` | Saved books |
| `books` | Book catalog browsing |
| `communication` | Messaging hub |
| `dashboard` | Home dashboard |
| `digital_library` | Digital asset browsing and reading |
| `finance` | Payment history |
| `fines` | Fine management |
| `lecturer` | Lecturer-specific features |
| `library_card` | Digital library card with QR |
| `loans` | Active loans and history |
| `messaging` | Direct messaging |
| `notifications` | Notification center |
| `profile` | User profile management |
| `publishers` | Browse publishers |
| `reports` | Personal reports |
| `reservations` | Book reservations |
| `scanner` | QR/barcode scanner |
| `subscriptions` | Subscription management |
| `teacher_assignments` | Assignment creation (teachers) |

### Platforms

- Android
- iOS
- Windows
- Web

---

## 20. Deployment

### Docker Setup

**Multi-stage Dockerfile**:
1. **Stage 1 (frontend)**: Node 22-alpine → `npm ci && npm run build`
2. **Stage 2 (vendor)**: Composer 2 → `composer install --no-dev --optimize-autoloader`
3. **Stage 3 (app)**: PHP 8.3-fpm-alpine + nginx + supervisor

**docker-compose.yml**:
- `app`: Port 80, PHP-FPM + nginx
- `database`: MySQL 8.0 with health check
- `cache`: Redis 7-alpine with health check
- Named volumes: storage, public, db, cache
- Bridge network: `ollmchs-network`

### Environment Requirements

**PHP Extensions**: pdo_mysql, pdo_pgsql, pdo_sqlite, gd, zip, mbstring, opcache, bcmath
**Timezone**: Africa/Nairobi
**Upload limit**: 100MB
**OPcache**: Enabled for production

### Deployment Scripts

| Script | Purpose |
|---|---|
| `deploy.sh` | Full deployment (build, migrate, seed, optimize) |
| `rollback.sh` | Rollback to previous version |
| `health-check-validation.sh` | Verify deployment health |

### Artisan Commands

| Command | Purpose |
|---|---|
| `circulation:check-overdue` | Mark overdue, assess fines, notify |
| `circulation:assess-overdue-fines` | Batch fine assessment |
| `circulation:expire-reservations` | Expire old reservations |
| `subscriptions:check-expiry` | Check membership expiry |
| `backups:create` | Create database backup |
| `backups:clean-old` | Remove old backups |
| `vapid:generate` | Generate Web Push keys |
| `messages:send-scheduled` | Send queued messages |

### Queued Jobs

| Job | Purpose |
|---|---|
| `CheckOverdueBorrowsJob` | Batch mark overdue borrows |
| `SendDueReminderJob` | Send due date reminders |
| `SendOverdueNotificationJob` | Send overdue notifications |
| `GenerateReportJob` | Async report generation |
| `ProcessSubscriptionRenewals` | Auto-renew subscriptions |

---

## 21. Testing

### Test Coverage

| Category | Count | Files |
|---|---|---|
| **Auth tests** | 13 | 2FA flows, registration, sessions, concurrent limits |
| **API tests** | 7 | Catalog, circulation, content, cards, messaging, permissions, push |
| **Catalog tests** | 2 | General catalog, bulk upload |
| **Circulation tests** | 3 | General circulation, overdue+fine, fine calculation |
| **Finance tests** | 3 | Finance module, M-Pesa service, webhooks |
| **Member tests** | 1 | Library card full lifecycle |
| **Digital Library tests** | 1 | Digital library service |
| **Settings tests** | 5 | Admin settings, permissions, navigation, sidebar, optimization |
| **Other tests** | 7 | Profile, health check, comprehensive system, example |
| **Unit tests** | 3 | Document service, reporting service, example |

### Factories (13)

Book, BookCopy, BorrowRecord, DigitalAsset, Fine, InAppNotification, Member, Message, Plan, Reservation, Subscription, Transaction, User

### Seeders (12)

Database, RolesAndPermissions, Catalog, Circulation, DigitalLibrary, DummyData, Features, Finance, NewsletterSubscriber, Subscription, Testimonial, WhyChooseUs

---

## 22. Data Model Reference

### 42 Eloquent Models

#### Core Models (8)

| Model | Purpose |
|---|---|
| User | Central user with roles, profile, 2FA, Spatie permissions |
| AccessLevel | Access level definitions for digital assets |
| Department | Academic departments |
| Program | Academic programs within departments |
| DocumentVerification | Document authenticity verification with QR codes |
| DownloadLog | Download tracking and rate limiting |
| LoginLog | Login audit trail |
| PushSubscription | Web Push (VAPID) subscriptions |

#### Catalog Module (6)

| Model | Purpose |
|---|---|
| Book | Library book entry |
| BookCopy | Physical copy of a book |
| Author | Book author |
| Category | Hierarchical book classification |
| Publisher | Book publisher |
| Subject | Subject tag for books |
| BookReview | User review of a book |

#### Circulation Module (3)

| Model | Purpose |
|---|---|
| BorrowRecord | Book borrowing transaction |
| Fine | Fine against a borrowing |
| Reservation | Book reservation |

#### Members Module (2)

| Model | Purpose |
|---|---|
| Member | Library member profile |
| LibraryCard | Physical/digital library card |

#### Finance Module (5)

| Model | Purpose |
|---|---|
| Transaction | Payment transaction |
| Invoice | Generated invoice |
| Receipt | Payment receipt |
| MpesaTransaction | M-Pesa payment record |
| Report | Generated report file |

#### Digital Library Module (5)

| Model | Purpose |
|---|---|
| DigitalAsset | Digital file/document |
| DigitalAssetCategory | Digital asset classification |
| Citation | Formatted citation for an asset |
| ReadingHistory | User reading progress |
| Recommendation | AI-generated recommendation |

#### Communication Module (8)

| Model | Purpose |
|---|---|
| Message | Internal message |
| MessageRecipient | Message delivery record |
| MessageAttachment | File attachment |
| MessageTemplate | Reusable template |
| Announcement | School-wide announcement |
| Bulletin | Department bulletin |
| Event | Calendar event |
| NotificationLog | Notification delivery log |
| CommunicationAnalytic | Communication metrics |

#### Subscriptions Module (3)

| Model | Purpose |
|---|---|
| Plan | Subscription plan definition |
| Subscription | User subscription record |
| WebhookLog | Payment gateway webhook log |

#### Settings Module (6)

| Model | Purpose |
|---|---|
| Setting | Key-value system setting |
| Feature | Landing page feature |
| Testimonial | User testimonial |
| WhyChooseUs | Landing page "Why choose us" item |
| NewsletterSubscriber | Newsletter subscriber |
| AuthCarouselImage | Auth page carousel image |

#### Notifications Module (1)

| Model | Purpose |
|---|---|
| InAppNotification | In-app notification |

#### Assignments Module (1)

| Model | Purpose |
|---|---|
| ReadingAssignment | Teacher-student reading assignment |

---

## Summary

The OLLMCHS Library Management System is a **complete, enterprise-grade library platform** built for a Kenyan health sciences college. It features:

- **42 database tables** across 14 domain modules
- **70+ API endpoints** for the Flutter mobile companion
- **10 user roles** with granular Spatie RBAC
- **Multi-channel notifications** (in-app, email, WhatsApp, push, SMS)
- **Dual payment gateways** (M-Pesa + Stripe)
- **Digital asset management** with in-browser reader and AI recommendations
- **Subscription-based access control** with capability enforcement
- **Document verification** with QR codes and public lookup
- **Comprehensive reporting** with PDF/CSV export
- **PWA support** with offline capability
- **Full Docker deployment** with multi-stage builds
- **45 test files** covering auth, API, circulation, finance, and settings
