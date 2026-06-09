# OLLMCHS Library Management System

A full-featured, enterprise-grade library management system built for OLLMCHS (a health sciences college in Kenya).

**Stack:** Laravel 13.8 • Livewire 3 • Volt 1.7 • Tailwind CSS 4 • Vite 8 • SQLite/MySQL/PostgreSQL

## Modules

| Module | Description |
|--------|-------------|
| **Catalog** | Books, book copies, bulk upload, search, filtering |
| **Circulation** | Borrowing, returns, overdue tracking, fine calculation |
| **Members** | Member list, profiles, import, membership management |
| **Finance** | Transactions, fines, fee management, analytics, reports |
| **Digital Library** | Digital assets, uploads, download tracking |
| **Notifications** | Email + in-app notifications, fine alerts, due reminders |
| **Settings** | General, appearance, backup, branding |
| **Auth** | Login, registration, email verification, 2FA, password reset |
| **API** | RESTful API with token/Sanctum authentication |
| **Roles** | Role/permission management (Spatie) |
| **Reports** | Report generation (PDF/CSV) with watermark support |

## Quick Start

```bash
cp .env.example .env
composer install
npm install && npm run build
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

**Default admin:** `admin@ollmchs.ac.ke` / `password`

## Testing

```bash
php artisan test
```

**44 tests · 99 assertions** — covering unit, feature, and health check suites.

## Production Deploy

### Docker (recommended)

```bash
docker compose build
docker compose up -d
```

### Manual Deploy

```bash
bash deploy.sh
```

Requires: PHP 8.3+, Composer 2, Node 22+, MySQL 8.0 or PostgreSQL 16.

## Security Hardening

- **Security Headers:** CSP, X-Frame-Options: DENY, X-Content-Type-Options: nosniff, Referrer-Policy, Permissions-Policy
- **Rate Limiting:** 6 req/min on auth routes (login, register, forgot/reset password, API login)
- **Session Encryption:** Enabled by default (`SESSION_ENCRYPT=true`)
- **CORS:** Restricted to `APP_URL` (no wildcard)
- **Upload Validation:** MIME type restriction on digital asset uploads
- **SQL Injection:** All sort columns validated against whitelists
- **Permission Checks:** Every action gated by Spatie roles/permissions
- **XSS:** Escaped output, CSP policy, `wire:confirm` on destructive actions

## DevOps

- **Docker:** Multi-stage build (PHP 8.3 FPM Alpine + Nginx + Supervisor)
- **Queue Workers:** 2 concurrent workers via Supervisor, `schedule:work` daemon
- **Health Check:** `GET /health` returns JSON with app, database, cache, and storage status
- **Deploy Script:** Zero-downtime symlink-based releases, keeps last 5 releases
- **Opcache:** Enabled with 128 MB, 10,000 files, validation disabled

## Architecture

- **Module-based:** Each domain lives in `app/Modules/{ModuleName}/` with its own routes, Livewire components, views, models, services, migrations, and provider
- **Livewire + Volt:** Full-page components with Volt functional API for CRUD; class-based components for complex interactions
- **Services Layer:** Business logic extracted into service classes (`BorrowingService`, `FineCalculationService`, `DocumentService`, `ReportingService`, `SettingsService`)
- **Settings Caching:** Frequently accessed settings cached in-memory via `SettingsService::cached()`

## License

Proprietary — OLLMCHS. All rights reserved.
