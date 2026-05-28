
# RoadWatch — Comprehensive Project Report

**Project Title:** RoadWatch — Citizen Road Issue Reporting & Resolution Platform  
**Author:** Gourav Dash (GitHub: GariFFon)  
**Repository:** https://github.com/GariFFon/RoadWatch  
**Report Date:** May 2026  
**Version:** 1.0  

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Project Objectives](#2-project-objectives)
3. [Technology Stack](#3-technology-stack)
4. [System Architecture Overview](#4-system-architecture-overview)
5. [Database Design & Schema](#5-database-design--schema)
6. [Application Layers & Directory Structure](#6-application-layers--directory-structure)
7. [Controllers — Detailed Reference](#7-controllers--detailed-reference)
8. [Middleware — Detailed Reference](#8-middleware--detailed-reference)
9. [Models & Eloquent ORM](#9-models--eloquent-orm)
10. [Role-Based Access Control](#10-role-based-access-control)
11. [Authentication System](#11-authentication-system)
12. [Complaint Lifecycle & State Machine](#12-complaint-lifecycle--state-machine)
13. [REST API (v1) Reference](#13-rest-api-v1-reference)
14. [Frontend — Views & JavaScript](#14-frontend--views--javascript)
15. [Media Storage Strategy](#15-media-storage-strategy)
16. [Docker & Containerization](#16-docker--containerization)
17. [CI/CD Pipeline — GitHub Actions](#17-cicd-pipeline--github-actions)
18. [Deployment on Railway](#18-deployment-on-railway)
19. [Testing Strategy](#19-testing-strategy)
20. [Security Considerations](#20-security-considerations)
21. [API Resource Transformation](#21-api-resource-transformation)
22. [Environment Configuration](#22-environment-configuration)
23. [Workflow Diagrams](#23-workflow-diagrams)
24. [Challenges & Design Decisions](#24-challenges--design-decisions)
25. [Future Enhancements & Conclusion](#25-future-enhancements--conclusion)

---

## 1. Executive Summary

**RoadWatch** is a full-stack, citizen-first web application built using **Laravel 13** (PHP 8.4) that enables citizens to digitally report road infrastructure issues — such as potholes, waterlogging, broken streetlights, encroachments, and garbage accumulation — directly to the relevant municipal authorities. It eliminates the friction of traditional, paper-based complaint systems and provides real-time, transparent tracking of resolution progress.

The platform is designed around **three distinct user roles**:

- **Citizens** — file complaints with photo/video evidence, track progress, and submit feedback after resolution.
- **Engineers** — receive assignments, document their repair work with "after" media, and update progress statuses.
- **Admins** — oversee the full system, assign complaints to engineers, review before/after media evidence, approve or reject resolutions, and manage user accounts.

RoadWatch is a production-grade application deployed on **Railway** via a containerised **Docker** image. It has a fully automated **CI/CD pipeline** using **GitHub Actions** with three distinct quality jobs: automated PHPUnit testing, Laravel Pint code style enforcement, and PHP syntax checking. The application exposes a **versioned REST API (v1)** for all frontend interactions, backed by a **MySQL 8.0** database in production (SQLite for local dev), and uses **AWS S3** for media file storage.

---

## 2. Project Objectives

The RoadWatch project was designed to meet the following core objectives:

| # | Objective | Implementation |
|---|-----------|---------------|
| 1 | Citizen complaint filing with evidence | Multi-file upload (images/videos), GPS coordinates, category tagging |
| 2 | Transparent status tracking | 6-state finite state machine with full audit trail |
| 3 | Efficient engineer workflow | Role-locked assignment list, "after" media upload, status progression |
| 4 | Admin oversight and control | Full complaint view, engineer assignment, before/after media review panel |
| 5 | Dual authentication | Email/password (Breeze) + Google OAuth (Socialite) |
| 6 | Media evidence management | Staged media (before / during / after), stored on AWS S3 |
| 7 | Citizen feedback collection | 1–5 star rating with optional comment after resolution |
| 8 | Production-ready deployment | Docker + Railway + CI/CD |
| 9 | Code quality enforcement | PHPUnit tests, Laravel Pint, PHP syntax checks on every push |

---

## 3. Technology Stack

### 3.1 Backend

| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 8.4 | Server-side language (required by Symfony 8.x components) |
| **Laravel Framework** | 13.9 | MVC web framework — routing, ORM, middleware, queues |
| **Laravel Breeze** | 2.4 | Authentication scaffolding (login, register, email verification, password reset) |
| **Laravel Socialite** | 5.27 | Google OAuth 2.0 third-party authentication driver |
| **Spatie Laravel Permission** | 7.4 | Role and permission management (roles: citizen, engineer, admin) |
| **Laravel Tinker** | 3.0 | Interactive REPL for development and debugging |
| **League Flysystem AWS S3 v3** | 3.0 | AWS S3 storage adapter for uploaded media files |
| **Composer** | 2 | PHP dependency management and autoloading |

### 3.2 Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| **Blade Templates** | Laravel 13 | Server-rendered HTML templating engine |
| **Tailwind CSS** | 3.x | Utility-first CSS framework for styling |
| **Alpine.js** | 3.x | Lightweight JavaScript for reactive UI components |
| **Axios** | 1.16 | Promise-based HTTP client for API calls from the browser |
| **Chart.js** | 4.5 | Data visualisation (admin dashboard analytics) |
| **Leaflet.js** | 1.9 | Open-source JavaScript library for interactive maps (GPS pinning) |
| **Vite** | 8.x | Frontend bundler and development server |
| **PostCSS + Autoprefixer** | — | CSS transformation pipeline |

### 3.3 Database

| Technology | Environment | Details |
|------------|-------------|---------|
| **MySQL 8.0** | Production (Railway) & CI | Primary relational database, mirrors production exactly in CI |
| **SQLite** | Local development | Zero-config local development fallback |

### 3.4 Infrastructure & DevOps

| Technology | Purpose |
|------------|---------|
| **Docker** | Containerisation for consistent builds and Railway deployment |
| **Railway** | Cloud PaaS deployment platform (replaces Heroku for PHP apps) |
| **AWS S3** (region: `ap-south-1`) | Production media storage (complaint photos and videos) |
| **GitHub Actions** | CI/CD pipeline — 3 automated jobs on every push |

### 3.5 Development Tools

| Tool | Purpose |
|------|---------|
| **Laravel Pint** | PHP code style fixer (PSR-12/Laravel standards) |
| **PHPUnit 12.5** | PHP unit and feature testing framework |
| **Faker (FakerPHP)** | Realistic fake data generation for tests and seeders |
| **Mockery** | PHP mock object framework for isolated unit tests |
| **Laravel Pail** | Real-time log tailing during development |
| **Laravel Pao** | Developer tooling optimisation |
| **Concurrently** | Runs multiple npm scripts simultaneously in dev mode |
| **Nunomaduro Collision** | Beautiful error reporting for Artisan commands |

---

## 4. System Architecture Overview

RoadWatch follows the **Model–View–Controller (MVC)** architectural pattern as prescribed by Laravel, with an additional **API layer** (versioned REST API at `/api/v1/`) that decouples the frontend views from the backend data logic.

```
┌─────────────────────────────────────────────────────────────────────┐
│                          CLIENT BROWSER                             │
│   Blade Templates (SSR)   ←→   Axios HTTP Calls (/api/v1/...)       │
└───────────────────────────────────┬─────────────────────────────────┘
                                    │ HTTP/HTTPS
┌───────────────────────────────────▼─────────────────────────────────┐
│                     LARAVEL 13 APPLICATION                          │
│                                                                     │
│  ┌─────────────┐   ┌──────────────────┐   ┌──────────────────────┐ │
│  │  Middleware  │   │    Controllers   │   │    API Resources     │ │
│  │ ─────────── │   │ ──────────────── │   │ ─────────────────── │ │
│  │ CheckRole   │ → │ Auth/*           │ → │ ComplaintResource   │ │
│  │ EnsurePass  │   │ Citizen/*        │   │ (JSON transformer)  │ │
│  │ Verified    │   │ Engineer/*       │   └──────────────────────┘ │
│  │ Auth        │   │ Admin/*          │                             │
│  └─────────────┘   │ Api/V1/*         │   ┌──────────────────────┐ │
│                    └──────────────────┘   │    Form Requests     │ │
│                                           │ (validation rules)   │ │
│  ┌─────────────────────────────────────┐  └──────────────────────┘ │
│  │              MODELS (ORM)           │                            │
│  │ User · Complaint · ComplaintMedia   │                            │
│  │ StatusHistory · Feedback · Category │                            │
│  └───────────────────┬─────────────────┘                           │
└──────────────────────┼──────────────────────────────────────────────┘
                       │
       ┌───────────────┼───────────────────────┐
       ▼               ▼                       ▼
 ┌──────────┐   ┌──────────────┐      ┌───────────────┐
 │  MySQL   │   │   AWS S3     │      │  Laravel      │
 │  8.0     │   │  (ap-south-1)│      │  Cache/Queue  │
 └──────────┘   └──────────────┘      └───────────────┘
```

### Architectural Decisions

1. **Hybrid Rendering** — Server-rendered Blade templates handle page routing and initial HTML. All dynamic data (complaint lists, status updates, media) is fetched via AJAX calls to the `/api/v1/` JSON endpoints. This provides the SEO and simplicity of SSR while enabling reactive UI updates without a full page reload.

2. **Versioned API** — The REST API is namespaced under `/api/v1/` allowing for future breaking changes under `/api/v2/` without disrupting existing clients.

3. **Session-Based API Auth** — Rather than tokens (Sanctum/Passport), the API uses the same session cookie as the web application (`auth:web`). This simplifies the auth story for a monolithic app where both the web and API are on the same origin.

4. **State Machine in the Model** — The `VALID_TRANSITIONS` constant in `Complaint.php` acts as a state machine guard. All status changes go through `updateStatus()` which enforces allowed transitions atomically within a database transaction.

---

## 5. Database Design & Schema

The application has **15 migration files** creating a total of **14 database tables** (including Spatie permission tables and Laravel system tables).

### 5.1 Core Application Tables

#### Table 1: `users`

The central identity table. Supports both email/password and Google OAuth authentication, and stores role (citizen/engineer/admin) directly on the record.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| `name` | VARCHAR(255) | NOT NULL | Full display name |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | Login email |
| `phone` | VARCHAR(255) | UNIQUE, NULLABLE | Contact phone number |
| `password` | VARCHAR(255) | NULLABLE | Hashed bcrypt password (null for Google OAuth users) |
| `role` | ENUM | DEFAULT 'citizen' | `citizen` / `engineer` / `admin` |
| `gender` | ENUM | NULLABLE | `male` / `female` / `other` / `prefer_not_to_say` |
| `profile_photo` | VARCHAR(255) | NULLABLE | S3 key or local path |
| `profile_banner_url` | VARCHAR(255) | NULLABLE | S3 URL for profile banner image |
| `address` | TEXT | NULLABLE | Street address |
| `city` | VARCHAR(255) | NULLABLE | City |
| `state` | VARCHAR(255) | NULLABLE | State / province |
| `latitude` | DECIMAL(10,7) | NULLABLE | GPS latitude |
| `longitude` | DECIMAL(10,7) | NULLABLE | GPS longitude |
| `notification_preferences` | JSON | NULLABLE | `{"email":true,"sms":false,"push":true}` |
| `is_active` | BOOLEAN | DEFAULT TRUE | Account active flag |
| `is_verified` | BOOLEAN | DEFAULT FALSE | Manual verification flag |
| `last_login_at` | TIMESTAMP | NULLABLE | Last successful login timestamp |
| `email_verified_at` | TIMESTAMP | NULLABLE | Null until email link clicked |
| `auth_provider` | ENUM | DEFAULT 'email' | `email` / `google` |
| `google_id` | VARCHAR(255) | UNIQUE, NULLABLE | Google UID |
| `google_avatar` | VARCHAR(255) | NULLABLE | Google profile picture URL |
| `password_set` | BOOLEAN | DEFAULT TRUE | FALSE for new Google OAuth users until they set a password |
| `remember_token` | VARCHAR(100) | NULLABLE | Laravel "remember me" token |
| `created_at` / `updated_at` | TIMESTAMP | — | Eloquent timestamps |

**Additional tables in same migration:** `password_reset_tokens`, `sessions`

---

#### Table 2: `categories`

Defines the classification taxonomy for road issues. Seeded on every deployment via `CategorySeeder`.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK | Primary key |
| `name` | VARCHAR(255) | NOT NULL | Display name (e.g., "Pothole") |
| `slug` | VARCHAR(255) | UNIQUE | URL-safe identifier |
| `icon` | VARCHAR(255) | NULLABLE | Emoji or icon CSS class |
| `color` | VARCHAR(255) | NULLABLE | Tailwind color name for UI badges |
| `description` | TEXT | NULLABLE | Longer description |
| `is_active` | BOOLEAN | DEFAULT TRUE | Hidden categories excluded from dropdowns |
| `sort_order` | INTEGER | DEFAULT 0 | Display ordering |
| `created_at` / `updated_at` | TIMESTAMP | — | Eloquent timestamps |

**Example categories seeded:** Pothole, Waterlogging, Broken Streetlight, Road Encroachment, Garbage Dump, Damaged Signage.

---

#### Table 3: `complaints`

The heart of the application. Each row represents a single citizen complaint, with full lifecycle tracking.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK | Internal ID |
| `complaint_number` | VARCHAR(255) | UNIQUE | Human-readable ref: `RW-2026-00042` |
| `user_id` | BIGINT UNSIGNED | FK → users | Citizen who filed |
| `category_id` | BIGINT UNSIGNED | FK → categories | Issue type |
| `assigned_to` | BIGINT UNSIGNED | FK → users, NULLABLE | Engineer assigned to fix |
| `title` | VARCHAR(255) | NOT NULL | Short complaint title |
| `description` | TEXT | NOT NULL | Detailed description |
| `latitude` | DECIMAL(10,7) | NOT NULL | GPS latitude of the issue |
| `longitude` | DECIMAL(10,7) | NOT NULL | GPS longitude of the issue |
| `location` | VARCHAR(255) | NOT NULL | Human-readable address |
| `severity` | ENUM | DEFAULT 'medium' | `low` / `medium` / `high` / `emergency` |
| `status` | ENUM | DEFAULT 'pending' | State machine status (6 values) |
| `votes_count` | UNSIGNED INT | DEFAULT 0 | Community upvote counter |
| `views_count` | UNSIGNED INT | DEFAULT 0 | Page view counter |
| `is_anonymous` | BOOLEAN | DEFAULT FALSE | Hide reporter identity |
| `is_duplicate` | BOOLEAN | DEFAULT FALSE | Duplicate flag |
| `duplicate_of` | BIGINT UNSIGNED | FK → complaints, NULLABLE | Parent complaint if duplicate |
| `rejection_reason` | TEXT | NULLABLE | Admin's reason for rejection |
| `estimated_completion` | DATE | NULLABLE | Estimated fix date |
| `resolved_at` | TIMESTAMP | NULLABLE | When engineer marked done |
| `engineer_rating` | TINYINT | NULLABLE | Admin rating of engineer work (1–5) |
| `engineer_rating_comment` | TEXT | NULLABLE | Admin's comment on engineer work |
| `rated_by` | BIGINT UNSIGNED | FK → users, NULLABLE | Admin who rated |
| `rated_at` | TIMESTAMP | NULLABLE | When rating was given |
| `created_at` / `updated_at` | TIMESTAMP | — | Eloquent timestamps |

**Indexes:** `status`, `severity`, `category_id`, `assigned_to`, `(latitude, longitude)` composite

---

#### Table 4: `complaint_media`

Stores all uploaded files (images and videos) associated with complaints. Supports cloud storage (S3) with staged metadata (before / during / after).

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK | Primary key |
| `complaint_id` | BIGINT UNSIGNED | FK → complaints, CASCADE DELETE | Parent complaint |
| `uploaded_by` | BIGINT UNSIGNED | FK → users | Uploader (citizen or engineer) |
| `file_type` | ENUM | NOT NULL | `image` / `video` |
| `stage` | ENUM | DEFAULT 'before' | `before` / `during` / `after` |
| `original_name` | VARCHAR(255) | NOT NULL | Original filename |
| `mime_type` | VARCHAR(255) | NOT NULL | MIME type (e.g., `image/jpeg`) |
| `size_bytes` | BIGINT UNSIGNED | NOT NULL | File size in bytes |
| `cloud_disk` | VARCHAR(255) | DEFAULT 's3' | Storage driver: `s3` / `public` |
| `cloud_path` | VARCHAR(255) | NOT NULL | Storage key / path |
| `cloud_url` | VARCHAR(255) | NOT NULL | Full CDN URL |
| `cloud_public_id` | VARCHAR(255) | NULLABLE | Cloudinary public ID (if used) |
| `width` | UNSIGNED INT | NULLABLE | Image width in pixels |
| `height` | UNSIGNED INT | NULLABLE | Image height in pixels |
| `duration` | FLOAT | NULLABLE | Video duration in seconds |
| `thumbnail_url` | VARCHAR(255) | NULLABLE | Video thumbnail URL |
| `is_flagged` | BOOLEAN | DEFAULT FALSE | Content moderation flag |
| `sort_order` | UNSIGNED INT | DEFAULT 0 | Display ordering |
| `created_at` | TIMESTAMP | useCurrent() | Immutable creation timestamp |

**Indexes:** `(complaint_id, stage)`, `(complaint_id, file_type)`

---

#### Table 5: `status_histories`

An **immutable audit trail** of every status transition on every complaint. Written atomically alongside each `updateStatus()` call. Never updated — only inserted.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK | Primary key |
| `complaint_id` | BIGINT UNSIGNED | FK → complaints, CASCADE DELETE | Parent complaint |
| `changed_by` | BIGINT UNSIGNED | FK → users | Who triggered the transition |
| `old_status` | VARCHAR(255) | NULLABLE | Previous status (NULL for initial 'pending') |
| `new_status` | VARCHAR(255) | NOT NULL | New status after transition |
| `remarks` | TEXT | NULLABLE | Optional note explaining the change |
| `time_in_previous_status` | UNSIGNED INT | NULLABLE | Seconds spent in old status |
| `created_at` | TIMESTAMP | useCurrent() | Immutable — no `updated_at` |

**Indexes:** `complaint_id`, `new_status`

---

#### Table 6: `feedbacks`

Citizen satisfaction rating submitted after a complaint is verified/resolved. Enforces one feedback per citizen per complaint.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK | Primary key |
| `complaint_id` | BIGINT UNSIGNED | FK → complaints, CASCADE DELETE | Parent complaint |
| `user_id` | BIGINT UNSIGNED | FK → users, CASCADE DELETE | Citizen submitting feedback |
| `rating` | TINYINT UNSIGNED | NOT NULL | 1 (Very Poor) to 5 (Excellent) |
| `comment` | TEXT | NULLABLE | Optional written feedback |
| `is_anonymous` | BOOLEAN | DEFAULT FALSE | Hide name in feedback display |
| `created_at` / `updated_at` | TIMESTAMP | — | Eloquent timestamps |

**Constraints:** UNIQUE(`complaint_id`, `user_id`), INDEX(`rating`)

---

#### Table 7: `complaint_upvotes`

Citizens can upvote complaints to raise their priority visibility to admins. One upvote per citizen per complaint.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGINT UNSIGNED | PK | Primary key |
| `complaint_id` | BIGINT UNSIGNED | FK → complaints | Parent complaint |
| `user_id` | BIGINT UNSIGNED | FK → users | Citizen who upvoted |
| `created_at` | TIMESTAMP | useCurrent() | Timestamp |

**Constraint:** UNIQUE(`complaint_id`, `user_id`)

---

#### Table 8: `notifications`

Standard Laravel notifications table. UUID-based primary key with polymorphic `notifiable` relationship for push/email notifications.

| Column | Description |
|--------|-------------|
| `id` | UUID (primary key) |
| `type` | Notification class name |
| `notifiable_type` / `notifiable_id` | Polymorphic target |
| `data` | JSON notification payload |
| `read_at` | Nullable timestamp |

---

#### Table 9: `complaint_comments` *(Scaffold)*

A minimal scaffold table created for future expansion (internal comments between admin and engineer on a complaint).

---

### 5.2 Spatie Permission Tables (5 tables)

These tables power the `spatie/laravel-permission` package and enable the role/permission system:

| Table | Purpose |
|-------|---------|
| `permissions` | Available permission names (e.g., `edit-users`) |
| `roles` | Role definitions (`citizen`, `engineer`, `admin`) |
| `model_has_permissions` | Pivot: direct user→permission assignments |
| `model_has_roles` | Pivot: user→role assignments |
| `role_has_permissions` | Pivot: role→permission assignments |

---

### 5.3 Entity Relationship Summary

```
users ──< complaints (via user_id, filed by citizen)
users ──< complaints (via assigned_to, assigned to engineer)
categories ──< complaints
complaints ──< complaint_media
complaints ──< status_histories
complaints ──< complaint_upvotes
complaints ──< feedbacks
complaints >── complaints (duplicate_of, self-referential)
users >── roles (via model_has_roles)
```

---

## 6. Application Layers & Directory Structure

```
RoadWatch/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php              # Base controller
│   │   │   ├── ProfileController.php       # Profile edit/delete
│   │   │   ├── Api/V1/
│   │   │   │   ├── Auth/
│   │   │   │   │   └── MeController.php            # GET /api/v1/me
│   │   │   │   ├── Admin/
│   │   │   │   │   ├── ComplaintController.php      # Admin API
│   │   │   │   │   └── UserController.php           # User role management API
│   │   │   │   ├── Engineer/
│   │   │   │   │   ├── ComplaintController.php      # Engineer API
│   │   │   │   │   └── MediaController.php          # After-work media API
│   │   │   │   ├── Citizen/
│   │   │   │   │   ├── ComplaintController.php      # Citizen API
│   │   │   │   │   ├── FeedbackController.php       # Feedback API
│   │   │   │   │   └── MediaController.php          # Before-media API
│   │   │   │   ├── CategoryController.php           # Public categories API
│   │   │   │   ├── ComplaintOptionsController.php   # Enums/options API
│   │   │   │   └── ProfileMediaController.php       # Photo/banner upload API
│   │   │   ├── Auth/
│   │   │   │   ├── AuthenticatedSessionController.php
│   │   │   │   ├── ConfirmablePasswordController.php
│   │   │   │   ├── EmailVerificationNotificationController.php
│   │   │   │   ├── EmailVerificationPromptController.php
│   │   │   │   ├── GoogleAuthController.php         # OAuth 2.0
│   │   │   │   ├── NewPasswordController.php
│   │   │   │   ├── PasswordController.php
│   │   │   │   ├── PasswordResetLinkController.php
│   │   │   │   ├── RegisteredUserController.php
│   │   │   │   ├── SetPasswordController.php        # Google OAuth onboarding
│   │   │   │   └── VerifyEmailController.php
│   │   │   └── Citizen/
│   │   │       ├── ComplaintController.php          # Blade complaint views
│   │   │       └── FeedbackController.php           # Blade feedback views
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php
│   │   │   └── EnsurePasswordIsSet.php
│   │   ├── Requests/                                # Form Request validators
│   │   └── Resources/
│   │       └── ComplaintResource.php                # API JSON transformer
│   ├── Models/
│   │   ├── User.php
│   │   ├── Complaint.php
│   │   ├── ComplaintMedia.php
│   │   ├── StatusHistory.php
│   │   ├── Feedback.php
│   │   └── Category.php
│   ├── Providers/
│   └── View/
├── database/
│   ├── migrations/          # 15 migration files
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   └── CategorySeeder.php
│   └── factories/
├── resources/
│   ├── views/
│   │   ├── welcome.blade.php
│   │   ├── admin/
│   │   ├── engineer/
│   │   ├── citizen/
│   │   ├── auth/
│   │   ├── layouts/
│   │   ├── components/
│   │   └── partials/
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php
│   ├── api.php
│   ├── auth.php
│   └── console.php
├── .github/workflows/ci.yml  # CI/CD pipeline
├── Dockerfile
├── docker-start.sh
├── railpack.json
├── composer.json
├── package.json
└── vite.config.js
```

---

## 7. Controllers — Detailed Reference

### 7.1 Auth Controllers (`app/Http/Controllers/Auth/`)

There are **11 Auth controllers** in total, scaffolded by Laravel Breeze with custom modifications.

| Controller | Route(s) | Purpose |
|------------|----------|---------|
| `AuthenticatedSessionController` | `GET /login`, `POST /login`, `POST /logout` | Standard email/password login and logout with role-based post-login redirect |
| `RegisteredUserController` | `GET /register`, `POST /register` | New user registration; assigns `citizen` role; triggers email verification |
| `EmailVerificationPromptController` | `GET /verify-email` | Shows "check your inbox" page for unverified users |
| `EmailVerificationNotificationController` | `POST /email/verification-notification` | Resends verification email |
| `VerifyEmailController` | `GET /verify-email/{id}/{hash}` | Handles click from verification email link |
| `PasswordResetLinkController` | `GET /forgot-password`, `POST /forgot-password` | Sends password reset email |
| `NewPasswordController` | `GET /reset-password/{token}`, `POST /reset-password` | Processes password reset form |
| `PasswordController` | `PUT /password` | Changes password from profile settings |
| `ConfirmablePasswordController` | `GET /confirm-password`, `POST /confirm-password` | Re-confirms password for sensitive actions |
| `GoogleAuthController` | `GET /auth/google`, `GET /auth/google/callback` | Full Google OAuth 2.0 flow (3 cases: existing Google user, email merge, new user) |
| `SetPasswordController` | `GET /set-password`, `POST /set-password` | Mandatory password setup for new Google OAuth users |

### 7.2 Citizen Web Controllers (`app/Http/Controllers/Citizen/`)

These controllers serve the server-rendered (Blade) citizen portal pages:

| Controller | Methods | Routes |
|------------|---------|--------|
| `ComplaintController` | `index()`, `create()`, `store()`, `show()` | `/citizen/complaints`, `/citizen/complaints/create`, `/citizen/complaints/{id}` |
| `FeedbackController` | `store()` | `POST /citizen/complaints/{complaint}/feedback` |

### 7.3 Shared Web Controller

| Controller | Methods | Routes |
|------------|---------|--------|
| `ProfileController` | `edit()`, `update()`, `destroy()` | `/profile` |

### 7.4 API Controllers (`app/Http/Controllers/Api/V1/`)

#### Common / Shared

| Controller | Endpoint | Description |
|------------|----------|-------------|
| `CategoryController` | `GET /api/v1/categories` | Returns all active categories (public — no auth required) |
| `ComplaintOptionsController` | `GET /api/v1/complaint-options` | Returns severity enum values and category list |
| `ProfileMediaController` | `POST /api/v1/profile/photo`, `POST /api/v1/profile/banner`, `DELETE /api/v1/profile/banner` | Profile photo and banner management (all roles) |

#### Auth

| Controller | Endpoint | Description |
|------------|----------|-------------|
| `MeController` | `GET /api/v1/me` | Returns authenticated user data (id, name, email, role, avatar) |

#### Citizen API

| Controller | Endpoints | Description |
|------------|-----------|-------------|
| `Citizen\ComplaintController` | `GET /api/v1/citizen/complaints` | Lists all complaints filed by the authenticated citizen |
| | `POST /api/v1/citizen/complaints` | Creates a new complaint with category, location, severity, anonymity |
| | `GET /api/v1/citizen/complaints/{id}` | Shows full complaint detail (with media, history, feedback) |
| | `DELETE /api/v1/citizen/complaints/{id}` | Deletes a complaint (only if still pending, no media uploaded) |
| `Citizen\MediaController` | `POST /api/v1/citizen/complaints/{id}/media` | Uploads before-photo evidence (validated: image/video, max 64MB) |
| | `DELETE /api/v1/citizen/media/{id}` | Deletes a specific media record from S3 and database |
| `Citizen\FeedbackController` | `POST /api/v1/citizen/complaints/{id}/feedback` | Submits 1–5 star rating + optional comment |

#### Engineer API

| Controller | Endpoints | Description |
|------------|-----------|-------------|
| `Engineer\ComplaintController` | `GET /api/v1/engineer/complaints` | Lists all complaints assigned to the authenticated engineer |
| | `GET /api/v1/engineer/complaints/{id}` | Full complaint detail including before-media |
| | `PATCH /api/v1/engineer/complaints/{id}/status` | Advances status following the state machine (`under_review` → `in_progress` → `awaiting_verification`) |
| `Engineer\MediaController` | `POST /api/v1/engineer/complaints/{id}/media` | Uploads after-work evidence photos/videos |
| | `DELETE /api/v1/engineer/media/{id}` | Removes after-media record |

#### Admin API

| Controller | Endpoints | Description |
|------------|-----------|-------------|
| `Admin\ComplaintController` | `GET /api/v1/admin/complaints` | Lists all complaints system-wide (with pagination, filters: status, severity, category) |
| | `GET /api/v1/admin/complaints/{id}` | Full complaint detail with before+after media side-by-side |
| | `PATCH /api/v1/admin/complaints/{id}/assign` | Assigns an engineer to a complaint |
| | `PATCH /api/v1/admin/complaints/{id}/status` | Updates status (admin has full override power, no transition restrictions) |
| | `POST /api/v1/admin/complaints/{id}/rate` | Rates the engineer's work quality (1–5 stars + comment) |
| `Admin\UserController` | `GET /api/v1/admin/users` | Lists all users with filters by role |
| | `PATCH /api/v1/admin/users/{id}/role` | Changes a user's role (citizen ↔ engineer ↔ admin) |

**Total API Controllers: 12** (spread across 4 namespace groups)  
**Total Auth Controllers: 11**  
**Total Web Blade Controllers: 3**  
**Grand Total Controllers: 26**

---

## 8. Middleware — Detailed Reference

RoadWatch has **2 custom middleware** classes, in addition to the standard Laravel middleware stack.

### 8.1 `CheckRole` (`app/Http/Middleware/CheckRole.php`)

**Registration alias:** `role`  
**Usage:** `->middleware('role:citizen')` or `->middleware('role:admin,engineer')`

**Purpose:** Guards routes against users with the wrong role. Checks the `role` column on the `users` table directly (not Spatie's permission table — this keeps the check fast and simple for route-level access control).

**Logic:**
1. If the user is not authenticated → redirect to `login`.
2. If `$user->role` is not in the allowed `$roles` array → abort with HTTP 403.
3. Otherwise → pass the request to the next middleware/controller.

**Applied to:**
- `/citizen/*` routes — requires `role:citizen`
- `/engineer/*` routes — requires `role:engineer`
- `/admin/*` routes — requires `role:admin`
- All corresponding `/api/v1/citizen/*`, `/api/v1/engineer/*`, `/api/v1/admin/*` routes

---

### 8.2 `EnsurePasswordIsSet` (`app/Http/Middleware/EnsurePasswordIsSet.php`)

**Registration alias:** `password.setup`  
**Applied to:** All routes under the `['auth', 'password.setup']` group

**Purpose:** Handles the mandatory post-registration onboarding step for Google OAuth users. When a new user signs in via Google for the first time, they have no password. This middleware intercepts every request from such users and redirects them to `/set-password` until they complete setup.

**Logic:**
1. Checks if the user is authenticated AND `$user->needsPasswordSetup()` returns `true` (i.e., `auth_provider === 'google'` AND `password_set === false`).
2. Exempts the `/set-password`, `/set-password` (POST), and `/logout` routes to avoid infinite redirect loops.
3. Redirects all other requests to `password.setup` with a warning flash message.

---

### 8.3 Standard Laravel Middleware in Use

| Middleware | Purpose |
|------------|---------|
| `auth` | Ensures user is authenticated (redirects to login if not) |
| `verified` | Enforces email verification (used for email/password users) |
| `auth:web` | API routes guard — uses web session (same cookie as browser) |
| `throttle` | Rate limiting on auth routes |
| `encrypt.cookies` | Automatically encrypts all session cookies |
| `validate.csrf` | CSRF token validation on state-changing requests |

---

## 9. Models & Eloquent ORM

### 9.1 `User` Model

**Key traits:** `HasFactory`, `HasRoles` (Spatie), `Notifiable`  
**Interface:** `MustVerifyEmail`

| Feature | Detail |
|---------|--------|
| **Roles** | Three constants: `ROLE_CITIZEN`, `ROLE_ENGINEER`, `ROLE_ADMIN` |
| **Google OAuth** | `AUTH_EMAIL`, `AUTH_GOOGLE` — tracks `auth_provider`, `google_id`, `google_avatar` |
| **Relationships** | `complaints()`, `assignedComplaints()`, `feedbacks()`, `uploadedMedia()`, `statusLogs()` |
| **Accessors** | `getProfilePhotoUrlAttribute()` — resolves S3 URL, local storage, or Google avatar |
| **Scopes** | `scopeActive()`, `scopeVerified()`, `scopeRole()`, `scopeCitizens()`, `scopeEngineers()`, `scopeAdmins()` |
| **Helper methods** | `isCitizen()`, `isEngineer()`, `isAdmin()`, `isAuthority()`, `isGoogleUser()`, `needsPasswordSetup()`, `markPasswordAsSet()` |
| **Notification prefs** | `wantsEmailNotification()`, `wantsSmsNotification()`, `wantsPushNotification()` |
| **Casts** | `password` → `hashed`, `notification_preferences` → `array`, `latitude/longitude` → `decimal:7` |

---

### 9.2 `Complaint` Model

The most complex model in the application. Implements a **state machine** via `VALID_TRANSITIONS`.

| Feature | Detail |
|---------|--------|
| **Status constants** | `PENDING`, `UNDER_REVIEW`, `IN_PROGRESS`, `AWAITING_VERIFICATION`, `VERIFIED`, `REJECTED` |
| **Severity constants** | `LOW`, `MEDIUM`, `HIGH`, `EMERGENCY` |
| **State machine** | `VALID_TRANSITIONS` array; `canTransitionTo()`, `updateStatus()` |
| **Auto-generated ID** | `generateComplaintNumber()` using `MAX()` + `lockForUpdate()` (race-safe) → `RW-2026-00042` |
| **Model events** | `creating` — auto-generates complaint number; `created` — writes initial `status_histories` entry |
| **Relationships** | `user()`, `assignedEngineer()`, `ratedBy()`, `category()`, `duplicateOf()`, `media()`, `images()`, `videos()`, `beforeMedia()`, `afterMedia()`, `statusHistories()`, `feedback()`, `upvotes()` |
| **Accessors** | `getStatusLabelAttribute()`, `getStatusColorAttribute()`, `getSeverityColorAttribute()`, `getReporterNameAttribute()`, `getThumbnailAttribute()` |
| **Scopes** | `scopePending()`, `scopeInProgress()`, `scopeResolved()`, `scopeEmergency()`, `scopeNearby()` (Haversine), `scopeByCategory()`, `scopeAssignedTo()` |
| **Analytics** | `resolutionTime()` — converts seconds to human-readable "2 day(s)" |
| **Engineer rating** | `rateEngineer(int $rating, ?string $comment, User $admin)` |

---

### 9.3 `ComplaintMedia` Model

Manages the files (photos and videos) attached to complaints.

| Feature | Detail |
|---------|--------|
| **Stages** | `before` (citizen evidence), `during`, `after` (engineer evidence) |
| **Types** | `image` / `video` |
| **Cloud storage** | `cloud_disk`, `cloud_path`, `cloud_url`, `cloud_public_id` |
| **Media dimensions** | `width`, `height` (images), `duration` (video), `thumbnail_url` |
| **Moderation** | `is_flagged` flag for content review |

---

### 9.4 `StatusHistory` Model

An append-only audit log. Every status change on a complaint creates a new row — never modified.

| Feature | Detail |
|---------|--------|
| **Immutability** | Only `created_at` — no `updated_at` |
| **Time analytics** | `time_in_previous_status` — seconds spent in old status |
| **Static helpers** | `timelineFor($complaintId)`, `resolutionTimeFor($complaintId)` |

---

### 9.5 `Feedback` Model

One-per-complaint citizen satisfaction survey.

| Feature | Detail |
|---------|--------|
| **Rating** | 1 (Very Poor) to 5 (Excellent) |
| **Anonymous option** | `is_anonymous` hides commenter name |
| **Unique constraint** | One feedback per (complaint, user) pair |

---

### 9.6 `Category` Model

Static taxonomy of road issue types managed by admin.

| Feature | Detail |
|---------|--------|
| **Soft toggle** | `is_active` allows disabling without deletion |
| **Ordering** | `sort_order` for UI display sequence |

---

## 10. Role-Based Access Control

### Roles

| Role | Default for | Capabilities |
|------|-------------|--------------|
| **Citizen** | All new registrations (email or Google) | File complaints, upload before-photos, track status, submit feedback, upvote |
| **Engineer** | Promoted by Admin | View assigned complaints, update status (within state machine), upload after-work media |
| **Admin** | Manual database setup / promoted by Admin API | Full system access: view all complaints, assign engineers, override any status, rate engineer work, manage users |

### Dual RBAC Approach

The application uses a **hybrid** approach for role checking:

1. **Column-based (`users.role`):** The `CheckRole` middleware reads `$user->role` directly from the users table for fast HTTP route guarding. This is used for all web and API route guards.

2. **Spatie Permission tables:** The `spatie/laravel-permission` package tables are installed and the `HasRoles` trait is used on `User`, enabling granular permission checks (`$user->hasRole('engineer')`) within business logic (e.g., in `ComplaintResource` to filter `next_statuses` based on who is viewing).

---

## 11. Authentication System

### 11.1 Email/Password Authentication (Laravel Breeze)

1. **Registration:** `POST /register` — validates name, email, phone, password; creates user with `role = citizen`; sends verification email.
2. **Email Verification:** User clicks link in email → `GET /verify-email/{id}/{hash}` → marks `email_verified_at`.
3. **Login:** `POST /login` → validates credentials → creates session → redirects to role-specific dashboard.
4. **Post-Login Redirect:** Determined by `$user->role` — citizens go to `/citizen/complaints`, engineers to `/engineer/complaints`, admins to `/admin/dashboard`.
5. **Password Reset:** Forgot password → email link → reset form → `POST /reset-password`.

### 11.2 Google OAuth Authentication (Laravel Socialite)

The `GoogleAuthController` handles three distinct cases:

**Case A — Existing Google User:**
- User previously signed in with Google.
- Found by `google_id` match.
- Updates `google_avatar` and `last_login_at`.
- If `password_set === false` → redirect to `/set-password`.
- Otherwise → role-based dashboard redirect.

**Case B — Account Email Merge:**
- User registered with email/password, now trying Google with the same email.
- Found by `email` match.
- Links `google_id` to existing account; marks `auth_provider = google`.
- Continues to role-based dashboard.

**Case C — Brand New User:**
- No existing record matches Google ID or email.
- Creates new `User` with `role = citizen`, `password = null`, `password_set = false`.
- Email is pre-verified by Google (`email_verified_at = now()`).
- Redirected to mandatory `/set-password` onboarding screen.

### 11.3 Mandatory Password Setup (Google Onboarding)

New Google users must set a password before accessing any other route:
- `SetPasswordController::show()` — displays the password setup form.
- `SetPasswordController::store()` — validates, hashes, and saves the password; calls `$user->markPasswordAsSet()`.
- `EnsurePasswordIsSet` middleware enforces this on every protected route until complete.

---

## 12. Complaint Lifecycle & State Machine

### 12.1 States

| Status | Who Sets It | Meaning |
|--------|-------------|---------|
| `pending` | System (on creation) | Filed by citizen, awaiting admin review |
| `under_review` | Engineer | Engineer has acknowledged and is reviewing the complaint |
| `in_progress` | Engineer | Active repair work underway |
| `awaiting_verification` | Engineer | Work complete — uploaded after-media — waiting for admin sign-off |
| `verified` | Admin | Admin reviewed before+after media and approved → fully resolved |
| `rejected` | Admin (any stage) | Complaint rejected (invalid, duplicate, or work unsatisfactory) |

### 12.2 Valid Transitions (State Machine)

```
CITIZEN
    │
    ▼  files complaint
[PENDING] ────────────────────────────────────────────► [REJECTED]
    │
    ▼  engineer takes it
[UNDER_REVIEW] ───────────────────────────────────────► [REJECTED]
    │
    ▼  starts work
[IN_PROGRESS] ────────────────────────────────────────► [REJECTED]
    │
    ▼  uploads after-media, marks done
[AWAITING_VERIFICATION] ──────────────────────────────► [REJECTED]
    │                 │
    ▼ (admin ✅)       └──► [IN_PROGRESS]  (admin ❌ sends back for redo)
[VERIFIED] ← terminal
```

**Implementation:** The `VALID_TRANSITIONS` constant in `Complaint.php`:
```php
const VALID_TRANSITIONS = [
    'pending'               => ['under_review', 'rejected'],
    'under_review'          => ['in_progress', 'rejected'],
    'in_progress'           => ['awaiting_verification', 'rejected'],
    'awaiting_verification' => ['verified', 'in_progress', 'rejected'],
    'verified'              => [],   // terminal
    'rejected'              => [],   // terminal
];
```

**Enforcement:** The `updateStatus()` method is the only way to change a complaint's status. It:
1. Checks `canTransitionTo($newStatus)` — throws `LogicException` if invalid.
2. Opens a database transaction.
3. Updates `status` column.
4. Auto-sets `resolved_at = now()` when transitioning to `awaiting_verification`.
5. Clears `resolved_at` if admin sends back to `in_progress`.
6. Atomically writes a new `status_histories` record.
7. Commits the transaction.

### 12.3 Complaint Number Generation

Every complaint gets a human-readable reference number like `RW-2026-00042`. The generation is race-safe:

```
RW-{YEAR}-{SEQUENCE}
   ↑  year is 4 digits
             ↑ 5-digit zero-padded sequence, reset per year
```

Uses `MAX()` (not `COUNT()`) on the sequence suffix, protected by `lockForUpdate()` inside a wrapping transaction, ensuring no duplicate numbers even under concurrent submissions.

---

## 13. REST API (v1) Reference

**Base URL:** `/api/v1/`  
**Authentication:** Laravel session cookie (`auth:web`) — same session as the web application.  
**Content-Type:** `application/json`

### 13.1 Public Endpoints (no auth required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/categories` | List all active categories |
| `GET` | `/api/v1/complaint-options` | Severity levels, category list for complaint creation form |

### 13.2 Authenticated — All Roles

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/me` | Authenticated user info |
| `POST` | `/api/v1/profile/photo` | Upload profile photo |
| `POST` | `/api/v1/profile/banner` | Upload profile banner |
| `DELETE` | `/api/v1/profile/banner` | Remove profile banner |

### 13.3 Citizen Endpoints (`role:citizen`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/citizen/complaints` | My complaint list |
| `POST` | `/api/v1/citizen/complaints` | File a new complaint |
| `GET` | `/api/v1/citizen/complaints/{id}` | Complaint detail |
| `DELETE` | `/api/v1/citizen/complaints/{id}` | Delete a pending complaint |
| `POST` | `/api/v1/citizen/complaints/{id}/media` | Upload evidence media |
| `DELETE` | `/api/v1/citizen/media/{id}` | Delete a media record |
| `POST` | `/api/v1/citizen/complaints/{id}/feedback` | Submit star rating |

### 13.4 Engineer Endpoints (`role:engineer`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/engineer/complaints` | Assigned complaint list |
| `GET` | `/api/v1/engineer/complaints/{id}` | Complaint detail |
| `PATCH` | `/api/v1/engineer/complaints/{id}/status` | Update status |
| `POST` | `/api/v1/engineer/complaints/{id}/media` | Upload after-work evidence |
| `DELETE` | `/api/v1/engineer/media/{id}` | Delete after-media |

### 13.5 Admin Endpoints (`role:admin`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/admin/complaints` | All complaints (filterable) |
| `GET` | `/api/v1/admin/complaints/{id}` | Full complaint detail |
| `PATCH` | `/api/v1/admin/complaints/{id}/assign` | Assign engineer |
| `PATCH` | `/api/v1/admin/complaints/{id}/status` | Update any status |
| `POST` | `/api/v1/admin/complaints/{id}/rate` | Rate engineer work (1–5) |
| `GET` | `/api/v1/admin/users` | List all users |
| `PATCH` | `/api/v1/admin/users/{id}/role` | Change user role |

---

## 14. Frontend — Views & JavaScript

### 14.1 Blade Template Structure

The frontend is built with **Laravel Blade** templates (server-rendered HTML). Dynamic data is loaded via **Axios** AJAX calls to the API endpoints, updating the DOM without full-page refreshes.

| View Directory | Contents |
|----------------|----------|
| `views/welcome.blade.php` | Public landing page with role-aware navigation (33KB — most complex view) |
| `views/citizen/` | Complaint list, complaint create form (GPS + file upload), complaint detail/tracking |
| `views/engineer/` | Assigned complaint list, complaint detail with status update |
| `views/admin/` | Dashboard with analytics, complaint list, complaint detail with slide-over panel, user management, categories |
| `views/auth/` | Login, register, forgot password, reset password, verify email, set password |
| `views/layouts/` | Base app layout with nav, sidebar components |
| `views/components/` | Reusable Blade components (badges, modals, alert banners) |
| `views/partials/` | Reusable snippets (complaint cards, status timelines) |

### 14.2 JavaScript Libraries

| Library | Version | Use |
|---------|---------|-----|
| **Alpine.js** | 3.x | In-page reactivity — dropdown toggles, modal show/hide, form state management |
| **Axios** | 1.16 | All AJAX calls to `/api/v1/*` — complaint creation, status updates, media uploads |
| **Leaflet.js** | 1.9 | Interactive map on complaint create form for GPS location pinning |
| **Chart.js** | 4.5 | Admin dashboard: bar charts (complaints by category), doughnut (status distribution), line (monthly trends) |

### 14.3 CSS / Styling

- **Tailwind CSS 3.x** — utility-first classes for layout, typography, colors, spacing
- **`@tailwindcss/forms`** — resets form input styles to Tailwind-compatible defaults
- **Custom inline styles** — used for specific animations and glassmorphism effects on the welcome page
- **Vite** — bundles and fingerprints all assets; generates `public/build/manifest.json` referenced by `@vite()` directive

---

## 15. Media Storage Strategy

### Local Development

Files are stored in `storage/app/public/complaints/{complaint_id}/images/` and `.../videos/`. A symlink from `public/storage` is created via `php artisan storage:link`. URLs are served as `http://localhost:8000/storage/...`.

### Production (AWS S3)

| Configuration | Value |
|---------------|-------|
| **Bucket** | `roadwatch-media` |
| **Region** | `ap-south-1` (Mumbai) |
| **Driver** | `s3` via `league/flysystem-aws-s3-v3` |
| **Path style** | Virtual-hosted (not path-style) |
| **Max upload size** | Images: validated in app; Videos: up to 64MB (configured in `uploads.ini` in Docker) |

**Upload limits configured in Dockerfile:**
```ini
upload_max_filesize = 64M
post_max_size = 128M
memory_limit = 256M
max_execution_time = 120
max_input_time = 120
```

**Storage architecture:**
```
AWS S3: roadwatch-media bucket
├── complaints/
│   └── {complaint_id}/
│       ├── images/
│       │   ├── before/  (citizen evidence)
│       │   └── after/   (engineer work proof)
│       └── videos/
│           ├── before/
│           └── after/
└── profiles/
    └── {user_id}/
        ├── photo/
        └── banner/
```

Each media record in the database stores both the `cloud_path` (S3 key for deletions) and `cloud_url` (full CDN URL for display). The PHP GD extension is intentionally **not** installed in Docker — all images go directly to S3 without server-side resizing.

---

## 16. Docker & Containerisation

### Why Docker?

1. **Reproducible Builds:** The same Docker image runs in CI (GitHub Actions), local testing, and Railway production — eliminating "works on my machine" issues.
2. **Consistent PHP Extensions:** The `Dockerfile` explicitly installs required extensions (`pdo_mysql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `zip`, `intl`, `opcache`), ensuring the same set is present everywhere.
3. **Node.js Bundling:** Node.js 22 is installed inside the same image to build frontend assets (`npm run build`) at image build time, so the container ships with compiled CSS/JS — no build step needed at startup.
4. **Railway Compatibility:** Railway's Docker deployment mode expects a `Dockerfile`; `railpack.json` provides metadata for Railway's build system.
5. **Upload Limits:** Docker allows injecting a custom `uploads.ini` to override PHP's default 2MB upload limit — critical for video upload support.

### Dockerfile Walkthrough

```dockerfile
FROM php:8.4-cli           # PHP 8.4 CLI image (no Apache/nginx — uses artisan serve)
WORKDIR /var/www/html

# 1. Install system packages + Node.js 22 (for npm run build)
RUN apt-get install ... nodejs

# 2. Install PHP extensions required by Laravel + MySQL
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip intl opcache

# 3. Increase PHP upload limits for video uploads
RUN echo "upload_max_filesize = 64M\npost_max_size = 128M..." > /usr/local/etc/php/conf.d/uploads.ini

# 4. Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 5. Copy project, install PHP deps (no-dev, optimised)
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader

# 6. Build frontend assets
RUN npm install && npm run build

# 7. Set storage permissions
RUN chmod -R 775 storage bootstrap/cache

# 8. Use startup script
CMD ["/usr/local/bin/start.sh"]
```

### Docker Startup Script (`docker-start.sh`)

At container start, the script:
1. **Validates** required environment variables (`DB_HOST`, `DB_DATABASE`, `DB_USERNAME`) — fails fast if missing.
2. **Caches config** — `php artisan config:cache` (performance).
3. **Caches routes** — `php artisan route:cache` (performance).
4. **Creates storage symlink** — `php artisan storage:link`.
5. **Runs migrations** — `php artisan migrate --force` (auto-upgrades DB schema on deploy).
6. **Seeds categories** — `php artisan db:seed --class=CategorySeeder --force` (idempotent).
7. **Starts server** — `php artisan serve --host=0.0.0.0 --port=$PORT` (Railway injects `PORT`).

---

## 17. CI/CD Pipeline — GitHub Actions

**File:** `.github/workflows/ci.yml`  
**Triggers:** Push or Pull Request to `main` or `develop` branches

The pipeline runs **3 parallel jobs** on every trigger:

### Job 1: 🧪 Run Tests (PHP 8.4)

**Runtime:** Ubuntu Latest  
**Matrix:** PHP 8.4 only (Symfony 8.x requires ≥8.4)

**Steps:**
1. Checkout code (`actions/checkout@v4`)
2. Setup PHP 8.4 with extensions: `mbstring`, `xml`, `bcmath`, `intl`, `pdo_mysql`, `zip`, `exif`, `pcntl` (`shivammathur/setup-php@v2`)
3. **Cache Composer packages** (key: OS + PHP version + `composer.lock` hash)
4. Install Composer dependencies (dev included for tests)
5. Setup Node.js 22 + cache npm
6. Install npm dependencies (`npm ci`) + build assets (`npm run build`)
7. **Configure Laravel environment:**
   - Copies `.env.example`
   - Injects `DB_CONNECTION=mysql`, MySQL credentials (roadwatch/password)
   - Configures `QUEUE_CONNECTION=sync`, `CACHE_STORE=array`, `SESSION_DRIVER=array`
   - Injects placeholder AWS and Google OAuth credentials (tests are mocked)
   - Generates app key
8. **MySQL service container:** MySQL 8.0 with health checks, matching Railway production
9. **Run migrations:** `php artisan migrate --force`
10. **Run tests:** `php artisan test --ansi`

### Job 2: 🎨 Code Style (Laravel Pint)

**Runtime:** Ubuntu Latest

**Steps:**
1. Checkout code
2. Setup PHP 8.4
3. Cache + Install Composer dependencies
4. Run `./vendor/bin/pint --test` — reads `pint.json` config, enforces Laravel/PSR-12 code style. **Fails the job if any file would be modified.**

### Job 3: 🔍 PHP Syntax Check

**Runtime:** Ubuntu Latest

**Steps:**
1. Checkout code
2. Setup PHP 8.4
3. Run `find app config database routes -name "*.php" -exec php -l {} \;` — checks every PHP file for syntax errors. Fails if any file has parse errors.

### Pipeline Summary Table

| Job | Tool | What Fails It |
|-----|------|--------------|
| Run Tests | PHPUnit 12 | Any failing test assertion |
| Code Style | Laravel Pint | Any style violation |
| Syntax Check | `php -l` | Any PHP parse error |

### Running Locally

```bash
# All tests
php artisan test --ansi

# Fix code style
./vendor/bin/pint

# Check style without fixing (mirrors CI)
./vendor/bin/pint --test

# PHP syntax check
find app config database routes -name "*.php" -exec php -l {} \;
```

---

## 18. Deployment on Railway

**Platform:** [Railway](https://railway.app) — a modern PaaS platform that supports Docker-based deployments.  
**Config file:** `railpack.json`

```json
{
  "providers": ["php", "node"],
  "php": { "version": "8.4.21" },
  "node": { "version": "22.22.3" }
}
```

### Deployment Flow

```
Developer pushes to main
         │
         ▼
GitHub Actions CI runs (tests + style + syntax)
         │
         ▼ (all green)
Railway detects new commit → builds Docker image
         │
         ▼
Docker image built:
  ├── PHP dependencies installed (composer install --no-dev)
  └── Frontend assets compiled (npm run build)
         │
         ▼
New container starts (docker-start.sh):
  ├── config:cache + route:cache
  ├── storage:link
  ├── migrate --force  (auto-applies new migrations)
  ├── db:seed CategorySeeder
  └── php artisan serve --host=0.0.0.0 --port=$PORT
         │
         ▼
Railway routes traffic to container
```

### Railway Environment Variables

Set in Railway dashboard (never in code):

| Variable | Example Value | Purpose |
|----------|---------------|---------|
| `APP_KEY` | `base64:...` | Laravel encryption key |
| `APP_ENV` | `production` | Env mode |
| `DB_CONNECTION` | `mysql` | Database driver |
| `DB_HOST` | Railway MySQL internal host | MySQL connection |
| `DB_DATABASE` | `railway` | Database name |
| `DB_USERNAME` | `root` | Database user |
| `DB_PASSWORD` | `<secret>` | Database password |
| `AWS_ACCESS_KEY_ID` | `AKIA...` | S3 credentials |
| `AWS_SECRET_ACCESS_KEY` | `...` | S3 secret |
| `AWS_BUCKET` | `roadwatch-media` | S3 bucket name |
| `GOOGLE_CLIENT_ID` | `924498...` | OAuth client ID |
| `GOOGLE_CLIENT_SECRET` | `GOCSPX-...` | OAuth secret |
| `PORT` | `8080` | Railway injected port |

---

## 19. Testing Strategy

### Test Structure

```
tests/
├── Feature/
│   ├── Auth/
│   │   ├── AuthenticationTest.php
│   │   ├── EmailVerificationTest.php
│   │   ├── PasswordConfirmationTest.php
│   │   ├── PasswordResetTest.php
│   │   ├── PasswordUpdateTest.php
│   │   └── RegistrationTest.php
│   ├── ExampleTest.php
│   └── ProfileTest.php
└── Unit/
    └── (unit tests)
```

### Test Configuration (`phpunit.xml`)

- **Test database:** Uses MySQL in CI (identical to production); can use SQLite locally.
- **In-memory approach:** `CACHE_STORE=array`, `SESSION_DRIVER=array`, `QUEUE_CONNECTION=sync` during tests.

### Test Coverage Areas

| Area | Tests |
|------|-------|
| Authentication (email) | Login, logout, failed login, account lockout |
| Email Verification | Prompt page, resend email, verify link |
| Password Management | Password confirmation, password reset flow, password update |
| User Registration | Successful registration, validation errors, duplicate email |
| Profile | Profile update, profile deletion |

**CI reports:** 25 PHPUnit tests passing (as documented in CI workflow).

### Testing Approach

- **Feature Tests** (HTTP-level): Simulate full HTTP request → response cycles using `$this->actingAs($user)` and `$this->get('/...')` / `$this->post('/...')`. Assert response status codes, session data, and database changes.
- **Factories:** `UserFactory` uses FakerPHP to generate realistic test user data.

---

## 20. Security Considerations

| Security Area | Implementation |
|---------------|---------------|
| **CSRF Protection** | All state-changing web routes protected by `ValidateCsrfToken` middleware |
| **SQL Injection** | Eloquent ORM and Query Builder use PDO prepared statements throughout |
| **XSS Prevention** | Blade templates auto-escape output with `{{ }}` |
| **Password Hashing** | Bcrypt with 12 rounds (`BCRYPT_ROUNDS=12` in `.env`) |
| **Role Isolation** | `CheckRole` middleware on every role-specific route (web + API) |
| **Mass Assignment** | All models have explicit `$fillable` arrays — no `$guarded = []` |
| **Hidden Fields** | `password`, `remember_token` in `$hidden` — never serialized to JSON |
| **Rate Limiting** | Laravel's built-in `throttle:6,1` on auth routes |
| **Email Verification** | Required for email/password registrations before accessing the app |
| **Secrets in ENV** | All API keys, DB credentials, and app secret in `.env` / Railway variables, never in code |
| **S3 Signed URLs** | Option to serve private media via pre-signed S3 URLs |
| **Session Encryption** | All session cookies encrypted via `EncryptCookies` middleware |
| **Google OAuth Validation** | State parameter and PKCE handled by Socialite |
| **Duplicate Complaint Number** | `lockForUpdate()` prevents race conditions in concurrent submissions |

---

## 21. API Resource Transformation

`ComplaintResource` (`app/Http/Resources/ComplaintResource.php`) is a single **unified JSON transformer** used by all roles (citizen, engineer, admin). It transforms an Eloquent `Complaint` model into a consistent API response shape.

**Key features:**
- **Role-aware `next_statuses`:** If an engineer views a complaint in `awaiting_verification` status, `next_statuses` returns `[]` — the engineer has no actions available; they wait for the admin to approve.
- **Conditional loading (`whenLoaded`):** Related data (category, media, status_histories, feedback, assignedEngineer) is only included in the response if the relationship was eager-loaded by the controller. This prevents N+1 queries.
- **Feedback formatting:** Transforms raw rating integer into label (`"Excellent"`), emoji (`"😍"`), and star string (`"★★★★★"`).
- **Engineer rating section:** Includes admin's score, label, emoji, and comment when available.
- **`ratings_locked`:** `true` only when both the admin has rated the engineer AND the citizen has submitted feedback — signals the UI to disable further rating input.
- **Anonymous masking:** `submitted_by` is omitted if `is_anonymous = true`.

---

## 22. Environment Configuration

The application is configured via `.env` (local development) and Railway environment variables (production).

### Key Configuration Groups

| Group | Key Variables | Purpose |
|-------|---------------|---------|
| **Application** | `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL` | Core Laravel settings |
| **Database** | `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | DB connection |
| **AWS S3** | `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET` | Media storage |
| **Google OAuth** | `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` | Social login |
| **Session/Cache** | `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | Storage drivers |
| **Mail** | `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, etc. | Email notifications |
| **Logging** | `LOG_CHANNEL`, `LOG_LEVEL` | Log configuration |
| **PHP** | `BCRYPT_ROUNDS` | Security |

### Local vs Production Differences

| Setting | Local | Production (Railway) |
|---------|-------|---------------------|
| `DB_CONNECTION` | `sqlite` | `mysql` |
| `FILESYSTEM_DISK` | `local` | `s3` |
| `QUEUE_CONNECTION` | `sync` | `sync` (expandable to Redis) |
| `CACHE_STORE` | `file` | `file` (expandable to Redis) |
| `APP_DEBUG` | `true` | `false` |
| `APP_ENV` | `local` | `production` |

---

## 23. Workflow Diagrams

### 23.1 Citizen Complaint Filing Workflow

```
Citizen Opens /citizen/complaints
          │
          ▼
Clicks "Report New Issue"
          │
          ▼
/citizen/complaints/create
  ├── Fills in: Title, Description
  ├── Selects: Category (fetched from /api/v1/categories)
  ├── Pins GPS location on Leaflet.js map
  ├── Selects: Severity (Low/Medium/High/Emergency)
  ├── Toggles: Anonymous submission
  └── Uploads: Before photos/videos
          │
          ▼
POST /api/v1/citizen/complaints
  └── Validates → Creates Complaint → Auto-generates RW-YYYY-NNNNN
          │
          ▼
POST /api/v1/citizen/complaints/{id}/media
  └── Uploads each file to S3 → Records in complaint_media
          │
          ▼
Redirected to /citizen/complaints/{id}
  └── Shows: Status badge, timeline, media gallery, map pin
```

### 23.2 Admin Assignment Workflow

```
Admin Opens /admin/complaints
  └── Sees: All complaints table (filter by status/severity/category)
          │
          ▼
Clicks complaint → Slide-over panel opens
  └── Shows: Before photos, complaint details, map
          │
          ▼
Selects engineer from dropdown
          │
          ▼
PATCH /api/v1/admin/complaints/{id}/assign
  └── Sets assigned_to = engineer_id
          │
          ▼
Engineer sees complaint in /engineer/complaints
```

### 23.3 Engineer Resolution Workflow

```
Engineer Opens /engineer/complaints
  └── Sees: List of complaints assigned to them
          │
          ▼
Clicks complaint → Views details
  └── Status: pending → clicks "Start Review"
          │
          ▼
PATCH /api/v1/engineer/complaints/{id}/status
  └── { new_status: "under_review" }
          │
          ▼
Goes to site → fixes the road issue → returns
  └── Status: under_review → clicks "Start Work"
  └── { new_status: "in_progress" }
          │
          ▼
Uploads after-work photos/videos
  └── POST /api/v1/engineer/complaints/{id}/media (stage: after)
          │
          ▼
Clicks "Mark as Done"
  └── { new_status: "awaiting_verification" }
  └── resolved_at = now() is auto-set
          │
          ▼
Complaint waits for Admin verification
```

### 23.4 Admin Verification & Feedback Workflow

```
Admin Reviews complaint in awaiting_verification
  └── Sees: Before photos (left) vs After photos (right) side-by-side
          │
    ┌─────┴──────┐
    ▼            ▼
 ✅ Satisfied  ❌ Not satisfied
    │            │
    ▼            ▼
PATCH /status  PATCH /status
{ verified }   { in_progress }
    │          (with remarks for engineer)
    ▼
Rating screen: Admin rates engineer work 1–5 stars
POST /api/v1/admin/complaints/{id}/rate
    │
    ▼
Citizen gets notification → opens complaint
    │
    ▼
Citizen submits feedback (1–5 stars + comment)
POST /api/v1/citizen/complaints/{id}/feedback
    │
    ▼
ratings_locked = true → Complaint fully closed
```

---

## 24. Challenges & Design Decisions

### Challenge 1: Race-Safe Complaint Number Generation

**Problem:** Multiple citizens submitting complaints simultaneously could cause duplicate complaint numbers if using `COUNT(*)` or incrementing a counter.

**Solution:** Using `MAX()` on the numeric suffix of `complaint_number` combined with `lockForUpdate()` inside a database transaction. Even if a concurrent request has begun but not committed, `lockForUpdate()` serialises access to the row set, ensuring sequential number generation.

---

### Challenge 2: Google OAuth Users Without Passwords

**Problem:** New Google OAuth users have no password. Allowing them unrestricted access without a password creates a security gap if their Google account is later compromised or if they need to use email login.

**Solution:** A two-pronged approach:
1. `password_set = false` flag on the user record.
2. `EnsurePasswordIsSet` middleware intercepts every request and redirects to `/set-password` until setup is complete.
3. `SetPasswordController` handles the mandatory onboarding step.

---

### Challenge 3: Session-Based API vs Token Auth

**Problem:** Classic REST API design would use token authentication (Bearer tokens). However, this app is a monolith where the web and API are co-located.

**Decision:** Use `auth:web` session-based auth for the API. Advantages: no token management overhead, same CSRF protection, simpler client-side code (cookies sent automatically). Trade-off: not suitable for mobile apps or third-party API consumers — acceptable for this use case.

---

### Challenge 4: Unified API Response for Multiple Roles

**Problem:** Admin, engineer, and citizen all call the same complaint endpoints but need slightly different data shapes (e.g., engineers shouldn't see `next_statuses` options that only admins can trigger).

**Solution:** `ComplaintResource` is role-aware — it checks `$request->user()->hasRole('engineer')` to filter `next_statuses`, keeping the JSON transformer unified while adapting to the caller's role.

---

### Challenge 5: CI Database Matching Production

**Problem:** Using SQLite in CI (common shortcut) hides MySQL-specific bugs — e.g., enum types, strict mode, full-text index differences.

**Decision:** The CI workflow spins up a real **MySQL 8.0 service container** matching the Railway production database exactly. This eliminates false positives from SQLite test passes that fail in production.

---

### Challenge 6: Docker Upload Limits

**Problem:** Laravel and PHP default upload limits (2MB) are too small for road repair videos (which can easily be 10–50MB).

**Solution:** A custom `uploads.ini` is written during the Docker build, overriding PHP defaults to allow 64MB file uploads, 128MB POST bodies, and 256MB memory limit with 120s execution time.

---

## 25. Future Enhancements & Conclusion

### Planned Enhancements

| Enhancement | Priority | Description |
|-------------|----------|-------------|
| **Real-time notifications** | High | WebSockets (Laravel Reverb) for instant status update alerts |
| **Mobile App (Flutter)** | High | Native iOS/Android app using the existing REST API v1 |
| **SMS notifications** | Medium | Twilio integration for citizens without smartphones |
| **Duplicate detection** | Medium | AI-powered geographic clustering to auto-flag nearby complaints as duplicates |
| **Report analytics export** | Medium | PDF/CSV export of complaint statistics for municipal reports |
| **Redis queue** | Medium | Move from `sync` to Redis queue for email notifications (non-blocking) |
| **Complaint comments** | Medium | Internal thread between admin and engineer (scaffold exists) |
| **AI severity suggestion** | Low | Auto-suggest severity from complaint description and photo content |
| **GIS heat map** | Low | Admin heat map overlaying complaint density on city map |
| **SLA tracking** | Low | Alert admins when complaints exceed defined resolution time limits |

### Conclusion

**RoadWatch** demonstrates a production-ready, full-stack Laravel application built with modern engineering practices:

- **Clean Architecture:** MVC + API layer, state machine in the model, role-aware resources
- **Security-First:** CSRF, bcrypt, role guards, mass-assignment protection, Google OAuth onboarding
- **DevOps Maturity:** Docker containerisation, fully automated CI/CD with three quality gates, environment parity between CI and production
- **Scalable Design:** Versioned API (`/v1/`), cloud storage (S3), queue-ready, Redis-ready
- **Comprehensive Database Design:** 14 tables with proper indexes, foreign keys, cascade rules, and an immutable audit trail
- **User Experience:** Role-based dashboards, real-time map integration, media evidence workflow, star rating system

The platform successfully digitises the municipal road complaint process, bringing transparency and accountability to infrastructure management while providing a robust technical foundation for future mobile and AI-enhanced capabilities.

---

*Report generated for RoadWatch v1.0 — May 2026*  
*Author: Gourav Dash | GitHub: GariFFon | Repository: github.com/GariFFon/RoadWatch*
