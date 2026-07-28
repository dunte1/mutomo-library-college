# OLLMCHS Mobile App — Complete Specification

> **Target Users**: Students and Lecturers
> **Platform**: Flutter 3.12 (Android, iOS, Web, Windows)
> **Backend**: Laravel API (Sanctum-authenticated, `/api/v1`)
> **Architecture**: BLoC (flutter_bloc) + Clean Architecture

---

## Table of Contents

1. [User Roles & Capabilities](#1-user-roles--capabilities)
2. [Screen Inventory](#2-screen-inventory)
3. [Feature Breakdown by Module](#3-feature-breakdown-by-module)
4. [Complete API Endpoint Map](#4-complete-api-endpoint-map)
5. [Data Models & DTOs](#5-data-models--dtos)
6. [State Management & Navigation](#6-state-management--navigation)
7. [Offline & Caching Strategy](#7-offline--caching-strategy)
8. [Push Notifications](#8-push-notifications)
9. [Authentication Flow](#9-authentication-flow)
10. [Permission Matrix](#10-permission-matrix)

---

## 1. User Roles & Capabilities

### Student

| Capability | Details |
|---|---|
| Browse catalog | Search, filter, view book details |
| View digital assets | Read ebooks, PDFs, notes in-app reader |
| Download digital assets | Subject to access level + rate limits |
| Borrow books | Up to 5 concurrent borrows |
| Return books | Self-service or librarian-assisted |
| Renew borrows | Up to max renewals allowed |
| Reserve books | Place holds on unavailable books |
| View reservations | See active/expired reservations |
| Cancel reservations | Cancel own reservations |
| View fines | See own fines, payment history |
| Pay fines | Via M-Pesa or Stripe |
| Manage library card | View card, QR code, barcode |
| Read messages | Inbox, sent, archived |
| Send messages | Direct messages to librarians/staff |
| View announcements | School-wide announcements |
| View events | Calendar events |
| View bulletins | Department-specific bulletins |
| View notifications | In-app notification center |
| Manage profile | Edit name, phone, avatar, password |
| View assignments | See teacher-assigned readings |
| Mark assignments complete | Submit completion status |
| View reading history | Past reads and progress |
| Get recommendations | AI-powered book suggestions |
| View reports | Personal borrowing summary |
| Manage subscriptions | View plans, subscribe, cancel |
| Bookmark books | Save books for later |

### Lecturer

All Student capabilities, plus:

| Capability | Details |
|---|---|
| Borrow books | Up to 10 concurrent borrows |
| Create assignments | Assign books/digital assets to students |
| Manage assignments | Edit, delete, track completion |
| View student progress | See assignment completion rates |
| Group assignments | Assign to entire program/department |
| Create recommendations | Recommend readings to students |

---

## 2. Screen Inventory

### Total: 48 Screens

---

### A. Authentication (6 screens)

| # | Screen | Route | Description |
|---|---|---|---|
| A1 | **Splash Screen** | `/splash` | App logo, auto-navigate |
| A2 | **Onboarding** | `/onboarding` | Feature highlights (3 slides) |
| A3 | **Login** | `/login` | Email + password, remember me |
| A4 | **Register** | `/register` | Full registration form |
| A5 | **Forgot Password** | `/forgot-password` | Email-based reset |
| A6 | **Two-Factor Verify** | `/2fa-verify` | TOTP code entry |

---

### B. Home & Navigation (3 screens)

| # | Screen | Route | Description |
|---|---|---|---|
| B1 | **Dashboard** | `/dashboard` | Stats, quick actions, recent activity |
| B2 | **Main Navigation** | Bottom nav | Home, Catalog, Library, Messages, More |
| B3 | **Search (Global)** | `/search` | Cross-module search bar |

---

### C. Book Catalog (7 screens)

| # | Screen | Route | Description |
|---|---|---|---|
| C1 | **Book List** | `/catalog/books` | Paginated grid/list with filters |
| C2 | **Book Detail** | `/catalog/books/:id` | Cover, info, copies, reviews, actions |
| C3 | **Book Search** | `/catalog/search` | Full-text search with results |
| C4 | **Category Browser** | `/catalog/categories` | Hierarchical category tree |
| C5 | **Author List** | `/catalog/authors` | Browse authors A-Z |
| C6 | **Author Detail** | `/catalog/authors/:id` | Author bio + books list |
| C7 | **Bookmarks** | `/catalog/bookmarks` | Saved books list |

---

### D. Digital Library (6 screens)

| # | Screen | Route | Description |
|---|---|---|---|
| D1 | **Digital Library Home** | `/digital-library` | Featured, categories, recent |
| D2 | **Asset Detail** | `/digital-library/:id` | Metadata, preview, download |
| D3 | **In-App Reader** | `/reader/:id` | PDF/ebook reader with progress |
| D4 | **Download Manager** | `/downloads` | Active/completed downloads |
| D5 | **Reading History** | `/reading-history` | Past reads with progress |
| D6 | **Citations** | `/citations/:id` | Generate citations (6 styles) |

---

### E. Circulation — My Loans (5 screens)

| # | Screen | Route | Description |
|---|---|---|---|
| E1 | **Active Loans** | `/loans/active` | Currently borrowed books |
| E2 | **Loan History** | `/loans/history` | Past borrows with dates |
| E3 | **Loan Detail** | `/loans/:id` | Full borrow record, actions |
| E4 | **My Reservations** | `/reservations` | Active/expired reservations |
| E5 | **Reserve Book** | `/reservations/new/:bookId` | Place a reservation |

---

### F. Fines (3 screens)

| # | Screen | Route | Description |
|---|---|---|---|
| F1 | **My Fines** | `/fines` | Outstanding + paid fines |
| F2 | **Fine Detail** | `/fines/:id` | Fine breakdown, pay action |
| F3 | **Payment Screen** | `/payment` | M-Pesa / Stripe payment flow |

---

### G. Library Card (2 screens)

| # | Screen | Route | Description |
|---|---|---|---|
| G1 | **My Library Card** | `/library-card` | Card with QR code, details |
| G2 | **Card Scanner** | `/scanner` | Scan QR/barcode for verification |

---

### H. Communication (6 screens)

| # | Screen | Route | Description |
|---|---|---|---|
| H1 | **Inbox** | `/messages/inbox` | Received messages list |
| H2 | **Sent Messages** | `/messages/sent` | Sent messages list |
| H3 | **Message Detail** | `/messages/:id` | Full message, replies |
| H4 | **Compose Message** | `/messages/compose` | New message form |
| H5 | **Announcements** | `/announcements` | School-wide announcements |
| H6 | **Events** | `/events` | Calendar events list |

---

### I. Assignments (3 screens)

| # | Screen | Route | Description |
|---|---|---|---|
| I1 | **My Assignments** | `/assignments` | Assigned readings list |
| I2 | **Assignment Detail** | `/assignments/:id` | Full assignment, status, actions |
| I3 | **Create Assignment** | `/assignments/create` | **(Lecturer only)** New assignment form |

---

### J. Notifications (2 screens)

| # | Screen | Route | Description |
|---|---|---|---|
| J1 | **Notification Center** | `/notifications` | All notifications list |
| J2 | **Notification Detail** | `/notifications/:id` | Expanded notification |

---

### K. Recommendations (2 screens)

| # | Screen | Route | Description |
|---|---|---|---|
| K1 | **Recommendations** | `/recommendations` | AI-suggested books |
| K2 | **Recommendation Detail** | `/recommendations/:id` | Suggestion rationale + book |

---

### L. Subscriptions (2 screens)

| # | Screen | Route | Description |
|---|---|---|---|
| L1 | **My Subscription** | `/subscription` | Current plan, status, renewal |
| L2 | **Plans List** | `/subscription/plans` | Available plans, checkout |

---

### M. Profile & Settings (5 screens)

| # | Screen | Route | Description |
|---|---|---|---|
| M1 | **Profile** | `/profile` | View/edit profile |
| M2 | **Change Password** | `/profile/password` | Update password |
| M3 | **Notification Preferences** | `/settings/notifications` | Toggle notification channels |
| M4 | **App Settings** | `/settings` | Theme, language, about |
| M5 | **About** | `/about` | App version, school info |

---

## 3. Feature Breakdown by Module

---

### 3.1 Authentication Module

#### Features

| Feature | Description | Student | Lecturer |
|---|---|---|---|
| Email + password login | Standard authentication | Yes | Yes |
| Registration | Create new account | Yes | Yes |
| Forgot password | Email-based reset | Yes | Yes |
| Two-factor authentication | TOTP via Google2FA | Yes | Yes |
| Biometric login | Fingerprint/face (local_auth) | Yes | Yes |
| Remember me | Persistent session | Yes | Yes |
| Auto-logout | Idle timeout (30 min) | Yes | Yes |
| Token refresh | Silent token renewal | Yes | Yes |

#### Login Flow

```
1. User opens app → Splash screen (2s)
2. No token → Onboarding (first run) → Login screen
3. User enters email + password
4. POST /api/v1/login → receives Sanctum token
5. If 2FA enabled → 2FA verify screen → POST /api/v1/2fa/verify
6. Store token in flutter_secure_storage
7. Navigate to Dashboard
8. Optional: Enable biometric login (local_auth + stored token)
```

#### Registration Fields

- Full name
- Email address
- Phone number
- Password + confirmation
- Department (dropdown)
- Program (filtered by department)
- Admission number (student) / Employee number (lecturer)
- Academic year + semester (student)
- Profile photo (optional, camera/gallery)

---

### 3.2 Dashboard Module

#### Student Dashboard

```
┌─────────────────────────────────────────────┐
│  Good morning, [Name]              [🔔] [👤] │
├─────────────────────────────────────────────┤
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐       │
│  │ Active│ │ Due  │ │Fines │ │ Card │       │
│  │ Loans │ │ Soon │ │      │ │      │       │
│  │   3   │ │   1  │ │ KES  │ │ [QR] │       │
│  │       │ │      │ │ 200  │ │      │       │
│  └──────┘ └──────┘ └──────┘ └──────┘       │
│                                             │
│  ⏰ Due Soon                                │
│  ┌─────────────────────────────────────┐   │
│  │ 📖 Anatomy Textbook 3rd Ed         │   │
│  │    Due: Jul 28, 2026               │   │
│  │    [Renew] [Details]               │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  📢 Announcements                           │
│  ┌─────────────────────────────────────┐   │
│  │ Library Hours Extended for Exams    │   │
│  │ New: 50+ Nursing ebooks added       │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  📚 Recommended For You                     │
│  ┌──────┐ ┌──────┐ ┌──────┐               │
│  │ 📖   │ │ 📖   │ │ 📖   │               │
│  │ Book │ │ Book │ │ Book │               │
│  │  1   │ │  2   │ │  3   │               │
│  └──────┘ └──────┘ └──────┘               │
│                                             │
│  📋 Assignments Due                         │
│  ┌─────────────────────────────────────┐   │
│  │ Pathology Review Ch.5-7            │   │
│  │ Due: Jul 30, 2026 | Dr. Mwangi     │   │
│  └─────────────────────────────────────┘   │
├─────────────────────────────────────────────┤
│  [🏠]    [📚]    [📖]    [💬]    [⋯]      │
│  Home   Catalog  Library  More    More     │
└─────────────────────────────────────────────┘
```

#### Lecturer Dashboard

Same as student, plus:

```
│  📋 My Assignments (3 active)                │
│  ┌─────────────────────────────────────┐   │
│  │ Anatomy Ch. 1-4 Reading            │   │
│  │ 25/30 completed | Due: Jul 28      │   │
│  │ [View Progress]                    │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  [+ Create Assignment]                      │
```

#### Dashboard Data

```
GET /api/v1/dashboard

Response:
{
  "user": { "name", "avatar", "role" },
  "stats": {
    "active_loans": 3,
    "due_soon": 1,
    "overdue": 0,
    "total_fines": 200,
    "unread_notifications": 5,
    "unread_messages": 2,
    "active_reservations": 1,
    "completed_assignments": 7,
    "pending_assignments": 2
  },
  "due_soon_books": [...],
  "recent_announcements": [...],
  "recommendations": [...],
  "pending_assignments": [...],
  "recent_activity": [...]
}
```

---

### 3.3 Book Catalog Module

#### Features

| Feature | Description | Student | Lecturer |
|---|---|---|---|
| Browse books | Paginated grid/list view | Yes | Yes |
| Search books | Full-text search across title, author, description | Yes | Yes |
| Filter books | By category, author, status, year, language | Yes | Yes |
| Sort books | By title, date added, popularity, rating | Yes | Yes |
| View book detail | Full metadata, copies, reviews | Yes | Yes |
| View book copies | Availability status per copy | Yes | Yes |
| Browse categories | Hierarchical category tree | Yes | Yes |
| Browse authors | Author list with detail pages | Yes | Yes |
| Browse publishers | Publisher list | Yes | Yes |
| Bookmark books | Save for later reading | Yes | Yes |
| Rate & review books | Star rating + text review | Yes | Yes |
| View reviews | See other users' reviews | Yes | Yes |
| Share book | Share via system share sheet | Yes | Yes |

#### Book Detail Screen

```
┌─────────────────────────────────────────────┐
│  ← Book Details                    [⋯] [♡]  │
├─────────────────────────────────────────────┤
│  ┌─────────────────────────────┐           │
│  │                             │           │
│  │      [Book Cover Image]     │           │
│  │                             │           │
│  └─────────────────────────────┘           │
│                                             │
│  Anatomy: A Complete Guide                  │
│  3rd Edition • 2024 • 450 pages             │
│  ★★★★☆ (4.2) • 23 reviews                 │
│                                             │
│  ┌─────────────┐ ┌─────────────┐           │
│  │  Available  │ │   3 of 5    │           │
│  │     ✅      │ │   copies    │           │
│  └─────────────┘ └─────────────┘           │
│                                             │
│  Categories: Medicine, Anatomy              │
│  Authors: Dr. James Mwangi                  │
│  Publisher: KenMed Press                    │
│  ISBN: 978-1234567890                       │
│  Dewey: 611.002                             │
│                                             │
│  ─── Description ─────────────────────     │
│  Comprehensive guide to human anatomy...    │
│                                             │
│  ─── Copies ──────────────────────────     │
│  ┌────────────────────────────────────┐    │
│  │ Barcode: BC-001  Shelf: A-3-12    │    │
│  │ Status: Available ✅               │    │
│  ├────────────────────────────────────┤    │
│  │ Barcode: BC-002  Shelf: A-3-13    │    │
│  │ Status: Borrowed 🔴               │    │
│  │ Due: Jul 30, 2026                 │    │
│  ├────────────────────────────────────┤    │
│  │ Barcode: BC-003  Shelf: A-3-14    │    │
│  │ Status: Available ✅               │    │
│  └────────────────────────────────────┘    │
│                                             │
│  ─── Reviews (23) ────────────────────     │
│  ⭐⭐⭐⭐⭐ "Excellent textbook..." - John  │
│  ⭐⭐⭐⭐ "Good but needs update..." - Mary │
│                                             │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐   │
│  │ 📖 Borrow│ │ 📋 Reserve│ │ 📝 Review│   │
│  └──────────┘ └──────────┘ └──────────┘   │
│                                             │
│  ─── Digital Assets ──────────────────     │
│  📄 Lecture Notes PDF (Download)            │
│  📄 Study Guide (Read Online)               │
└─────────────────────────────────────────────┘
```

#### Search with Filters

```
┌─────────────────────────────────────────────┐
│  🔍 [Search books, authors, ISBN...    ] [X]│
├─────────────────────────────────────────────┤
│  Filters:                                   │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐   │
│  │ Category ▼│ │ Author  ▼│ │ Status  ▼│   │
│  └──────────┘ └──────────┘ └──────────┘   │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐   │
│  │  Year   ▼│ │ Language ▼│ │  Sort   ▼│   │
│  └──────────┘ └──────────┘ └──────────┘   │
├─────────────────────────────────────────────┤
│  Results (42 books)                         │
│  ┌─────────────────────────────────────┐   │
│  │ 📖 Anatomy Guide    ✅ Available    │   │
│  │    Dr. Mwangi | 2024                │   │
│  ├─────────────────────────────────────┤   │
│  │ 📖 Physiology 101   🔴 Borrowed     │   │
│  │    Dr. Ochieng | 2023               │   │
│  └─────────────────────────────────────┘   │
└─────────────────────────────────────────────┘
```

#### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/books` | List books (paginated, filterable) |
| GET | `/api/v1/books/:id` | Book detail |
| GET | `/api/v1/books/search?q=` | Full-text search |
| GET | `/api/v1/categories` | List categories |
| GET | `/api/v1/categories/:id/books` | Books in category |
| GET | `/api/v1/authors` | List authors |
| GET | `/api/v1/authors/:id` | Author detail + books |
| GET | `/api/v1/publishers` | List publishers |
| GET | `/api/v1/publishers/:id` | Publisher detail + books |
| POST | `/api/v1/reviews` | Create book review |
| GET | `/api/v1/reviews?book_id=` | Reviews for a book |
| GET | `/api/v1/reviews/my` | My reviews |

---

### 3.4 Digital Library Module

#### Features

| Feature | Description | Student | Lecturer |
|---|---|---|---|
| Browse digital assets | Grid/list by category | Yes | Yes |
| View asset detail | Metadata, preview, download | Yes | Yes |
| Download assets | Subject to access level | Yes | Yes |
| In-app reader | PDF/ebook reader with controls | Yes | Yes |
| Track reading progress | Auto-save position | Yes | Yes |
| View reading history | Past reads with duration | Yes | Yes |
| Generate citations | 6 styles (APA, MLA, etc.) | Yes | Yes |
| Bookmark digital assets | Save for later | Yes | Yes |
| View recommendations | AI-powered suggestions | Yes | Yes |

#### Asset Detail Screen

```
┌─────────────────────────────────────────────┐
│  ← Digital Asset                   [⋯] [♡]  │
├─────────────────────────────────────────────┤
│  ┌─────────────────────────────┐           │
│  │      [Cover Image]          │           │
│  │      📄 PDF • 12.5 MB       │           │
│  └─────────────────────────────┘           │
│                                             │
│  Pathology Lecture Notes 2025               │
│  Dr. Sarah Wambui                           │
│  Published: Jan 15, 2025                    │
│                                             │
│  🔓 Public Access                           │
│  ⬇️ Downloads: 156  👁️ Views: 892           │
│                                             │
│  ─── Details ─────────────────────────     │
│  Category: Lecture Notes                    │
│  Language: English                          │
│  Pages: 85                                  │
│  Keywords: pathology, disease, diagnosis    │
│                                             │
│  ─── Description ─────────────────────     │
│  Comprehensive lecture notes covering...    │
│                                             │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐   │
│  │ 📖 Read  │ │ ⬇️ Download│ │ 📋 Cite  │   │
│  └──────────┘ └──────────┘ └──────────┘   │
│                                             │
│  ─── Related Books ──────────────────      │
│  📖 Textbook of Pathology                   │
│  📖 Clinical Pathology Guide                │
└─────────────────────────────────────────────┘
```

#### In-App Reader

```
┌─────────────────────────────────────────────┐
│  ← Pathology Notes     Page 23/85     [⋯]  │
├─────────────────────────────────────────────┤
│                                             │
│  Chapter 3: Inflammatory Responses          │
│                                             │
│  3.1 Acute Inflammation                     │
│                                             │
│  Acute inflammation is a rapid response     │
│  to harmful stimuli such as pathogens,     │
│  damaged cells, or irritants. The process   │
│  involves vascular changes and cellular    │
│  recruitment...                             │
│                                             │
│  [Content continues...]                     │
│                                             │
├─────────────────────────────────────────────┤
│  📑 Contents   🔍 Search   📌 Notes        │
│  ───────────────────────────               │
│  ◀◀  ◀  ●●●○○○○○○  ▶  ▶▶                  │
│  Page 23 of 85                              │
└─────────────────────────────────────────────┘
```

#### Citation Generator

```
┌─────────────────────────────────────────────┐
│  ← Generate Citation                        │
├─────────────────────────────────────────────┤
│  Style: [APA ▼]                             │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │ Wambui, S. (2025). Pathology        │   │
│  │ Lecture Notes. OLLMCHS. Retrieved    │   │
│  │ from digital library.                │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  Available styles:                          │
│  ○ APA  ○ MLA  ○ Chicago                   │
│  ○ Harvard  ○ Vancouver  ○ IEEE            │
│                                             │
│  [📋 Copy to Clipboard]                    │
│  [📤 Share]                                 │
└─────────────────────────────────────────────┘
```

#### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/digital-assets` | List digital assets (paginated) |
| GET | `/api/v1/digital-assets/:id` | Asset detail |
| POST | `/api/v1/digital-assets/:id/download` | Download asset |
| GET | `/api/v1/digital-categories` | List categories |
| GET | `/api/v1/digital-categories/:id/assets` | Assets in category |
| GET | `/api/v1/reading-history` | My reading history |
| GET | `/api/v1/recommendations` | Get recommendations |

---

### 3.5 Circulation Module (My Loans)

#### Features

| Feature | Description | Student | Lecturer |
|---|---|---|---|
| View active loans | Currently borrowed books | Yes | Yes |
| View loan history | Past borrows | Yes | Yes |
| View loan detail | Full borrow record | Yes | Yes |
| Renew a loan | Extend due date | Yes | Yes |
| View reservations | Active/expired reservations | Yes | Yes |
| Reserve a book | Place hold on unavailable book | Yes | Yes |
| Cancel reservation | Remove a reservation | Yes | Yes |

#### Active Loans Screen

```
┌─────────────────────────────────────────────┐
│  ← My Loans (3 active)                      │
├─────────────────────────────────────────────┤
│  [Active] [History] [Reserved]              │
│  ─────────────────                          │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │ 📖 Anatomy Textbook 3rd Ed          │   │
│  │    Due: Jul 28, 2026 (3 days)       │   │
│  │    ⚠️ Due soon                      │   │
│  │    [Renew] [Details]                │   │
│  ├─────────────────────────────────────┤   │
│  │ 📖 Physiology Manual                 │   │
│  │    Due: Aug 5, 2026 (11 days)       │   │
│  │    [Renew] [Details]                │   │
│  ├─────────────────────────────────────┤   │
│  │ 📖 Clinical Practice Guide          │   │
│  │    Due: Aug 15, 2026 (21 days)      │   │
│  │    [Renew] [Details]                │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  📊 Borrowing: 3/5 slots used               │
│  ████████░░ 60%                             │
└─────────────────────────────────────────────┘
```

#### Loan Detail Screen

```
┌─────────────────────────────────────────────┐
│  ← Loan Detail                              │
├─────────────────────────────────────────────┤
│  📖 Anatomy Textbook 3rd Edition            │
│                                             │
│  ┌─────────┐                                │
│  │ [Cover] │  Borrowed: Jul 14, 2026        │
│  │         │  Due: Jul 28, 2026             │
│  └─────────┘  Renewed: 0/2 times            │
│                                             │
│  Barcode: BC-001                            │
│  Shelf: A-3-12                              │
│  Issued by: Librarian Jane                  │
│                                             │
│  ─── Status ───────────────────────────    │
│  🟢 Active — 3 days remaining               │
│                                             │
│  ─── Fine Preview ─────────────────────    │
│  If not returned by Jul 28:                 │
│  Late fee: KES 50/day                       │
│                                             │
│  ┌──────────────────┐ ┌────────────────┐   │
│  │ 🔄 Renew Loan    │ │ 📋 Book Details│   │
│  └──────────────────┘ └────────────────┘   │
└─────────────────────────────────────────────┘
```

#### Reserve Book Screen

```
┌─────────────────────────────────────────────┐
│  ← Reserve Book                             │
├─────────────────────────────────────────────┤
│  📖 Physiology 101                          │
│  By: Dr. Ochieng                            │
│                                             │
│  Status: All copies currently borrowed       │
│  Queue position: #3                         │
│                                             │
│  ─── Reservation Details ──────────────    │
│  Preferred pickup: [Main Library ▼]         │
│  Notify me via: [Email] [Push] [SMS]       │
│                                             │
│  Estimated availability: ~2 weeks           │
│                                             │
│  ⚠️ Reservations expire after 3 days        │
│     if not collected after notification     │
│                                             │
│  [✅ Confirm Reservation]                   │
└─────────────────────────────────────────────┘
```

#### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/loans/active` | Active loans |
| GET | `/api/v1/loans/history` | Loan history |
| GET | `/api/v1/loans/:id` | Loan detail |
| POST | `/api/v1/loans/:id/renew` | Renew loan |
| GET | `/api/v1/reservations` | My reservations |
| POST | `/api/v1/reservations` | Create reservation |
| DELETE | `/api/v1/reservations/:id` | Cancel reservation |

---

### 3.6 Fine Management Module

#### Features

| Feature | Description | Student | Lecturer |
|---|---|---|---|
| View outstanding fines | List of unpaid fines | Yes | Yes |
| View paid fines | Payment history | Yes | Yes |
| View fine detail | Breakdown of a fine | Yes | Yes |
| Pay fine (M-Pesa) | STK push payment | Yes | Yes |
| Pay fine (Stripe) | Card payment | Yes | Yes |

#### Fines Screen

```
┌─────────────────────────────────────────────┐
│  ← My Fines                                 │
├─────────────────────────────────────────────┤
│  Outstanding: KES 200                       │
│  ████████░░░░░░░░░░░░                       │
│                                             │
│  [Outstanding] [Paid]                        │
│  ──────────────                             │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │ ⚠️ Overdue Fine                     │   │
│  │ Anatomy Textbook — 4 days late      │   │
│  │ KES 200                             │   │
│  │ Assessed: Jul 18, 2026              │   │
│  │ [Pay Now]                           │   │
│  ├─────────────────────────────────────┤   │
│  │ 💰 Paid Fine                        │   │
│  │ Physiology Manual — 2 days late     │   │
│  │ KES 100 — Paid Jul 20, 2026        │   │
│  │ Receipt: RCT-20260720-001234       │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  [💳 Pay All Outstanding — KES 200]        │
└─────────────────────────────────────────────┘
```

#### Payment Flow

```
┌─────────────────────────────────────────────┐
│  ← Payment                                  │
├─────────────────────────────────────────────┤
│  Fine: Overdue — Anatomy Textbook           │
│  Amount: KES 200                            │
│                                             │
│  Payment Method:                            │
│  ┌─────────────────────────────────────┐   │
│  │ 📱 M-Pesa (Recommended)             │   │
│  │ 💳 Credit/Debit Card                │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  Phone: [+254 7XX XXX XXX]                 │
│                                             │
│  [✅ Pay KES 200]                           │
│                                             │
│  🔒 Secure payment via Safaricom/Stripe     │
└─────────────────────────────────────────────┘

         ↓ After M-Pesa STK Push ↓

┌─────────────────────────────────────────────┐
│  ✅ Payment Successful                       │
├─────────────────────────────────────────────┤
│  Amount: KES 200                            │
│  M-Pesa Ref: QHK7Y8Z5LP                   │
│  Receipt: RCT-20260725-001234              │
│  Date: Jul 25, 2026 14:32                  │
│                                             │
│  [📋 Save Receipt]  [📤 Share]              │
│  [← Back to Fines]                          │
└─────────────────────────────────────────────┘
```

#### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/fines` | My fines (outstanding + paid) |
| GET | `/api/v1/fines/:id` | Fine detail |
| POST | `/api/v1/fines/:id/pay` | Initiate payment |
| GET | `/api/v1/payments` | Payment history |
| GET | `/api/v1/payments/:id` | Payment detail |

---

### 3.7 Library Card Module

#### Features

| Feature | Description | Student | Lecturer |
|---|---|---|---|
| View library card | Digital card with photo | Yes | Yes |
| View QR code | For verification scanning | Yes | Yes |
| View barcode | For barcode scanning | Yes | Yes |
| Download card as PDF | Printable card | Yes | Yes |
| Scan QR/barcode | Verify another card | Yes | Yes |

#### Library Card Screen

```
┌─────────────────────────────────────────────┐
│  ← My Library Card                          │
├─────────────────────────────────────────────┤
│  ┌─────────────────────────────────────┐   │
│  │  ╔═══════════════════════════════╗  │   │
│  │  ║   OLLMCHS LIBRARY            ║  │   │
│  │  ║   ─────────────────          ║  │   │
│  │  ║   [Photo]                     ║  │   │
│  │  ║                               ║  │   │
│  │  ║   MEM-00142                   ║  │   │
│  │  ║   John Kamau                  ║  │   │
│  │  ║   Student • Nursing           ║  │   │
│  │  ║   Valid: Jan 2026 - Dec 2026  ║  │   │
│  │  ║                               ║  │   │
│  │  ║   ▐▐▐▐▐▐▐▐▐▐▐▐▐             ║  │   │
│  │  ║   BARCODE: LC-2026-00142     ║  │   │
│  │  ╚═══════════════════════════════╝  │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │          [QR Code Image]            │   │
│  │     Scan to verify this card        │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  [📋 Copy Card Number]                     │
│  [📥 Download PDF]                          │
│  [🔍 Verify Card]                           │
└─────────────────────────────────────────────┘
```

#### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/library-card` | My card data |
| GET | `/api/v1/library-card/qr` | QR code image |
| GET | `/api/v1/library-card/barcode` | Barcode image |
| GET | `/api/v1/library-card/pdf` | Download card PDF |

---

### 3.8 Communication Module

#### Features

| Feature | Description | Student | Lecturer |
|---|---|---|---|
| View inbox | Received messages | Yes | Yes |
| View sent messages | Messages sent by user | Yes | Yes |
| Read message | Full message content | Yes | Yes |
| Reply to message | Inline reply | Yes | Yes |
| Forward message | Forward to another user | Yes | Yes |
| Compose new message | New direct message | Yes | Yes |
| Archive/unarchive | Organize messages | Yes | Yes |
| Search messages | Search across messages | Yes | Yes |
| View announcements | School-wide notices | Yes | Yes |
| View events | Calendar events | Yes | Yes |
| View bulletins | Department bulletins | Yes | Yes |

#### Inbox Screen

```
┌─────────────────────────────────────────────┐
│  ← Messages                     🔍 [✏️]     │
├─────────────────────────────────────────────┤
│  [Inbox] [Sent] [Archived]                  │
│  ─────────────                              │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │ 📩 Library Hours Update        2h   │   │
│  │    Admin: New opening hours for...   │   │
│  │    🔴 Unread                         │   │
│  ├─────────────────────────────────────┤   │
│  │ 📩 Fines Reminder              1d   │   │
│  │    System: You have an outstanding...│   │
│  │    ⚪ Read                           │   │
│  ├─────────────────────────────────────┤   │
│  │ 💬 Dr. Mwangi - Assignment Q   3d   │   │
│  │    Hello, I have a question about... │   │
│  │    🔴 Unread                         │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  Unread: 3 messages                         │
└─────────────────────────────────────────────┘
```

#### Compose Message Screen

```
┌─────────────────────────────────────────────┐
│  ← Compose Message                          │
├─────────────────────────────────────────────┤
│  To: [🔍 Search recipients...]              │
│  Subject: [Assignment question           ]  │
│                                             │
│  Priority: [Normal ▼]                       │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │                                     │   │
│  │  Hello,                            │   │
│  │                                     │   │
│  │  I have a question about the       │   │
│  │  pathology assignment...            │   │
│  │                                     │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  [📎 Attach File]                           │
│                                             │
│  [📤 Send]                                  │
└─────────────────────────────────────────────┘
```

#### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/messages/inbox` | Inbox messages |
| GET | `/api/v1/messages/sent` | Sent messages |
| GET | `/api/v1/messages/unread-count` | Unread count |
| GET | `/api/v1/messages/search?q=` | Search messages |
| GET | `/api/v1/messages/archived` | Archived messages |
| GET | `/api/v1/messages/:id` | Message detail |
| POST | `/api/v1/messages/send` | Send new message |
| POST | `/api/v1/messages/:id/reply` | Reply to message |
| POST | `/api/v1/messages/:id/forward` | Forward message |
| POST | `/api/v1/messages/:id/archive` | Archive message |
| GET | `/api/v1/announcements` | List announcements |
| GET | `/api/v1/announcements/:id` | Announcement detail |
| GET | `/api/v1/events` | List events |
| GET | `/api/v1/events/:id` | Event detail |
| GET | `/api/v1/bulletins` | List bulletins |

---

### 3.9 Assignments Module

#### Student Features

| Feature | Description |
|---|---|
| View assigned readings | List of all assignments from teachers |
| View assignment detail | Full assignment info, due date, status |
| Mark as viewed | Confirm assignment was seen |
| Mark as completed | Submit completion |
| Open assigned book/asset | Direct link to the resource |

#### Lecturer Features (all student features, plus)

| Feature | Description |
|---|---|
| Create assignment | Assign book or digital asset to students |
| Select recipients | Individual students, program, or department |
| Set due date | Deadline for completion |
| Edit assignment | Modify title, description, due date |
| Delete assignment | Remove an assignment |
| Track student progress | See who completed, who hasn't |
| View completion stats | Percentage, individual status |

#### Student Assignments Screen

```
┌─────────────────────────────────────────────┐
│  ← My Assignments                           │
├─────────────────────────────────────────────┤
│  [Pending] [In Progress] [Completed] [All]  │
│  ─────────────────────────                  │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │ 📋 Pathology Review Ch. 5-7         │   │
│  │    Dr. Wambui | Due: Jul 30         │   │
│  │    📖 Pathology Textbook 2024       │   │
│  │    Status: Pending                  │   │
│  │    [View] [Mark Complete]           │   │
│  ├─────────────────────────────────────┤   │
│  │ 📋 Anatomy Lab Manual               │   │
│  │    Dr. Mwangi | Due: Aug 5          │   │
│  │    📄 Anatomy Lab Notes PDF         │   │
│  │    Status: In Progress (40%)        │   │
│  │    [Continue Reading]              │   │
│  ├─────────────────────────────────────┤   │
│  │ ✅ Ethics in Healthcare             │   │
│  │    Dr. Ochieng | Completed Jul 20   │   │
│  │    📖 Ethics Textbook               │   │
│  │    Status: Completed ✓              │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  Pending: 1 | In Progress: 1 | Done: 5     │
└─────────────────────────────────────────────┘
```

#### Lecturer Create Assignment Screen

```
┌─────────────────────────────────────────────┐
│  ← Create Assignment                        │
├─────────────────────────────────────────────┤
│  Title: [Pathology Review Ch. 5-7        ]  │
│                                             │
│  Description:                               │
│  ┌─────────────────────────────────────┐   │
│  │ Read chapters 5-7 and prepare for   │   │
│  │ next week's quiz. Focus on...       │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  Resource Type:                             │
│  ○ Book  ● Digital Asset                    │
│                                             │
│  Select Resource: [🔍 Search assets...]     │
│  Selected: 📄 Pathology Textbook 2024       │
│                                             │
│  Due Date: [📅 Jul 30, 2026]               │
│                                             │
│  Assign To:                                 │
│  ○ Individual Students                      │
│  ● Entire Program: [Nursing ▼]             │
│  ○ Department: [Medicine ▼]                │
│                                             │
│  Notify via: ☑️ Email ☑️ Push ☑️ In-app     │
│                                             │
│  [✅ Create Assignment]                     │
└─────────────────────────────────────────────┘
```

#### Lecturer Track Progress Screen

```
┌─────────────────────────────────────────────┐
│  ← Assignment Progress                      │
├─────────────────────────────────────────────┤
│  📋 Pathology Review Ch. 5-7                │
│  Dr. Wambui | Due: Jul 30                   │
│                                             │
│  Progress: 25/30 completed (83%)            │
│  ████████████████████░░░ 83%               │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │ ✅ John Kamau    — Completed Jul 25 │   │
│  │ ✅ Mary Wanjiku  — Completed Jul 25 │   │
│  │ ✅ Peter Omondi  — Completed Jul 24 │   │
│  │ ⏳ Grace Njeri   — In Progress      │   │
│  │ ⏳ David Mutua   — In Progress      │   │
│  │ ❌ Sarah Chebet  — Not Started      │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  [📤 Send Reminder to Incomplete]           │
│  [📋 Export Progress CSV]                   │
└─────────────────────────────────────────────┘
```

#### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/assignments` | My assignments (student) |
| GET | `/api/v1/assignments/:id` | Assignment detail |
| POST | `/api/v1/assignments/:id/view` | Mark as viewed |
| POST | `/api/v1/assignments/:id/complete` | Mark as completed |
| GET | `/api/v1/teacher/assignments` | Lecturer's assignments |
| POST | `/api/v1/teacher/assignments` | Create assignment |
| PUT | `/api/v1/teacher/assignments/:id` | Update assignment |
| DELETE | `/api/v1/teacher/assignments/:id` | Delete assignment |
| GET | `/api/v1/teacher/assignments/:id/students` | Student progress |

---

### 3.10 Notifications Module

#### Features

| Feature | Description | Student | Lecturer |
|---|---|---|---|
| View notifications | All in-app notifications | Yes | Yes |
| Mark as read | Single notification | Yes | Yes |
| Mark all as read | Bulk mark | Yes | Yes |
| Unread count badge | Nav bar badge | Yes | Yes |
| Notification detail | Expanded view | Yes | Yes |

#### Notification Types

| Type | Trigger | Icon |
|---|---|---|
| `overdue` | Book becomes overdue | 🔴 |
| `due_reminder` | Approaching due date | 🟡 |
| `hold_available` | Reserved book returned | 🟢 |
| `fine_assessed` | New fine created | 🔴 |
| `reservation` | Reservation confirmed | 🟢 |
| `return_reminder` | Approaching due date | 🟡 |
| `assignment` | New assignment from teacher | 📋 |
| `message` | New message received | 📩 |
| `system` | System announcements | ℹ️ |

#### Notifications Screen

```
┌─────────────────────────────────────────────┐
│  ← Notifications (5 unread)    [Mark All ✓] │
├─────────────────────────────────────────────┤
│  ┌─────────────────────────────────────┐   │
│  │ 🔴 Overdue — Anatomy Textbook       │   │
│  │    Your book is 2 days overdue.      │   │
│  │    Fine of KES 100 has been assessed.│   │
│  │    2 hours ago                       │   │
│  ├─────────────────────────────────────┤   │
│  │ 🟡 Due Tomorrow — Physiology Manual  │   │
│  │    Please return or renew by         │   │
│  │    tomorrow.                        │   │
│  │    1 day ago                        │   │
│  ├─────────────────────────────────────┤   │
│  │ 📋 New Assignment — Dr. Wambui       │   │
│  │    Pathology Review Ch. 5-7         │   │
│  │    Due: Jul 30, 2026               │   │
│  │    2 days ago                       │   │
│  ├─────────────────────────────────────┤   │
│  │ 📩 New Message — Dr. Mwangi         │   │
│  │    Re: Assignment question          │   │
│  │    3 days ago                       │   │
│  ├─────────────────────────────────────┤   │
│  │ 🟢 Hold Available — Physiology 101   │   │
│  │    Your reserved book is ready      │   │
│  │    for pickup.                      │   │
│  │    5 days ago                       │   │
│  └─────────────────────────────────────┘   │
└─────────────────────────────────────────────┘
```

#### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/notifications` | List notifications |
| GET | `/api/v1/notifications/unread-count` | Unread count |
| POST | `/api/v1/notifications/:id/read` | Mark as read |
| POST | `/api/v1/notifications/read-all` | Mark all as read |

---

### 3.11 Recommendations Module

#### Features

| Feature | Description | Student | Lecturer |
|---|---|---|---|
| View recommendations | AI-suggested books | Yes | Yes |
| View recommendation detail | Rationale + book info | Yes | Yes |
| Save/bookmark | Save recommendation | Yes | Yes |

#### Recommendation Strategies

1. **Similar to read books** — Based on category, author, subjects
2. **Based on reading history** — Books matching reading patterns
3. **Department popular** — Most borrowed in user's department
4. **New arrivals** — Recently added to catalog
5. **Personalized** — Combined scoring across all signals

#### Recommendations Screen

```
┌─────────────────────────────────────────────┐
│  ← Recommended For You                      │
├─────────────────────────────────────────────┤
│  Because you read "Anatomy Textbook"        │
│  ─────────────────────────────              │
│  ┌──────┐ ┌──────┐ ┌──────┐               │
│  │ 📖   │ │ 📖   │ │ 📖   │               │
│  │Human │ │Atlas │ │Lab   │               │
│  │Phys. │ │Anat. │ │Guide │               │
│  └──────┘ └──────┘ └──────┘               │
│                                             │
│  Popular in Nursing Department              │
│  ─────────────────────────────              │
│  ┌──────┐ ┌──────┐ ┌──────┐               │
│  │ 📖   │ │ 📖   │ │ 📖   │               │
│  │Med-  │ │Clin- │ │Phar- │               │
│  │icines│ │ical  │ │maco. │               │
│  └──────┘ └──────┘ └──────┘               │
│                                             │
│  New Arrivals                               │
│  ─────────────────────────────              │
│  ┌──────┐ ┌──────┐ ┌──────┐               │
│  │ 📖   │ │ 📖   │ │ 📖   │               │
│  │New 1 │ │New 2 │ │New 3 │               │
│  └──────┘ └──────┘ └──────┘               │
└─────────────────────────────────────────────┘
```

#### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/recommendations` | Get recommendations |

---

### 3.12 Subscriptions Module

#### Features

| Feature | Description | Student | Lecturer |
|---|---|---|---|
| View current subscription | Plan, status, renewal date | Yes | Yes |
| Browse available plans | Plans with features | Yes | Yes |
| Subscribe to plan | Payment via M-Pesa/Stripe | Yes | Yes |
| Cancel subscription | Cancel with reason | Yes | Yes |

#### Subscription Screen

```
┌─────────────────────────────────────────────┐
│  ← My Subscription                          │
├─────────────────────────────────────────────┤
│  ┌─────────────────────────────────────┐   │
│  │  🎓 Student Plan — Monthly          │   │
│  │  Status: Active ✅                   │   │
│  │  Started: Jul 1, 2026              │   │
│  │  Renews: Aug 1, 2026              │   │
│  │  Price: KES 500/month              │   │
│  │                                     │   │
│  │  Features:                          │   │
│  │  ✅ View catalog                    │   │
│  │  ✅ Borrow books                    │   │
│  │  ✅ Download digital assets         │   │
│  │  ✅ Send messages                   │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  [🔄 Change Plan]  [❌ Cancel]              │
│                                             │
│  ─── Available Plans ──────────────────    │
│  ┌─────────────────────────────────────┐   │
│  │ 📘 Student Monthly — KES 500/mo     │   │
│  │ 📗 Student Yearly — KES 4,500/yr    │   │
│  │ 📕 Lecturer Monthly — KES 800/mo    │   │
│  │ 📙 Lecturer Yearly — KES 7,200/yr   │   │
│  └─────────────────────────────────────┘   │
└─────────────────────────────────────────────┘
```

#### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/subscription/my` | My subscription |
| GET | `/api/v1/subscription/plans` | Available plans |
| POST | `/api/v1/subscription/store` | Create subscription |
| POST | `/api/v1/subscription/cancel` | Cancel subscription |

---

### 3.13 Profile & Settings Module

#### Features

| Feature | Description | Student | Lecturer |
|---|---|---|---|
| View profile | Current profile info | Yes | Yes |
| Edit profile | Update name, phone, photo | Yes | Yes |
| Change password | Update password | Yes | Yes |
| Enable/disable 2FA | Toggle two-factor | Yes | Yes |
| Notification preferences | Toggle channels | Yes | Yes |
| App theme | Light/dark mode | Yes | Yes |
| App language | Locale selection | Yes | Yes |

#### Profile Screen

```
┌─────────────────────────────────────────────┐
│  ← My Profile                               │
├─────────────────────────────────────────────┤
│         ┌──────────┐                        │
│         │ [Avatar] │                        │
│         │  Camera  │                        │
│         └──────────┘                        │
│         John Kamau                           │
│         MEM-00142 • Student                  │
│         Nursing Department                   │
│                                             │
│  ─── Personal Info ───────────────────     │
│  Name: John Kamau              [Edit]       │
│  Email: john@ollmchs.ac.ke     [Edit]       │
│  Phone: +254 712 345 678       [Edit]       │
│  Department: Nursing                       │
│  Program: BSc Nursing                      │
│  Admission: ADM-2024-001                   │
│  Academic Year: 2025/2026                  │
│                                             │
│  ─── Security ────────────────────────     │
│  🔐 Change Password                        │
│  📱 Two-Factor Auth: ON  [Manage]          │
│  🔑 Biometric Login: ON  [Toggle]          │
│                                             │
│  ─── Preferences ────────────────────     │
│  🔔 Notification Settings                  │
│  🌙 Theme: [Light ▼]                       │
│  🌐 Language: [English ▼]                  │
│                                             │
│  ─── About ──────────────────────────     │
│  📱 App Version: 1.0.0                     │
│  🏫 Our Lady of Lourdes Mutomo CHS         │
│  📧 support@ollmchs.ac.ke                  │
│                                             │
│  [🚪 Logout]                                │
└─────────────────────────────────────────────┘
```

#### Notification Preferences Screen

```
┌─────────────────────────────────────────────┐
│  ← Notification Preferences                 │
├─────────────────────────────────────────────┤
│                                             │
│  Email Notifications                        │
│  ──────────────────                         │
│  ☑️ Overdue notices                         │
│  ☑️ Due date reminders                      │
│  ☑️ Reservation updates                     │
│  ☑️ Fine assessments                        │
│  ☑️ Assignment notifications                │
│  ☑️ Announcements                           │
│  ☑️ Payment confirmations                   │
│                                             │
│  Push Notifications                         │
│  ──────────────────                         │
│  ☑️ Overdue notices                         │
│  ☑️ Due date reminders                      │
│  ☑️ New messages                            │
│  ☑️ Assignment notifications                │
│  ☑️ Announcements                           │
│  ☐ Marketing messages                       │
│                                             │
│  In-App Notifications                       │
│  ──────────────────                         │
│  ☑️ All types                               │
│                                             │
│  [✅ Save Preferences]                      │
└─────────────────────────────────────────────┘
```

#### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/profile` | Get profile |
| PUT | `/api/v1/profile` | Update profile |
| POST | `/api/v1/profile/avatar` | Upload avatar |
| POST | `/api/v1/change-password` | Change password |
| POST | `/api/v1/2fa/enable` | Enable 2FA |
| POST | `/api/v1/2fa/verify-setup` | Verify 2FA setup |
| POST | `/api/v1/2fa/disable` | Disable 2FA |
| GET | `/api/v1/push/preferences` | Get notification prefs |
| PUT | `/api/v1/push/preferences` | Update notification prefs |

---

### 3.14 Reports Module (Personal)

#### Student Reports

| Report | Description |
|---|---|
| Borrowing summary | Total borrows, current active, history |
| Reading summary | Digital assets read, completion rates |
| Fine summary | Total fines, paid, outstanding |
| Assignment summary | Completed, pending, overdue |

#### Lecturer Reports

All student reports, plus:

| Report | Description |
|---|---|
| Assignment completion | Student progress across assignments |
| Department borrowing | Books borrowed by department |

#### API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/v1/reports/reading-summary` | Personal reading summary |
| GET | `/api/v1/reports/loan-history` | Loan history report |
| GET | `/api/v1/reports/fine-history` | Fine history report |

---

## 4. Complete API Endpoint Map

### Authentication

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 1 | POST | `/api/v1/login` | No | Login |
| 2 | POST | `/api/v1/register` | No | Register |
| 3 | POST | `/api/v1/forgot-password` | No | Request password reset |
| 4 | POST | `/api/v1/reset-password` | No | Reset password |
| 5 | POST | `/api/v1/logout` | Yes | Logout |
| 6 | GET | `/api/v1/user` | Yes | Current user |
| 7 | POST | `/api/v1/refresh` | Yes | Refresh token |
| 8 | POST | `/api/v1/change-password` | Yes | Change password |
| 9 | POST | `/api/v1/verify-email` | Yes | Verify email |
| 10 | POST | `/api/v1/resend-verification` | Yes | Resend verification |

### Two-Factor Authentication

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 11 | POST | `/api/v1/2fa/verify` | No | Verify 2FA (login) |
| 12 | POST | `/api/v1/2fa/enable` | Yes | Enable 2FA |
| 13 | POST | `/api/v1/2fa/verify-setup` | Yes | Verify 2FA setup |
| 14 | POST | `/api/v1/2fa/disable` | Yes | Disable 2FA |

### Profile

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 15 | GET | `/api/v1/profile` | Yes | Get profile |
| 16 | PUT | `/api/v1/profile` | Yes | Update profile |
| 17 | POST | `/api/v1/profile/avatar` | Yes | Upload avatar |

### Dashboard

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 18 | GET | `/api/v1/dashboard` | Yes | Dashboard data |

### Book Catalog

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 19 | GET | `/api/v1/books` | Yes | List books (paginated) |
| 20 | GET | `/api/v1/books/:id` | Yes | Book detail |
| 21 | GET | `/api/v1/books/search?q=` | Yes | Search books |
| 22 | GET | `/api/v1/categories` | Yes | List categories |
| 23 | GET | `/api/v1/categories/:id/books` | Yes | Books in category |
| 24 | GET | `/api/v1/authors` | Yes | List authors |
| 25 | GET | `/api/v1/authors/:id` | Yes | Author detail |
| 26 | GET | `/api/v1/publishers` | Yes | List publishers |
| 27 | GET | `/api/v1/publishers/:id` | Yes | Publisher detail |
| 28 | POST | `/api/v1/reviews` | Yes | Create review |
| 29 | GET | `/api/v1/reviews?book_id=` | Yes | Book reviews |
| 30 | GET | `/api/v1/reviews/my` | Yes | My reviews |

### Digital Library

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 31 | GET | `/api/v1/digital-assets` | Yes | List digital assets |
| 32 | GET | `/api/v1/digital-assets/:id` | Yes | Asset detail |
| 33 | POST | `/api/v1/digital-assets/:id/download` | Yes | Download asset |
| 34 | GET | `/api/v1/digital-categories` | Yes | List categories |
| 35 | GET | `/api/v1/digital-categories/:id/assets` | Yes | Assets in category |
| 36 | GET | `/api/v1/reading-history` | Yes | Reading history |
| 37 | GET | `/api/v1/recommendations` | Yes | Get recommendations |

### Circulation

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 38 | GET | `/api/v1/loans/active` | Yes | Active loans |
| 39 | GET | `/api/v1/loans/history` | Yes | Loan history |
| 40 | GET | `/api/v1/loans/:id` | Yes | Loan detail |
| 41 | POST | `/api/v1/loans/:id/renew` | Yes | Renew loan |
| 42 | GET | `/api/v1/reservations` | Yes | My reservations |
| 43 | POST | `/api/v1/reservations` | Yes | Create reservation |
| 44 | DELETE | `/api/v1/reservations/:id` | Yes | Cancel reservation |

### Library Card

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 45 | GET | `/api/v1/library-card` | Yes | My card |
| 46 | GET | `/api/v1/library-card/qr` | Yes | QR code |
| 47 | GET | `/api/v1/library-card/barcode` | Yes | Barcode |
| 48 | GET | `/api/v1/library-card/pdf` | Yes | Card PDF |

### Fines & Payments

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 49 | GET | `/api/v1/fines` | Yes | My fines |
| 50 | GET | `/api/v1/fines/:id` | Yes | Fine detail |
| 51 | POST | `/api/v1/fines/:id/pay` | Yes | Initiate payment |
| 52 | GET | `/api/v1/payments` | Yes | Payment history |
| 53 | GET | `/api/v1/payments/:id` | Yes | Payment detail |

### Messaging

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 54 | GET | `/api/v1/messages/inbox` | Yes | Inbox |
| 55 | GET | `/api/v1/messages/sent` | Yes | Sent messages |
| 56 | GET | `/api/v1/messages/unread-count` | Yes | Unread count |
| 57 | GET | `/api/v1/messages/search?q=` | Yes | Search messages |
| 58 | GET | `/api/v1/messages/archived` | Yes | Archived messages |
| 59 | GET | `/api/v1/messages/:id` | Yes | Message detail |
| 60 | POST | `/api/v1/messages/send` | Yes | Send message |
| 61 | POST | `/api/v1/messages/:id/reply` | Yes | Reply |
| 62 | POST | `/api/v1/messages/:id/forward` | Yes | Forward |
| 63 | POST | `/api/v1/messages/:id/archive` | Yes | Archive |

### Notifications

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 64 | GET | `/api/v1/notifications` | Yes | List notifications |
| 65 | GET | `/api/v1/notifications/unread-count` | Yes | Unread count |
| 66 | POST | `/api/v1/notifications/:id/read` | Yes | Mark read |
| 67 | POST | `/api/v1/notifications/read-all` | Yes | Mark all read |

### Push Notifications

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 68 | POST | `/api/v1/push/subscribe` | Yes | Subscribe to push |
| 69 | DELETE | `/api/v1/push/unsubscribe` | Yes | Unsubscribe |
| 70 | DELETE | `/api/v1/push/unsubscribe-all` | Yes | Unsubscribe all |
| 71 | GET | `/api/v1/push/subscriptions` | Yes | List subscriptions |
| 72 | GET | `/api/v1/push/preferences` | Yes | Get preferences |
| 73 | PUT | `/api/v1/push/preferences` | Yes | Update preferences |
| 74 | GET | `/api/v1/vapid-key` | No | VAPID public key |

### Content

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 75 | GET | `/api/v1/announcements` | Yes | List announcements |
| 76 | GET | `/api/v1/announcements/:id` | Yes | Announcement detail |
| 77 | GET | `/api/v1/events` | Yes | List events |
| 78 | GET | `/api/v1/events/:id` | Yes | Event detail |
| 79 | GET | `/api/v1/bulletins` | Yes | List bulletins |

### Assignments

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 80 | GET | `/api/v1/assignments` | Yes | My assignments (student) |
| 81 | GET | `/api/v1/assignments/:id` | Yes | Assignment detail |
| 82 | POST | `/api/v1/assignments/:id/view` | Yes | Mark viewed |
| 83 | POST | `/api/v1/assignments/:id/complete` | Yes | Mark completed |
| 84 | GET | `/api/v1/teacher/assignments` | Yes (L) | Lecturer's assignments |
| 85 | POST | `/api/v1/teacher/assignments` | Yes (L) | Create assignment |
| 86 | PUT | `/api/v1/teacher/assignments/:id` | Yes (L) | Update assignment |
| 87 | DELETE | `/api/v1/teacher/assignments/:id` | Yes (L) | Delete assignment |
| 88 | GET | `/api/v1/teacher/assignments/:id/students` | Yes (L) | Student progress |

### Subscriptions

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 89 | GET | `/api/v1/subscription/my` | Yes | My subscription |
| 90 | GET | `/api/v1/subscription/plans` | Yes | Available plans |
| 91 | POST | `/api/v1/subscription/store` | Yes | Create subscription |
| 92 | POST | `/api/v1/subscription/cancel` | Yes | Cancel subscription |

### Reports

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 93 | GET | `/api/v1/reports/reading-summary` | Yes | Reading summary |
| 94 | GET | `/api/v1/reports/loan-history` | Yes | Loan history |
| 95 | GET | `/api/v1/reports/fine-history` | Yes | Fine history |

### Miscellaneous

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 96 | GET | `/api/v1/programs` | Yes | List programs |
| 97 | GET | `/api/v1/departments` | Yes | List departments |

### Webhooks (Server-to-Server, not mobile)

| # | Method | Endpoint | Auth | Description |
|---|---|---|---|---|
| 98 | POST | `/api/v1/mpesa/callback` | HMAC | M-Pesa callback |
| 99 | POST | `/api/v1/mpesa/validation` | HMAC | M-Pesa validation |
| 100 | POST | `/api/v1/stripe/webhook` | Signature | Stripe webhook |

**Total: 100 API endpoints** (97 mobile-accessible + 3 webhooks)

---

## 5. Data Models & DTOs

### Core DTOs

```
User {
  id, name, email, phone, avatar, department, program,
  role, is_active, two_factor_enabled, created_at
}

Book {
  id, isbn, title, subtitle, description, language, pages,
  publication_year, edition, cover_image, condition, status,
  category, authors[], subjects[], copies_count, available_copies,
  average_rating, reviews_count, is_featured, digital_assets[]
}

BookCopy {
  id, barcode, rfid_tag, shelf_location, status, condition,
  acquired_at, book: Book
}

Author {
  id, name, biography, nationality, photo, books_count
}

Category {
  id, name, description, parent_id, children[], books_count
}

Publisher {
  id, name, address, phone, email, books_count
}
```

### Circulation DTOs

```
BorrowRecord {
  id, book: Book, book_copy: BookCopy, borrowed_at, due_at,
  returned_at, renewed_at, renewal_count, max_renewals,
  status, notes, issued_by, can_renew
}

Fine {
  id, borrow_record: BorrowRecord, type, amount, paid_amount,
  waived_amount, status, reason, assessed_at, paid_at, balance
}

Reservation {
  id, book: Book, status, reserved_at, expires_at,
  queue_position, notified_at
}
```

### Digital Library DTOs

```
DigitalAsset {
  id, title, description, file_type, file_size, cover_image,
  category, author, language, keywords, access_level,
  allow_download, allow_printing, times_downloaded, times_viewed,
  is_featured, reading_progress, last_read_at
}

ReadingHistory {
  id, digital_asset: DigitalAsset, started_at, completed_at,
  progress, last_page, duration_minutes
}

Recommendation {
  id, book: Book, digital_asset: DigitalAsset, type, score,
  reason, recommended_at
}

Citation {
  id, digital_asset_id, book_id, citation_text, style
}
```

### Communication DTOs

```
Message {
  id, sender: User, subject, body, priority, type,
  status, sent_at, is_read, read_at, attachments_count,
  replies_count, thread_id
}

MessageAttachment {
  id, file_name, file_size, mime_type, download_url
}

Announcement {
  id, title, content, type, status, published_at, expires_at,
  created_by: User
}

Event {
  id, title, description, location, start_date, end_date,
  type, status, created_by: User
}

Bulletin {
  id, title, content, department, status, published_at
}
```

### Assignment DTOs

```
ReadingAssignment {
  id, title, description, resource_type, resource: Book|DigitalAsset,
  due_date, status, type, completed_at, viewed_at,
  created_by: User, student_count?, completion_rate?
}

AssignmentProgress {
  assignment_id, total_students, completed, in_progress,
  not_started, students: [{ student, status, completed_at }]
}
```

### Subscription DTOs

```
Plan {
  id, name, type, billing_cycle, price, currency,
  features[], trial_period_days, is_active
}

Subscription {
  id, plan: Plan, status, start_date, end_date, renewal_date,
  auto_renew, trial_ends_at, grace_period_ends_at
}
```

### Notification DTOs

```
InAppNotification {
  id, type, title, body, icon, action_url, is_read, read_at,
  created_at
}

PushSubscription {
  id, endpoint, is_active, created_at
}

NotificationPreferences {
  email_overdue, email_due_reminder, email_reservations,
  email_fines, email_assignments, email_announcements,
  push_overdue, push_due_reminder, push_messages,
  push_assignments, push_announcements, push_marketing,
  in_app_all
}
```

---

## 6. State Management & Navigation

### BLoC Structure

```
lib/
├── core/
│   ├── auth/
│   │   ├── auth_bloc.dart          # Login, logout, token management
│   │   ├── auth_event.dart
│   │   └── auth_state.dart
│   ├── theme/
│   │   └── theme_bloc.dart         # Light/dark mode
│   └── connectivity/
│       └── connectivity_cubit.dart # Online/offline status
│
├── features/
│   ├── auth/
│   │   ├── login/
│   │   │   ├── login_bloc.dart
│   │   │   ├── login_event.dart
│   │   │   └── login_state.dart
│   │   ├── register/
│   │   ├── forgot_password/
│   │   └── two_factor/
│   │
│   ├── catalog/
│   │   ├── book_list/
│   │   ├── book_detail/
│   │   ├── book_search/
│   │   ├── category_browser/
│   │   └── author_list/
│   │
│   ├── digital_library/
│   │   ├── asset_list/
│   │   ├── asset_detail/
│   │   ├── reader/
│   │   ├── downloads/
│   │   └── reading_history/
│   │
│   ├── circulation/
│   │   ├── active_loans/
│   │   ├── loan_history/
│   │   ├── reservations/
│   │   └── renew/
│   │
│   ├── fines/
│   │   ├── fine_list/
│   │   └── payment/
│   │
│   ├── library_card/
│   │   └── card_bloc.dart
│   │
│   ├── messaging/
│   │   ├── inbox/
│   │   ├── compose/
│   │   ├── message_detail/
│   │   └── announcements/
│   │
│   ├── assignments/
│   │   ├── assignment_list/
│   │   ├── assignment_detail/
│   │   └── create_assignment/     # Lecturer only
│   │
│   ├── notifications/
│   │   └── notification_bloc.dart
│   │
│   ├── recommendations/
│   │   └── recommendation_bloc.dart
│   │
│   ├── subscriptions/
│   │   ├── my_subscription/
│   │   └── plans_list/
│   │
│   └── profile/
│       ├── profile_bloc.dart
│       ├── password_bloc.dart
│       └── preferences_bloc.dart
│
├── shared/
│   ├── widgets/                   # Reusable UI components
│   ├── models/                    # Data models
│   ├── repositories/              # API repositories
│   ├── services/                  # API service, storage, notifications
│   └── utils/                     # Helpers, extensions, constants
```

### Navigation Map (go_router)

```
/                          → Redirect to /login or /dashboard
/splash                    → Splash screen
/onboarding                → Onboarding (first run only)
/login                     → Login screen
/register                  → Registration
/forgot-password           → Password reset
/2fa-verify                → 2FA verification

/dashboard                 → Home dashboard

/catalog                   → Book list
/catalog/search            → Search with filters
/catalog/categories        → Category browser
/catalog/categories/:id    → Books in category
/catalog/authors           → Author list
/catalog/authors/:id       → Author detail
/catalog/books/:id         → Book detail
/catalog/bookmarks         → Bookmarks list

/digital-library           → Digital asset list
/digital-library/:id       → Asset detail
/digital-library/categories → Digital categories
/digital-library/categories/:id → Assets in category
/reader/:id                → In-app reader
/downloads                 → Download manager
/reading-history           → Reading history
/citations/:id             → Citation generator

/loans                     → Active loans (tab)
/loans/history             → Loan history (tab)
/loans/:id                 → Loan detail
/reservations              → My reservations
/reservations/new/:bookId  → Reserve book

/fines                     → My fines
/fines/:id                 → Fine detail
/payment                   → Payment screen

/library-card              → My library card
/scanner                   → QR/barcode scanner

/messages                  → Inbox
/messages/sent             → Sent
/messages/archived         → Archived
/messages/:id              → Message detail
/messages/compose          → Compose message
/announcements             → Announcements list
/announcements/:id         → Announcement detail
/events                    → Events list
/events/:id                → Event detail
/bulletins                 → Bulletin list

/assignments               → My assignments (student)
/assignments/:id           → Assignment detail
/assignments/create        → Create assignment (lecturer only)
/assignments/:id/progress  → Track progress (lecturer only)

/notifications             → Notification center
/notifications/:id         → Notification detail

/recommendations           → Recommendations list
/recommendations/:id       → Recommendation detail

/subscription              → My subscription
/subscription/plans        → Available plans
/subscription/checkout/:id → Checkout flow

/profile                   → My profile
/profile/password          → Change password
/profile/2fa               → 2FA management
/settings                  → App settings
/settings/notifications    → Notification preferences
/about                     → About
```

---

## 7. Offline & Caching Strategy

### Cached Locally (Hive)

| Data | TTL | Refresh |
|---|---|---|
| User profile | Session | On pull-to-refresh |
| Book catalog | 30 min | Background sync |
| Digital asset metadata | 30 min | Background sync |
| Categories | 24 hours | Background sync |
| Active loans | 5 min | On pull-to-refresh |
| Reservations | 5 min | On pull-to-refresh |
| Fines | 5 min | On pull-to-refresh |
| Notifications | 2 min | Push-triggered |
| Messages | 2 min | Push-triggered |
| Library card | Session | On re-login |
| Settings | Session | On change |

### Offline Capabilities

| Feature | Offline Support |
|---|---|
| View cached books | Yes (read-only) |
| View cached digital assets | Metadata only |
| Read downloaded PDFs | Yes (full) |
| View library card | Yes (cached) |
| View active loans | Yes (cached) |
| View notifications | Yes (cached) |
| Search books | Cached results only |
| Send messages | Queue for sync |
| Pay fines | No (requires connection) |
| Reserve books | No (requires connection) |
| Download assets | No (requires connection) |

### Sync Strategy

```
App Launch:
  1. Check connectivity
  2. If online: refresh critical data (loans, fines, notifications)
  3. If offline: serve from Hive cache
  4. Queue pending actions for sync

Background:
  1. Periodic sync every 15 minutes (if online)
  2. Push notifications trigger immediate sync
  3. Pull-to-refresh forces immediate sync
```

---

## 8. Push Notifications

### Setup Flow

```
1. App launches → Request notification permission
2. Get FCM token (firebase_messaging)
3. POST /api/v1/push/subscribe with endpoint + keys
4. Store subscription locally
5. On token refresh → Update subscription
```

### Notification Handling

```
Foreground:
  → Display in-app snackbar
  → Update notification badge
  → Navigate to relevant screen on tap

Background:
  → System notification shown
  → Tap → Deep link to relevant screen

Terminated:
  → System notification shown
  → Tap → App launches → Deep link to screen
```

### Deep Link Mapping

| Notification Type | Navigate To |
|---|---|
| `overdue` | `/loans/:id` |
| `due_reminder` | `/loans/:id` |
| `hold_available` | `/reservations` |
| `fine_assessed` | `/fines/:id` |
| `reservation` | `/reservations` |
| `assignment` | `/assignments/:id` |
| `message` | `/messages/:id` |
| `announcement` | `/announcements/:id` |
| `system` | `/notifications` |

---

## 9. Authentication Flow

### Complete Login Flow

```
┌──────────┐     ┌──────────┐     ┌──────────┐
│  Splash  │────▶│  Login   │────▶│Dashboard │
│  Screen  │     │  Screen  │     │          │
└──────────┘     └────┬─────┘     └──────────┘
                      │
                      ├── [No Token] ──▶ Login Screen
                      │
                      ├── [Has Token] ──▶ Dashboard
                      │
                      └── [2FA Required] ──▶ 2FA Verify
                                                    │
                                                    └──▶ Dashboard

Login Screen:
  ┌───────────────────────────────┐
  │  Email:    [_______________]  │
  │  Password: [_______________]  │
  │  ☑️ Remember me               │
  │                               │
  │  [     Login     ]            │
  │                               │
  │  Forgot password?             │
  │  Don't have account? Register │
  └───────────────────────────────┘

2FA Verify Screen:
  ┌───────────────────────────────┐
  │  Enter 6-digit code from     │
  │  your authenticator app      │
  │                               │
  │  [ _ ][ _ ][ _ ][ _ ][ _ ][ _ ]│
  │                               │
  │  [     Verify     ]           │
  │                               │
  │  Use recovery code            │
  └───────────────────────────────┘
```

### Token Management

```
Storage: flutter_secure_storage (encrypted)

Keys stored:
  - access_token: Sanctum Bearer token
  - refresh_token: Token refresh value
  - token_expiry: Expiration timestamp
  - user_id: Current user ID
  - user_role: Current user role
  - biometric_enabled: Boolean
  - onboarding_completed: Boolean

Token refresh:
  - On 401 response → attempt refresh
  - If refresh fails → redirect to login
  - Clear all stored data on logout
```

### Biometric Login

```
Setup:
  1. User enables in Profile → Security
  2. Verify with local_auth (fingerprint/face)
  3. Store token reference securely
  4. Enable biometric flag

Login:
  1. Splash → Check biometric flag
  2. Prompt biometric authentication
  3. If success → load stored token → Dashboard
  4. If fail → Login screen
```

---

## 10. Permission Matrix

### Screen Access by Role

| Screen | Student | Lecturer |
|---|---|---|
| **A1-A6** Authentication | ✅ | ✅ |
| **B1-B3** Dashboard & Navigation | ✅ | ✅ |
| **C1-C7** Book Catalog | ✅ | ✅ |
| **D1-D6** Digital Library | ✅ | ✅ |
| **E1-E5** My Loans | ✅ | ✅ |
| **F1-F3** Fines | ✅ | ✅ |
| **G1-G2** Library Card | ✅ | ✅ |
| **H1-H6** Communication | ✅ | ✅ |
| **I1-I2** View Assignments | ✅ | ✅ |
| **I3** Create Assignment | ❌ | ✅ |
| **I-Progress** Track Progress | ❌ | ✅ |
| **J1-J2** Notifications | ✅ | ✅ |
| **K1-K2** Recommendations | ✅ | ✅ |
| **L1-L2** Subscriptions | ✅ | ✅ |
| **M1-M5** Profile & Settings | ✅ | ✅ |

### Action Permissions

| Action | Student | Lecturer |
|---|---|---|
| Borrow books (max 5) | ✅ | ✅ (max 10) |
| Renew books | ✅ | ✅ |
| Reserve books | ✅ | ✅ |
| Cancel reservations | ✅ | ✅ |
| Pay fines | ✅ | ✅ |
| Download digital assets | ✅ | ✅ |
| Send messages | ✅ | ✅ |
| Create assignments | ❌ | ✅ |
| Edit own assignments | ❌ | ✅ |
| Delete own assignments | ❌ | ✅ |
| View student progress | ❌ | ✅ |
| Rate & review books | ✅ | ✅ |
| Bookmark books | ✅ | ✅ |
| Generate citations | ✅ | ✅ |
| Subscribe to plans | ✅ | ✅ |

### API Endpoint Permissions

| Endpoint | Student | Lecturer |
|---|---|---|
| All auth endpoints | ✅ | ✅ |
| All profile endpoints | ✅ | ✅ |
| All catalog endpoints | ✅ | ✅ |
| All digital library endpoints | ✅ | ✅ |
| All circulation endpoints | ✅ | ✅ |
| All fine endpoints | ✅ | ✅ |
| All library card endpoints | ✅ | ✅ |
| All messaging endpoints | ✅ | ✅ |
| All notification endpoints | ✅ | ✅ |
| All recommendation endpoints | ✅ | ✅ |
| All subscription endpoints | ✅ | ✅ |
| Student assignment endpoints | ✅ | ✅ |
| Teacher assignment endpoints | ❌ | ✅ |
| Reports endpoints | ✅ | ✅ |

---

## Summary

| Metric | Count |
|---|---|
| **Total Screens** | 48 |
| **Total API Endpoints** | 100 (97 mobile + 3 webhooks) |
| **BLoC Classes** | ~35 |
| **Data Models/DTOs** | ~30 |
| **Feature Modules** | 14 |
| **Push Notification Types** | 9 |
| **Email Notification Types** | 7 (mobile-relevant) |
| **Offline-Cached Features** | 10 |
| **Offline-Capable Features** | 6 |

### Development Phases

| Phase | Scope | Screens | APIs |
|---|---|---|---|
| **Phase 1** | Auth + Dashboard + Profile | 10 | 18 |
| **Phase 2** | Catalog + Digital Library | 13 | 19 |
| **Phase 3** | Circulation + Fines + Library Card | 10 | 17 |
| **Phase 4** | Communication + Notifications | 8 | 21 |
| **Phase 5** | Assignments + Recommendations | 5 | 13 |
| **Phase 6** | Subscriptions + Reports + Polish | 5 | 12 |
