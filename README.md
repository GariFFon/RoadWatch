<h1 align="center">🛣️ RoadWatch</h1>

<p align="center">
  <strong>A citizen-first road issue reporting and resolution platform built with Laravel 13</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.9-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/SQLite-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite">
  <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind">
</p>

---

## 📖 Overview

**RoadWatch** is a full-stack web application that allows citizens to report road issues (potholes, waterlogging, broken streetlights, etc.) to municipal authorities. The platform provides role-based dashboards for **Citizens**, **Engineers**, and **Admins** — each with tailored workflows for reporting, managing, and resolving complaints.

### Key Highlights

- 📸 **Photo & video evidence** at both report filing (before) and resolution (after)
- 🔄 **Strict status transition engine** — engineers follow defined workflows, admins have full override power
- 🛡️ **Role-aware navigation** — every user sees only what's relevant to their role
- 🔍 **Admin slide-over detail panel** — review before/after media side-by-side before marking resolved
- 🔑 **Google OAuth + email/password** dual authentication
- 📡 **REST API v1** powering all frontend panels

---

## 🏗️ Architecture

```
RoadWatch/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/
│   │   │   │   ├── Admin/          # Admin complaint + user management APIs
│   │   │   │   ├── Engineer/       # Engineer assignment + media upload APIs
│   │   │   │   └── Citizen/        # Citizen complaint + feedback APIs
│   │   │   ├── Auth/               # Login, Register, Google OAuth, Set-Password
│   │   │   └── Citizen/            # Blade-facing complaint + feedback controllers
│   │   └── Middleware/
│   │       ├── CheckRole.php       # Role guard (citizen | engineer | admin)
│   │       └── EnsurePasswordIsSet.php
│   ├── Models/
│   │   ├── User.php                # Roles, Google OAuth, profile photo
│   │   ├── Complaint.php           # VALID_TRANSITIONS state machine
│   │   ├── ComplaintMedia.php      # stage: before | after
│   │   ├── StatusHistory.php       # Immutable audit trail
│   │   ├── Feedback.php            # Citizen ratings (1-5 stars)
│   │   └── Category.php
│   └── Http/Resources/
│       └── ComplaintResource.php   # Unified API shape for all roles
├── resources/views/
│   ├── welcome.blade.php           # Public landing page (role-aware nav)
│   ├── admin/                      # Admin dashboard, complaints, users
│   ├── engineer/                   # Engineer assignment list + detail
│   └── citizen/                    # Citizen complaint list, create, show
├── routes/
│   ├── web.php                     # Blade view routes
│   └── api.php                     # REST API v1 routes
└── docs/
    └── API_REFERENCE.md            # Complete API documentation
```

---

## 👥 Roles & Portals

| Role | Portal | Access |
|------|--------|--------|
| **Citizen** | `/citizen/complaints` | File complaints, upload before-photos, track status, submit feedback |
| **Engineer** | `/engineer/complaints` | View assigned complaints, upload after-work evidence (photos/videos) |
| **Admin** | `/admin/dashboard` | Manage all complaints, assign engineers, review before+after media, update status |

After login, each user is **automatically redirected** to their role's panel.

---

## 🔄 Complaint Workflow

```
Citizen files complaint (with before photos)
         ↓
Admin reviews → assigns to Engineer
         ↓
Engineer works on issue → uploads After photos/videos as proof
         ↓
Admin reviews before + after evidence
   ✅ Satisfied  →  marks "Resolved"
   ❌ Not happy  →  sends back "In Progress" with remarks
         ↓
Citizen submits 1–5 star feedback
```

### Status Transition Rules

**Engineer** (enforced by `VALID_TRANSITIONS`):
```
pending  ──►  under_review  ──►  in_progress  ──►  resolved
   │               │                  │
   └──► rejected   └──► rejected       └──► rejected
```

**Admin** — unrestricted, can set any status at any time.

---

## 🚀 Getting Started

### Prerequisites

- PHP 8.4+
- Composer
- Node.js & npm
- SQLite (default) or MySQL/PostgreSQL

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/GariFFon/RoadWatch.git
cd RoadWatch

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Set up environment
cp .env.example .env
php artisan key:generate

# 5. Configure database (SQLite by default — no setup needed)
touch database/database.sqlite

# 6. Run migrations and seed categories
php artisan migrate --seed

# 7. Create storage symlink (for uploaded media)
php artisan storage:link

# 8. Build frontend assets
npm run dev
```

### Google OAuth Setup (optional)

Add these to your `.env`:

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

### Start the Server

```bash
php artisan serve
# → http://localhost:8000
```

---

## 🌐 Key URLs

| URL | Description |
|-----|-------------|
| `http://localhost:8000` | Public landing page |
| `http://localhost:8000/login` | Login |
| `http://localhost:8000/register` | Register |
| `http://localhost:8000/citizen/complaints` | Citizen portal |
| `http://localhost:8000/engineer/complaints` | Engineer portal |
| `http://localhost:8000/admin/dashboard` | Admin dashboard |
| `http://localhost:8000/admin/complaints` | Admin complaint management |
| `http://localhost:8000/admin/users` | User & role management |

---

## 📡 API Reference

Full REST API documentation is in [`docs/API_REFERENCE.md`](docs/API_REFERENCE.md).

**Base URL:** `http://localhost:8000/api/v1`

| # | Group | Endpoints |
|---|-------|-----------|
| 1 | Public | `GET /categories` · `GET /complaint-options` |
| 2 | Auth | `GET /me` |
| 3 | Citizen — Complaints | List · Create · Show · Delete |
| 4 | Citizen — Media | Upload · Delete |
| 5 | Citizen — Feedback | Submit star rating |
| 6 | Engineer — Complaints | List · Show · Update status |
| 7 | Engineer — Media | Upload after-work evidence · Delete |
| 8 | Admin — Complaints | List all · Show detail · Assign · Update status |
| 9 | Admin — Users | List · Change role |

---

## 🗄️ Database Schema

| Table | Description |
|-------|-------------|
| `users` | Citizens, Engineers, Admins with Google OAuth support |
| `categories` | Road issue categories (Pothole, Waterlogging, etc.) |
| `complaints` | Core complaint records with status state machine |
| `complaint_media` | Before/after photos and videos (stored in `public/storage`) |
| `status_histories` | Immutable audit log of every status change |
| `feedbacks` | Citizen star ratings for resolved complaints |

---

## 🔐 Authentication

- **Email + Password** — standard Breeze authentication
- **Google OAuth** — via Laravel Socialite; new Google users are prompted to set a password on first login
- **Role guard** — `CheckRole` middleware enforces access to `/citizen`, `/engineer`, `/admin` routes
- **Session-based API** — all API endpoints use `auth:web` session cookies (no tokens needed)

---

## 📦 Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend Framework | Laravel 13 (PHP 8.4) |
| Authentication | Laravel Breeze + Laravel Socialite (Google) |
| Database | SQLite (dev) / MySQL (prod) |
| File Storage | Laravel Storage → `public/storage` |
| Frontend | Blade templates + Vanilla JS + Axios |
| Styling | Tailwind CSS + custom inline styles |
| API | RESTful JSON API (session-based auth) |

---

## 📁 Media Storage

All uploaded files (complaint photos/videos) are stored in:
```
storage/app/public/complaints/{complaint_id}/images/
storage/app/public/complaints/{complaint_id}/videos/
```

Accessible via: `http://localhost:8000/storage/complaints/...`

Run `php artisan storage:link` once to create the public symlink.

---

## 🧑‍💻 Author

**Gourav Dash** — [GitHub: GariFFon](https://github.com/GariFFon)

---

## 📄 License

This project is open-source under the [MIT License](https://opensource.org/licenses/MIT).
