# OLLMCHS Library Management System

**Our Lady of Lourdes Mutomo College of Health Sciences — Enterprise Library Platform**

A full-featured, enterprise-grade library management system built for OLLMCHS, a health sciences college in Kenya. Includes a Laravel web application, RESTful API, and a companion Flutter mobile app (Student & Lecturer APKs).

**Project started:** June 9, 2026

---

## Table of Contents

1. [Tech Stack](#tech-stack)
2. [Architecture](#architecture)
3. [Modules](#modules)
4. [Authentication & Authorization](#authentication--authorization)
5. [Database Schema](#database-schema)
6. [API Reference](#api-reference)
7. [Scheduled Tasks](#scheduled-tasks)
8. [Notifications System](#notifications-system)
9. [Payment Integration](#payment-integration)
10. [PWA & Offline Support](#pwa--offline-support)
11. [Mobile App (Flutter)](#mobile-app-flutter)
12. [Deployment](#deployment)
13. [Security](#security)
14. [Testing](#testing)
15. [Environment Configuration](#environment-configuration)
16. [Quick Start](#quick-start)

---

## Tech Stack

### Backend

| Component | Technology |
|-----------|-----------|
| Language | PHP 8.3+ |
| Framework | Laravel 13.8 |
| Livewire | 3.6.4+ |
| Volt | 1.7.0 (functional API) |
| API Auth | Laravel Sanctum 4.3 |
| RBAC | Spatie Permission 7.4 |
| Audit Trail | Spatie Activity Log 4.12 |
| PDF | DomPDF |
| Payments | M-Pesa (Daraja API) + Stripe |
| 2FA | Google2FA |
| Web Push | VAPID (minishlink/web-push 10.1) |
| QR Codes | simplesoftwareio/simple-qrcode 4.2 |
| Spreadsheets | phpoffice/phpspreadsheet |

### Frontend

| Component | Technology |
|-----------|-----------|
| Build | Vite 8.0 |
| CSS | Tailwind CSS 3.1+ |
| Dark Mode | Tailwind `class` strategy |
| PWA | vite-plugin-pwa 1.3.0 |
| Image Crop | CropperJS 2.1.1 |
| Service Worker | Workbox 7.4.0 |

### Mobile App (Flutter)

| Component | Technology |
|-----------|-----------|
| SDK | Flutter 3.12.2+ |
| State | flutter_bloc 9.1.0 |
| HTTP | dio 5.7.0 |
| Routing | go_router 15.1.2 |
| Local Storage | hive_flutter, flutter_secure_storage |
| Push | firebase_messaging 15.2.4 |
| QR/Barcode | mobile_scanner 6.0.7 |
| Biometrics | local_auth 2.3.0 |
| Crashlytics | firebase_crashlytics 4.3.0 |
| Platforms | Android, iOS, Web, Windows |

### Infrastructure

| Component | Technology |
|-----------|-----------|
| Database | MySQL 8.0 / PostgreSQL 16 / SQLite (dev) |
| Cache | Redis 7 / Database |
| Queue | Database / Redis |
| Web Server | Nginx + PHP-FPM |
| Process Mgr | Supervisor |
| Container | Docker (multi-stage) |
| CI/CD | GitHub Actions |
| Opcache | Enabled, 128MB |

---

## Architecture

### Module-Based Structure

Every domain lives in `app/Modules/{ModuleName}/` with its own routes, Livewire components, views, models, services, migrations, and provider:

```
app/Modules/
├── AI/                  # Recommendation engine, smart tagging
├── API/                 # RESTful API (controllers, routes, services)
├── Assignments/         # Reading assignments (teacher/student)
├── Auth/                # Authentication, 2FA, registration
├── Catalog/             # Books, authors, publishers, categories
├── Circulation/         # Borrowing, returns, reservations, fines
├── Communication/       # Messaging, announcements, WhatsApp, SMS
├── DigitalLibrary/      # Digital assets, reading history, citations
├── Finance/             # Transactions, invoices, M-Pesa, Stripe
├── Members/             # Member profiles, library cards
├── Notifications/       # In-app notifications
├── Reports/             # Report dashboards with PDF/CSV export
├── Roles/               # Role/permission management
├── Settings/            # 47 Livewire settings components
├── Shared/              # Shared traits, helpers, interfaces
└── Subscriptions/       # Plans, Stripe, renewal processing
```

### Key Design Patterns

- **Livewire + Volt:** Full-page components with Volt functional API for CRUD; class-based components for complex interactions
- **Services Layer:** Business logic in service classes (`BorrowingService`, `FineCalculationService`, `DocumentService`, `ReportingService`, `SettingsService`)
- **Settings Caching:** All settings cached via `SettingsService::cached()` with 24-hour TTL
- **Activity Logging:** Spatie Activity Log on all critical operations

---

## Modules

### Catalog

Full book management with ISBN, Dewey Decimal, LC classification, and hierarchical categories.

| Feature | Details |
|---------|---------|
| Books | CRUD with ISBN, title, subtitle, slug, description, language, pages, publication year, edition, volume, series, cover image, condition, status, price, tags, featured toggle |
| Book Copies | Individual copy tracking with barcode, RFID tag, shelf location, status (available/borrowed/lost/damaged) |
| Authors | Biography, birth/death dates, nationality, photo, website |
| Publishers | Address, phone, email, website |
| Categories | Hierarchical (parent-child), sort order |
| Subjects | Independent taxonomy |
| Reviews | User-submitted with rating, admin approval workflow |
| Bulk Upload | CSV/Excel import of books |
| Search | Full-text search on title/description; LIKE search on ISBN, authors, publisher, subjects |
| Filtering | By category, author, publisher, subject, year |
| Inventory | Inventory management view |
| New Arrivals | Recently added books view |
| Featured Books | Toggle-able for landing page display |

### Circulation

| Feature | Details |
|---------|---------|
| Borrowing | Role-based limits (student: 5, lecturer: 10, librarian: 15, admin: 20, super-admin: 100) |
| Durations | Student: 14 days, Lecturer: 30 days, Others: 21 days |
| Returns | Process returns with condition tracking, overdue fine assessment |
| Renewals | Up to 2 renewals, 7-day periods, no renewal for overdue items |
| Overdue Detection | Automatic daily job at 02:00 |
| Reservations | Place holds when no copies available, auto-expire, notify when available |
| Waitlists | Waitlist management for popular titles |
| Override | Admin override for due dates |
| Lost/Damaged | Mark with automatic fine assessment |
| Fines | Overdue: KES 50/day, Lost: KES 1500 + overdue, Damage: KES 500 (configurable) |
| Audit Trail | Every borrow/return logged via Activity Log |

### Members

| Feature | Details |
|---------|---------|
| Profiles | Auto-generated member ID (MEM-XXXXX), full CRUD with department, program, class |
| Library Cards | Auto-generated card numbers (OLLMCHS-{YEAR}-{SEQUENCE}), QR code, barcode, PDF export |
| Card Verification | Public QR code scan route (`/verify/card/{cardNumber}`) |
| Bulk Import | CSV import with template download |
| Membership | Request/approval workflow, suspension management |
| Expiry | Daily scheduled job checks membership expiry |

### Digital Library

| Feature | Details |
|---------|---------|
| Assets | Upload PDFs, docs, presentations, videos, audio, datasets, ebooks |
| Classification | Automatic file type by MIME type |
| Access Levels | Public, restricted, private |
| Download Tracking | Per-asset and per-user download logs |
| View Tracking | View counts, reading history per user |
| Reading Progress | Track progress percentage, last page, completion |
| Recommendations | AI-powered engine based on reading history, categories, subjects |
| Citations | Generate in multiple styles (APA, etc.) |
| Smart Tags | AI-powered auto-tagging |
| Search & Filter | By type, category, access level, with sorting |
| Linked Books | Optional link to physical book catalog |

### Finance

| Feature | Details |
|---------|---------|
| Transactions | Auto-generated transaction numbers |
| Types | Fine payment, lost book fine, damage fine, subscription payment/renewal |
| Payment Methods | Cash, M-Pesa, Stripe |
| Invoices | Auto-generated, PDF download, email sending |
| Receipts | Auto-generated, PDF download, email sending |
| M-Pesa | STK Push via Daraja API (sandbox/production), callback handling, validation, stale cleanup |
| Stripe | Checkout sessions, webhook handling |
| Analytics | Revenue by plan, monthly breakdown, growth rates |
| Reports | Financial reports with PDF/CSV export |

### Communication

| Feature | Details |
|---------|---------|
| Messaging | Direct, group, broadcast, department, program-scoped |
| Message Features | Priority levels, attachments, drafts, scheduled sending, reply, reply-all, forward, archive |
| Templates | Create, edit, apply variable-based templates |
| Announcements | CRUD for library announcements |
| Events | Library events management |
| Bulletins | Library bulletins |
| Analytics | Delivery tracking, communication metrics |

### Subscriptions

| Feature | Details |
|---------|---------|
| Plans | Individual/School, Monthly/Yearly, configurable pricing |
| Lifecycle | pending -> active -> expired -> grace period -> suspended |
| Trial | Configurable trial days, auto-create on registration |
| Payment | M-Pesa and Stripe |
| Auto-Renewal | Configurable with scheduled processing |
| Grace Period | Configurable after expiry |
| Revenue | Dashboard with revenue by plan, growth rate |
| Enforcement | Middleware gates features behind active subscriptions |

### Assignments

| Feature | Details |
|---------|---------|
| Teacher | Create, edit, delete reading assignments |
| Student | View and submit completed assignments |
| Groups | Support for group-based assignments |
| Tracking | Submission tracking with timestamps |

### Reports

| Feature | Details |
|---------|---------|
| Dashboard | Overview with key metrics |
| Catalog | Book statistics, collection analysis |
| Circulation | Borrow/return statistics, overdue analysis |
| Members | Membership statistics |
| Digital Library | Download/usage statistics |
| Export | PDF and CSV with watermark support |

### Settings (47 Livewire Components)

- **General:** Site name, description, address, phone, email, opening hours
- **Circulation:** Max borrow days, items, renewals, fine rates, grace periods
- **Digital Library:** Upload size, allowed file types, auto-approve, limits
- **Notifications:** Email, SMS, WhatsApp toggle, due reminders, overdue alerts
- **Security:** Password requirements, login attempts, session timeout, 2FA enforcement
- **Email:** SMTP configuration
- **Backup:** Auto backup, frequency, retention, location
- **Localization:** Language, timezone (Africa/Nairobi), date/time format, currency (KES)
- **Appearance:** Branding, theming
- **Landing Page:** 50+ configurable settings (hero, SEO, newsletter, features, stats, social links, footer)
- **Auth Carousel:** Login page carousel images
- **Feature List:** CRUD for landing page features
- **Why Choose Us:** CRUD for "Why Choose Us" section
- **Testimonials:** CRUD with approval workflow
- **Newsletter:** Subscriber management
- **Programs/Departments:** Academic programs and departments
- **Access Levels:** Digital library access levels
- **User Management:** CRUD with role assignment
- **Role Management:** CRUD with permission assignment
- **Audit Log:** Spatie Activity Log viewer
- **System Logs:** Application log viewer
- **System Health:** App, database, cache, storage status
- **Queue Monitor:** Queued jobs monitor
- **Cache Manager:** Clear/manage cache
- **Storage Manager:** Disk usage monitoring
- **AI Settings:** Configure AI features

---

## Authentication & Authorization

### Authentication

- **Login/Registration:** Laravel Volt functional components
- **Student Registration:** Separate flow (`/register/student`)
- **Email Verification:** Signed links with throttle
- **Password Reset:** Token-based reset
- **2FA:** Google2FA with QR code, recovery codes, enable/disable/verify
- **Biometric (Mobile):** Fingerprint and Face ID via `local_auth`
- **Session Security:** Encrypted sessions, secure cookies, configurable timeout
- **Rate Limiting:** 6 req/min on auth routes; 30 req/min for local dev
- **Login Logs:** Tracks `last_login_at` and `last_login_ip`

### Roles (11)

| Role | Description |
|------|-------------|
| `super-admin` | Full system access |
| `admin` | Administrative access |
| `librarian` | Library operations |
| `assistant-librarian` | Limited library operations |
| `student` | Student access |
| `lecturer` | Lecturer access |
| `finance-officer` | Financial operations |
| `department-head` | Department management |
| `ict-admin` | System configuration |
| `staff` | General staff access |
| `guest` | Read-only access |

### Permissions

100+ granular permissions managed via Spatie Permission, covering every module and action.

---

## Database Schema

67 migration files across all modules. Key tables:

### Core

| Table | Purpose |
|-------|---------|
| `users` | User accounts with 2FA, avatar, department, program |
| `members` | Member profiles with auto-generated IDs |
| `departments` | Academic departments |
| `programs` | Academic programs |
| `library_cards` | Generated library cards with QR/barcode |

### Catalog

| Table | Purpose |
|-------|---------|
| `books` | Book records with full metadata |
| `book_copies` | Individual copy tracking |
| `authors` | Author profiles |
| `publishers` | Publisher information |
| `categories` | Hierarchical categories |
| `subjects` | Subject taxonomy |
| `book_author` | Book-author pivot |
| `book_subject` | Book-subject pivot |
| `book_reviews` | User reviews |

### Circulation

| Table | Purpose |
|-------|---------|
| `borrow_records` | Borrow/return tracking |
| `reservations` | Book holds |
| `fines` | Fine records |
| `circulation_settings` | Configurable circulation settings |

### Digital Library

| Table | Purpose |
|-------|---------|
| `digital_assets` | Digital resource records |
| `digital_asset_categories` | Asset categories |
| `reading_histories` | Reading progress tracking |
| `recommendations` | AI recommendations |
| `citations` | Generated citations |
| `download_logs` | Download tracking |

### Finance

| Table | Purpose |
|-------|---------|
| `transactions` | All financial transactions |
| `invoices` | Generated invoices |
| `receipts` | Generated receipts |
| `mpesa_transactions` | M-Pesa payment records |
| `payment_logs` | Payment audit trail |

### Communication

| Table | Purpose |
|-------|---------|
| `messages` | Internal messages |
| `message_recipients` | Message delivery tracking |
| `message_attachments` | File attachments |
| `message_templates` | Reusable templates |
| `announcements` | Library announcements |
| `events` | Library events |
| `bulletins` | Library bulletins |
| `notification_logs` | Multi-channel delivery logs |
| `push_subscriptions` | Web push registrations |

### Subscriptions

| Table | Purpose |
|-------|---------|
| `plans` | Subscription plan definitions |
| `subscriptions` | User subscription records |
| `webhook_logs` | Payment webhook audit |

### System

| Table | Purpose |
|-------|---------|
| `settings` | Cached application settings |
| `activity_log` | Spatie audit trail |
| `login_logs` | Login audit |
| `sessions` | Session storage |
| `cache` | Cache storage |
| `jobs` | Queue jobs |

---

## API Reference

All endpoints prefixed with `/api/v1/`. Authenticated via Bearer token (Sanctum).

### Public Endpoints

| Method | Endpoint | Description | Throttle |
|--------|----------|-------------|----------|
| POST | `/auth/login` | Login | 6/min |
| POST | `/auth/register` | Register | 6/min |
| POST | `/auth/forgot-password` | Forgot password | 6/min |
| POST | `/auth/reset-password` | Reset password | 6/min |
| POST | `/auth/2fa/verify` | Verify 2FA | 10/min |
| POST | `/auth/2fa/verify-recovery` | Verify recovery code | 5/min |
| POST | `/mpesa/validation` | M-Pesa validation webhook | — |
| POST | `/mpesa/callback` | M-Pesa callback webhook | — |
| POST | `/stripe/webhook` | Stripe webhook | — |
| GET | `/books/search` | Public book search | 30/min |
| GET | `/push/vapid-key` | VAPID public key | — |

### Authenticated Endpoints

#### Authentication & Profile
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/logout` | Logout |
| GET | `/auth/user` | Current user |
| POST | `/auth/refresh` | Refresh token |
| POST | `/auth/change-password` | Change password |
| POST | `/auth/verify-email` | Verify email |
| POST | `/auth/resend-verification` | Resend verification |
| POST | `/auth/2fa/enable` | Enable 2FA |
| POST | `/auth/2fa/verify-setup` | Verify 2FA setup |
| POST | `/auth/2fa/disable` | Disable 2FA |
| GET/PUT | `/profile` | View/edit profile |
| POST | `/profile/avatar` | Upload avatar |
| GET | `/dashboard` | Dashboard data |

#### Catalog
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/books` | List books |
| GET | `/books/{book}` | Book detail |
| GET | `/categories` | List categories |
| GET | `/categories/{id}` | Category detail |
| GET | `/authors` | List authors |
| GET | `/authors/{id}` | Author detail |
| GET | `/publishers` | List publishers |
| GET | `/publishers/{id}` | Publisher detail |

#### Circulation
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/loans/active` | Active loans |
| GET | `/loans/history` | Loan history |
| GET | `/loans/overdue` | Overdue loans |
| POST | `/loans/issue` | Issue book |
| POST | `/loans/return` | Return book |
| GET | `/loans/{id}` | Loan detail |
| POST | `/loans/{id}/renew` | Renew loan |
| GET/POST/DELETE | `/reservations` | Manage reservations |
| GET | `/fines` | List fines |
| POST | `/fines/{id}/pay` | Pay fine |

#### Library Card
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/library-card` | View card |
| GET | `/library-card/qr-code` | QR code image |
| GET | `/library-card/barcode` | Barcode image |
| GET | `/library-card/pdf` | PDF download |

#### Digital Library
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/digital-assets` | List assets |
| GET | `/digital-assets/{asset}` | Asset detail |
| GET | `/digital-assets/{asset}/download` | Download asset |
| GET | `/digital-categories` | List categories |
| GET/PUT | `/reading-history` | Reading history |
| GET | `/recommendations` | AI recommendations |

#### Reports
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/reports/reading-summary` | Reading summary |
| GET | `/reports/loan-history` | Loan history |
| GET | `/reports/fine-history` | Fine history |

#### Messaging
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/messages` | List messages |
| GET | `/messages/inbox` | Inbox |
| GET | `/messages/sent` | Sent messages |
| GET | `/messages/search` | Search messages |
| GET | `/messages/unread-count` | Unread count |
| GET | `/messages/archived` | Archived |
| POST | `/messages/send` | Send message |
| GET/DELETE | `/messages/{id}` | View/delete message |
| POST | `/messages/{id}/reply` | Reply |
| POST | `/messages/{id}/forward` | Forward |
| POST | `/messages/{id}/mark-unread` | Mark unread |
| POST | `/messages/{id}/archive` | Archive |
| POST | `/messages/{id}/unarchive` | Unarchive |

#### Templates
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/templates` | List/create templates |
| GET/PUT/DELETE | `/templates/{id}` | View/update/delete |
| POST | `/templates/{id}/apply` | Apply template |

#### Notifications
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/notifications` | List notifications |
| POST | `/notifications/{id}/read` | Mark read |
| POST | `/notifications/read-all` | Mark all read |
| GET | `/notifications/unread-count` | Unread count |

#### Content
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/announcements` | List announcements |
| GET | `/announcements/{id}` | Announcement detail |
| GET | `/events` | List events |
| GET | `/events/{id}` | Event detail |
| GET | `/bulletins` | List bulletins |
| GET | `/bulletins/{id}` | Bulletin detail |

#### Assignments
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/assignments` | List assignments |
| GET | `/assignments/{id}` | Assignment detail |
| POST | `/assignments/{id}/submit` | Submit assignment |
| POST/PUT/DELETE | `/teacher/assignments` | Manage assignments |
| GET | `/teacher/assignments/{id}/progress` | View progress |

#### Programs & Users
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/programs` | List programs |
| GET | `/departments` | List departments |
| GET | `/students` | List students |

#### Subscriptions & Payments
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/subscription-plans` | List plans |
| GET | `/subscriptions/my` | My subscription |
| GET/POST | `/subscriptions` | List/create subscriptions |
| POST | `/subscriptions/{id}/cancel` | Cancel subscription |
| GET | `/payments` | List payments |
| GET | `/payments/{id}` | Payment detail |

#### Reviews
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/books/{bookId}/reviews` | Book reviews |
| GET/DELETE | `/books/reviews` | Manage reviews |
| POST | `/books/reviews/{id}` | Review actions |
| GET | `/my-reviews` | My reviews |

#### Push Notifications
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/push/subscribe` | Subscribe to push |
| POST | `/push/unsubscribe` | Unsubscribe |
| POST | `/push/unsubscribe-all` | Unsubscribe all |
| GET | `/push/subscriptions` | List subscriptions |
| GET/PUT | `/push/preferences` | Push preferences |

---

## Scheduled Tasks

| Schedule | Command/Job | Description |
|----------|------------|-------------|
| Daily 00:30 | `circulation:expire-reservations` | Expire old book reservations |
| Daily 00:45 | `circulation:assess-overdue-fines` | Auto-assess overdue fines |
| Daily 01:00 | `ProcessSubscriptionRenewals` | Process expired/trial/grace/renewal subscriptions |
| Daily 02:00 | `CheckOverdueBorrowsJob` | Check and update overdue borrow status |
| Daily 03:00 | `backup:database` | Automated database backup |
| Daily 03:30 | `backup:clean` | Clean old backups |
| Daily 04:00 | `sanctum:prune-expired --hours=48` | Prune expired API tokens |
| Daily 06:00 | `members:check-expiry` | Check membership expiry |
| Daily 08:00 | `send-overdue-notifications` | Send overdue notifications |
| Daily 08:00 | `send-subscription-expiry-notices` | 7-day subscription expiry warnings |
| Daily 09:00 | `send-due-date-reminders` | Due date reminders |
| Every 30 min | `clean-stale-mpesa-transactions` | Mark timed-out M-Pesa as failed |
| Every minute | `schedule:scheduled-messages` | Send scheduled messages |

---

## Notifications System

Multi-channel notification delivery:

| Channel | Implementation |
|---------|---------------|
| **In-App** | Real-time via `InAppNotification` model |
| **Email** | 9 Mailable classes (due reminders, overdue notices, payment confirmations, etc.) |
| **Push (Web)** | VAPID web push via Workbox service worker |
| **Push (Mobile)** | Firebase Cloud Messaging |
| **SMS** | Pluggable SMS provider |
| **WhatsApp** | WhatsApp Cloud API (Meta Graph API v22.0) |

### Notification Types

- Overdue notices
- Due date reminders
- Hold available alerts
- Fine assessments
- Borrow/return confirmations
- Messages
- Subscription expiry warnings (7-day advance)
- Subscription activation
- Assignment notifications
- Welcome credentials

### User Preferences

Configurable per-channel notification preferences (in_app, email, push, sms).

---

## Payment Integration

### M-Pesa (Safaricom Daraja API)

- STK Push for payments
- Sandbox/production environments
- Callback handling with validation
- Status query API
- Stale transaction cleanup (every 30 min)
- Phone number normalization (Kenyan format)

### Stripe

- Checkout sessions (subscription + one-time)
- Webhook handling for:
  - `checkout.session.completed`
  - `invoice.paid`
  - `invoice.payment_failed`
  - `customer.subscription.updated`
  - `customer.subscription.deleted`

### Fine Rates (Configurable)

| Type | Rate |
|------|------|
| Overdue | KES 50/day |
| Lost Book | KES 1500 + overdue |
| Damage | KES 500 |

---

## PWA & Offline Support

### Web App Manifest

- **Name:** OLLMCHS Library
- **Display:** Standalone with window-controls-overlay
- **Icons:** 72, 96, 128, 144, 152, 192, 384, 512px (any + maskable)
- **Shortcuts:** Dashboard, Books Catalog, Digital Library
- **Categories:** Education, Libraries, Books
- **Theme Color:** #153168

### Service Worker (Workbox 7.4.0)

- Precaching of build assets
- Network-first for API routes (10s timeout, 100 entries, 1hr TTL)
- Cache-first for images (50 entries, 7-day TTL)
- Offline fallback page (`offline.html`)

### JavaScript Features

- Dark/light theme toggle with localStorage persistence
- Mobile table-to-card responsive conversion
- Swipe-to-reveal gesture on list items
- Pull-to-refresh (mobile touch)
- CropperJS integration for image cropping
- Service worker registration with auto-update detection

---

## Mobile App (Flutter)

### Platforms

Android, iOS, Web, Windows

### Architecture

- **State:** BLoC pattern (flutter_bloc)
- **HTTP:** Dio with interceptors
- **Routing:** GoRouter with ShellRoute
- **Storage:** Hive (local) + flutter_secure_storage (credentials)
- **Notifications:** Firebase Cloud Messaging
- **Biometrics:** local_auth (fingerprint, Face ID)
- **QR/Barcode:** mobile_scanner
- **Crash Reporting:** Firebase Crashlytics

### Student App Features

Dashboard, Books, Book Details, Search, Categories, Recommendations, New Arrivals, Bookmarks, Downloads, Digital Library, Reader (PDF), Assignments, Loans, Loan History, Reservations, Renewals, Notifications, Messaging (Inbox/Compose/Reply), Profile, Settings, About, Help

### Lecturer App Features

Dashboard, Assignments (CRUD), Students, Analytics, Digital Library, Recommendations, Messaging (Broadcast), Announcements, Notifications, Profile, Settings, Reports

---

## Deployment

### Docker (Recommended)

```bash
docker compose build
docker compose up -d
```

**Services:**
- `app`: PHP 8.3 FPM Alpine + Nginx (port 80)
- `database`: MySQL 8.0 (port 3306)
- `cache`: Redis 7 Alpine (port 6379)

**Dockerfile:** Multi-stage build
1. Node 22 Alpine — frontend build
2. Composer 2 — PHP dependencies
3. PHP 8.3 FPM Alpine — runtime with Nginx + Supervisor

### Zero-Downtime Deploy

```bash
bash deploy.sh
```

- Timestamped release directories
- Symlinked shared .env, storage, public/storage
- Health check validation before/after symlink switch
- Keeps last 5 releases
- Rollback: `bash rollback.sh`

### Supervisor Processes

- nginx (daemon off)
- php-fpm
- queue-worker (2 processes, sleep=3, tries=3, max-time=3600)
- scheduler (schedule:work)

### CI/CD (GitHub Actions)

1. **Lint:** Laravel Pint code style
2. **Security:** Composer audit
3. **Test:** PHP 8.3, PHPUnit with coverage
4. **Frontend:** Node 22, npm build
5. **Docker:** Build image check (main/master)
6. **Deploy:** SSH deploy + health check + Slack notification (main/master)

---

## Security

### Hardening

| Layer | Measure |
|-------|---------|
| **Headers** | X-Frame-Options: DENY, X-Content-Type-Options: nosniff, Referrer-Policy: strict-origin-when-cross-origin, CSP |
| **Rate Limiting** | 6 req/min on auth routes, configurable per endpoint |
| **Sessions** | Encrypted (`SESSION_ENCRYPT=true`), secure cookies |
| **CORS** | Restricted to `APP_URL` (no wildcard) |
| **Uploads** | MIME type restriction on digital asset uploads |
| **SQL Injection** | Parameterized queries, validated sort columns against whitelists |
| **RBAC** | Every action gated by Spatie roles/permissions |
| **XSS** | Escaped output, CSP policy, `wire:confirm` on destructive actions |
| **2FA** | Google2FA with encrypted secrets, recovery codes |
| **Passwords** | Bcrypt with 12 rounds |
| **Tokens** | Daily pruning of expired Sanctum tokens |
| **Audit** | Spatie Activity Log on all critical operations |
| **Nginx** | Security headers, fastcgi restrictions |

---

## Testing

### Test Suite

**39 test files** covering:

- API Auth (login, register, 2FA, token refresh, recovery codes, permission checks)
- API Catalog (book listing, search)
- API Circulation (borrow, return, renew)
- API Content (announcements, events, bulletins)
- API Library Card (QR, barcode, PDF)
- API Messaging (inbox, sent, search, templates)
- API Push Notifications (subscribe, unsubscribe, VAPID key)
- Web Auth (login, registration, 2FA end-to-end, active account checks)
- Book Bulk Upload
- Borrow/Overdue/Fine/Payment flows
- Catalog, Circulation, Finance module tests
- Concurrent Session Limit
- Digital Library Service
- Fine Calculation
- Health Check
- M-Pesa Service
- Navigation Audit
- Optimization Tab
- Profile
- Registration Activation Policy
- Scheduled Token Cleanup
- Session Idle Timeout
- Subscription Module
- Webhook handling
- Document Service (Unit)
- Reporting Service (Unit)

### Run Tests

```bash
php artisan test
```

---

## Environment Configuration

### Core

```
APP_NAME=OLLMCHS Library
APP_ENV=production
APP_KEY=base64:...
APP_DEBUG=false
APP_URL=https://library.ollmchs.ac.ke
```

### Database

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ollmchs_library
DB_USERNAME=root
DB_PASSWORD=secret
```

### Session & Cache

```
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database
```

### Mail

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.ollmchs.ac.ke
MAIL_PORT=587
MAIL_USERNAME=library@ollmchs.ac.ke
MAIL_PASSWORD=secret
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=library@ollmchs.ac.ke
MAIL_FROM_NAME="OLLMCHS Library"
```

### M-Pesa

```
MPESA_CONSUMER_KEY=...
MPESA_CONSUMER_SECRET=...
MPESA_PASSKEY=...
MPESA_SHORTCODE=...
MPESA_ENVIRONMENT=sandbox
```

### Stripe

```
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

### WhatsApp

```
WHATSAPP_ENABLED=true
WHATSAPP_PROVIDER=cloud-api
WHATSAPP_TOKEN=...
WHATSAPP_PHONE_NUMBER_ID=...
WHATSAPP_FROM=...
```

### Web Push (VAPID)

```
VAPID_PUBLIC_KEY=...
VAPID_PRIVATE_KEY=...
```

### Two-Factor Auth

```
TWO_FACTOR_ENABLED=true
```

### Redis (Optional)

```
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Fine Rates (Optional Override)

```
FINE_DAILY_RATE=50
FINE_LOST_BOOK_RATE=1500
FINE_DAMAGE_RATE=500
```

---

## Quick Start

### Local Development

```bash
git clone <repository-url>
cd ollmchs-library

# Option 1: One-command setup
composer setup

# Option 2: Manual setup
cp .env.example .env
composer install
npm install && npm run build
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

**Default admin:** `admin@ollmchs.ac.ke` / `password`

### Development Server

```bash
# Runs 4 concurrent processes (server, queue, logs, vite)
composer dev
```

### Docker

```bash
docker compose build
docker compose up -d
```

### Production Deploy

```bash
bash deploy.sh
```

---

## Custom Artisan Commands

| Command | Description |
|---------|-------------|
| `php artisan assessment:overdue-fines` | Assess overdue fines for active borrows |
| `php artisan backup:database` | Database backup |
| `php artisan members:check-expiry` | Check and update expired memberships |
| `php artisan circulation:check-overdue` | Mark overdue borrows |
| `php artisan backup:clean` | Remove old backup files |
| `php artisan circulation:expire-reservations` | Expire old reservations |
| `php artisan push:generate-vapid-keys` | Generate VAPID keys for web push |
| `php artisan messages:send-scheduled` | Send queued scheduled messages |

---

## Project Structure

```
ollmchs-library/
├── app/
│   ├── Console/Commands/          # 8 artisan commands
│   ├── Helpers/                   # Utility helpers
│   ├── Http/Controllers/Auth/     # Auth controllers
│   ├── Jobs/                      # 4 queued jobs
│   ├── Livewire/Verify/           # Document lookup component
│   ├── Mail/                      # 9 Mailable classes
│   ├── Models/                    # 8 core models
│   ├── Modules/                   # 15 domain modules
│   ├── Providers/                 # Service providers
│   ├── Services/                  # Document, Download, Export services
│   ├── Traits/                    # Shared traits
│   └── View/                      # View components
├── bootstrap/
├── config/                        # 17 config files
├── database/
│   ├── migrations/                # 67 migration files
│   ├── seeders/                   # 12 seeders
│   └── factories/
├── docker/                        # nginx.conf, supervisord.conf, start.sh
├── ollmchs_mobile/                # Flutter mobile app
├── public/                        # Static assets, PWA manifest, service worker
├── resources/
│   ├── css/
│   ├── js/                        # app.js, sw.js (Workbox)
│   └── views/                     # Blade views
├── routes/                        # web.php, auth.php, console.php
├── tests/
│   ├── Feature/                   # 36 feature tests
│   └── Unit/                      # 3 unit tests
├── Dockerfile                     # Multi-stage build
├── docker-compose.yml
├── deploy.sh                      # Zero-downtime deploy
├── rollback.sh                    # Rollback script
└── .github/workflows/deploy.yml   # CI/CD
```

---

## License

Proprietary — Our Lady of Lourdes Mutomo College of Health Sciences (OLLMCHS). All rights reserved.
