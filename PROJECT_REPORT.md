# 🛣️ RoadWatch — Detailed Project Report

> **Author:** Gourav Dash ([GitHub: GariFFon](https://github.com/GariFFon))
> **Framework:** Laravel 13 (PHP 8.3+)
> **Generated:** May 2026

---

## 📖 1. What Is This Project?

**RoadWatch** is a full-stack web application for **citizen-driven road issue reporting and resolution**. It allows ordinary citizens to report road problems (potholes, waterlogging, broken streetlights, cracked roads, etc.) directly to municipal authorities.

The system provides **three distinct role-based portals**:

| Role | Portal URL | Purpose |
|------|-----------|---------|
| **Citizen** | `/citizen/complaints` | File complaints, upload photos/videos, track status, submit feedback |
| **Engineer** | `/engineer/complaints` | View assigned complaints, upload work evidence, update status |
| **Admin** | `/admin/dashboard` | Full oversight — assign engineers, review evidence, approve/reject work |

After login, users are **automatically redirected** to their role's portal. The entire frontend is powered by a **RESTful JSON API (v1)** with session-based authentication.

---

## 🏗️ 2. Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend Framework | **Laravel 13** (PHP 8.3+) |
| Authentication | **Laravel Breeze** + **Laravel Socialite** (Google OAuth) |
| Role Management | **Spatie Laravel Permission** (v7.4) + custom `CheckRole` middleware |
| Database | **SQLite** (development) / MySQL or PostgreSQL (production) |
| File Storage | **Laravel Storage** →  **AWS S3** (production) |
| Frontend | **Blade templates** + **Vanilla JavaScript** + **Axios** |
| Styling | **Tailwind CSS** + custom inline styles |
| API | **RESTful JSON API v1** — session-cookie authenticated |
| Dev Tools | Vite (asset bundling), Laravel Pint (code style), PHPUnit (testing) |

---

## 📁 3. Project Structure

```
RoadWatch/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/
│   │   │   │   ├── Admin/              # Admin REST API controllers
│   │   │   │   │   ├── ComplaintController.php
│   │   │   │   │   └── UserController.php
│   │   │   │   ├── Engineer/           # Engineer REST API controllers
│   │   │   │   │   ├── ComplaintController.php
│   │   │   │   │   └── MediaController.php
│   │   │   │   └── Citizen/            # Citizen REST API controllers
│   │   │   │       ├── ComplaintController.php
│   │   │   │       ├── FeedbackController.php
│   │   │   │       └── MediaController.php
│   │   │   ├── Auth/                   # Authentication controllers
│   │   │   │   ├── GoogleAuthController.php
│   │   │   │   ├── RegisteredUserController.php
│   │   │   │   ├── AuthenticatedSessionController.php
│   │   │   │   ├── SetPasswordController.php
│   │   │   │   └── ... (Breeze default controllers)
│   │   │   └── Citizen/               # Blade-facing web controllers
│   │   │       ├── ComplaintController.php
│   │   │       └── FeedbackController.php
│   │   ├── Middleware/
│   │   │   ├── CheckRole.php           # Role guard (citizen|engineer|admin)
│   │   │   └── EnsurePasswordIsSet.php # Forces Google users to set a password
│   │   └── Resources/
│   │       └── ComplaintResource.php   # Unified API JSON shape for all roles
│   ├── Models/
│   │   ├── User.php
│   │   ├── Complaint.php
│   │   ├── ComplaintMedia.php
│   │   ├── StatusHistory.php
│   │   ├── Feedback.php
│   │   └── Category.php
│   └── View/
├── database/
│   ├── migrations/                     # 15 migration files
│   ├── seeders/
│   │   ├── DatabaseSeeder.php
│   │   └── CategorySeeder.php
│   └── database.sqlite
├── resources/
│   └── views/
│       ├── welcome.blade.php           # Public landing page
│       ├── admin/                      # Admin dashboard, complaints, users
│       ├── engineer/                   # Engineer assignment list + detail
│       ├── citizen/                    # Citizen complaint list, create, show
│       ├── auth/                       # Login, register, etc.
│       └── components/                 # Reusable Blade components
├── routes/
│   ├── web.php                         # Blade view routes
│   ├── api.php                         # REST API v1 routes
│   └── auth.php                        # Breeze auth routes
└── docs/
    └── API_REFERENCE.md                # Full API documentation
```

---

## 🗃️ 4. Database Schema

### Table Overview

| Table | Description |
|-------|-------------|
| `users` | All users — Citizens, Engineers, Admins with Google OAuth support |
| `categories` | Road issue types (Pothole, Waterlogging, etc.) |
| `complaints` | Core complaint records — the heart of the system |
| `complaint_media` | Before/after photos and videos per complaint |
| `status_histories` | Immutable audit log of every status change |
| `feedbacks` | Citizen star ratings (1–5) after complaint resolution |
| `permissions` / `roles` (Spatie) | RBAC permission tables |

### Migration Timeline

| Migration | Purpose |
|-----------|---------|
| `0001_01_01_000000_create_users_table` | Core user table |
| `0001_01_01_000001_create_cache_table` | Laravel cache |
| `0001_01_01_000002_create_jobs_table` | Laravel queue jobs |
| `2026_05_13_*_create_permission_tables` | Spatie RBAC tables |
| `2026_05_14_000001_create_categories_table` | Issue categories |
| `2026_05_14_000002_create_complaints_table` | Core complaints |
| `2026_05_14_000003_create_complaint_media_table` | Media attachments |
| `2026_05_14_000004_create_status_histories_table` | Audit log |
| `2026_05_14_000005_create_feedbacks_table` | Citizen feedback |
| `2026_05_14_000006_create_supporting_tables` | IssueUpvotes, etc. |
| `2026_05_14_120000_*_nullable_old_status` | Schema fix |
| `2026_05_14_130000_expand_complaint_status_enum` | New statuses |
| `2026_05_14_140000_add_engineer_rating` | Admin engineer rating |
| `2026_05_14_143246_create_complaint_comments_table` | Comments |
| `2026_05_15_*_add_profile_banner_to_users` | Profile banner URL |

---

## 🧩 5. Models — Detailed Breakdown

### 5.1 `User` Model
**File:** `app/Models/User.php`

The central identity model. Every person in the system is a `User`.

**Key Fields:**

| Field | Type | Description |
|-------|------|-------------|
| `name` | string | Display name |
| `email` | string | Unique email address |
| `phone` | string | Optional phone number |
| `role` | enum | `citizen` / `engineer` / `admin` |
| `gender` | enum | `male` / `female` / `other` / `prefer_not_to_say` |
| `profile_photo` | string | S3 URL or local path |
| `profile_banner_url` | string | Banner image URL |
| `address`, `city`, `state` | string | Location details |
| `latitude`, `longitude` | decimal(7) | GPS coordinates |
| `notification_preferences` | JSON | `{"email":true,"sms":false,"push":true}` |
| `is_active` | boolean | Account enabled/disabled |
| `is_verified` | boolean | Admin-verified status |
| `last_login_at` | datetime | Last sign-in timestamp |
| `google_id` | string | Google OAuth ID |
| `auth_provider` | enum | `email` / `google` |
| `google_avatar` | string | Google profile picture URL |
| `password_set` | boolean | Whether Google user has set a password |

**Role Constants:**
- `ROLE_CITIZEN` = `'citizen'`
- `ROLE_ENGINEER` = `'engineer'`
- `ROLE_ADMIN` = `'admin'`

**Key Methods:**
- `isCitizen()`, `isEngineer()`, `isAdmin()`, `isAuthority()` — role checks
- `isGoogleUser()` — checks if signed in via Google OAuth
- `needsPasswordSetup()` — `true` for new Google users who haven't set a password
- `markPasswordAsSet()` — marks password setup complete
- `wantsEmailNotification()`, `wantsSmsNotification()`, `wantsPushNotification()` — notification prefs
- `getProfilePhotoUrlAttribute()` — returns S3/local/Google avatar URL

**Relationships:**
- `complaints()` — HasMany → complaints filed by this citizen
- `assignedComplaints()` — HasMany → complaints assigned to this engineer
- `feedbacks()` — HasMany → feedback given by this user

---

### 5.2 `Complaint` Model
**File:** `app/Models/Complaint.php`

The **core model** of the entire system. Every road issue is a `Complaint`. It contains a **state machine** with strictly defined transitions.

**Key Fields:**

| Field | Type | Description |
|-------|------|-------------|
| `complaint_number` | string | Auto-generated unique ID (e.g., `RW-2026-00003`) |
| `user_id` | FK → users | Citizen who filed the complaint |
| `category_id` | FK → categories | Type of road issue |
| `assigned_to` | FK → users | Engineer assigned to fix it |
| `title` | string | Short summary |
| `description` | string (2000 max) | Detailed description |
| `latitude`, `longitude` | decimal(7) | GPS location of the issue |
| `location` | string | Human-readable address |
| `severity` | enum | `low` / `medium` / `high` / `emergency` |
| `status` | enum | See status table below |
| `votes_count` | integer | Community upvotes |
| `views_count` | integer | How many times it was viewed |
| `is_duplicate` | boolean | Flagged as a duplicate report |
| `duplicate_of` | FK → complaints | Links to original if duplicate |
| `rejection_reason` | string | Admin's reason for rejection |
| `estimated_completion` | date | Expected fix date |
| `resolved_at` | datetime | When engineer marked work done |
| `is_anonymous` | boolean | Citizen's identity is hidden |
| `engineer_rating` | integer (1–5) | Admin's rating of engineer work |
| `engineer_rating_comment` | string | Admin's comment on rating |
| `rated_by` | FK → users | Admin who rated |
| `rated_at` | datetime | When rating was given |

**Severity Levels:**
| Level | Constant | Color |
|-------|----------|-------|
| Low | `SEVERITY_LOW` | 🟢 Green |
| Medium | `SEVERITY_MEDIUM` | 🟡 Yellow |
| High | `SEVERITY_HIGH` | 🟠 Orange |
| Emergency | `SEVERITY_EMERGENCY` | 🔴 Red |

**Status State Machine:**

| Status | Constant | Color | Description |
|--------|----------|-------|-------------|
| Pending | `STATUS_PENDING` | 🟡 Yellow | Just filed, awaiting admin review |
| Under Review | `STATUS_UNDER_REVIEW` | 🔵 Blue | Admin has reviewed, engineer assigned |
| In Progress | `STATUS_IN_PROGRESS` | 🟣 Indigo | Engineer actively working |
| Awaiting Verification | `STATUS_AWAITING_VERIFICATION` | 🟠 Orange | Engineer done, admin must approve |
| Verified | `STATUS_VERIFIED` | 🟢 Green | Admin approved — fully resolved ✓ |
| Rejected | `STATUS_REJECTED` | 🔴 Red | Admin rejected the complaint entirely |

**Valid Status Transitions:**

```
pending          → [under_review, rejected]
under_review     → [in_progress, rejected]
in_progress      → [awaiting_verification, rejected]
awaiting_verif.  → [verified, in_progress, rejected]
verified         → []   (terminal — no further changes)
rejected         → []   (terminal — no further changes)
```

**Auto-generated Complaint Number:**
The `complaint_number` is auto-generated on create using a `MAX()` + year-based sequential ID format: `RW-{YEAR}-{NNNNN}` (e.g., `RW-2026-00001`). It uses `lockForUpdate()` to prevent race conditions under concurrent requests.

**Key Methods:**
- `updateStatus($newStatus, $user, $remarks)` — guarded transition with automatic audit log (wrapped in a DB transaction)
- `canTransitionTo($status)` — checks if a transition is valid
- `rateEngineer($rating, $comment, $admin)` — saves admin's 1–5 star rating of the engineer
- `timeline()` — returns full chronological status history
- `resolutionTime()` — human-readable total resolution time
- `incrementViews()` — efficiently increments view count
- `scopeNearby($lat, $lng, $radius)` — Haversine formula to find nearby complaints

**Auto-Actions on Model Events:**
- `creating`: Auto-generates `complaint_number`
- `created`: Automatically writes the first `StatusHistory` entry (status = `pending`)

---

### 5.3 `ComplaintMedia` Model
**File:** `app/Models/ComplaintMedia.php`

Manages all **photo and video evidence** attached to a complaint. Designed to work with both local storage and cloud (AWS S3 / Cloudinary).

**Key Fields:**

| Field | Description |
|-------|-------------|
| `complaint_id` | FK → complaints |
| `uploaded_by` | FK → users (citizen or engineer) |
| `file_type` | `image` or `video` |
| `stage` | `before` (citizen) / `during` (engineer mid-fix) / `after` (engineer done) |
| `original_name` | Original filename from device |
| `cloud_disk` | `'s3'` / `'cloudinary'` / `'public'` |
| `cloud_path` | Relative storage key (e.g., `complaints/5/images/abc123.jpg`) |
| `cloud_url` | Full public CDN URL |
| `cloud_public_id` | Cloudinary public ID (for deletion/transforms) |
| `mime_type` | e.g., `image/jpeg` |
| `size_bytes` | File size |
| `width`, `height` | Image/video dimensions in px |
| `duration` | Video duration in seconds |
| `thumbnail_url` | Auto-generated video thumbnail |
| `is_flagged` | Admin can flag inappropriate content |
| `sort_order` | Ordering of multiple images |

**File Limits:**
- Images: Max 10 MB, types: JPEG, PNG, WebP, HEIC
- Videos: Max 50 MB, types: MP4, QuickTime, WebM

**Key Methods:**
- `deleteFromCloud()` — deletes from S3 or Cloudinary
- `getUrlAttribute()` — returns CDN URL (falls back to local)
- `getThumbnailUrl($w, $h)` — Cloudinary transform URL for thumbnails
- `getFileSizeAttribute()` — human-readable file size (B/KB/MB)

---

### 5.4 `StatusHistory` Model
**File:** `app/Models/StatusHistory.php`

An **immutable audit log** — every time a complaint's status changes, one entry is written here. It is never updated, only created.

**Key Fields:**

| Field | Description |
|-------|-------------|
| `complaint_id` | FK → complaints |
| `changed_by` | FK → users (who made the change) |
| `old_status` | Previous status (nullable for initial entry) |
| `new_status` | The new status |
| `remarks` | Optional comment explaining the change |
| `time_in_previous_status` | Seconds spent in the old status (auto-calculated) |

**Auto-calculation on `creating`:**
When a new `StatusHistory` entry is created, the model automatically calculates how many seconds the complaint spent in the previous status by comparing timestamps. It uses `abs()` to prevent negative values from clock drift.

**Key Static Methods:**
- `timelineFor($complaintId)` — full chronological timeline with user info
- `resolutionTimeFor($complaintId)` — total time from `pending` → `verified` in seconds

**Accessors:**
- `getOldStatusLabelAttribute()` — "Pending", "In Progress", etc.
- `getNewStatusLabelAttribute()` — human-readable new status
- `getTransitionLabelAttribute()` — "Pending → Under Review" arrow string
- `getTimeInPreviousStatusHumanAttribute()` — "2 day(s)", "3 hour(s)", "45 minute(s)"
- `getNewStatusColorAttribute()` — Tailwind color name for the badge

---

### 5.5 `Feedback` Model
**File:** `app/Models/Feedback.php`

Stores **citizen ratings** for resolved complaints. Citizens can rate the resolution quality on a 1–5 star scale.

**Key Fields:**

| Field | Description |
|-------|-------------|
| `complaint_id` | FK → complaints |
| `user_id` | FK → users (the citizen) |
| `rating` | Integer 1–5 |
| `comment` | Optional written feedback |
| `is_anonymous` | Citizen can hide their identity |

**Rating Labels:**
| Rating | Label | Emoji |
|--------|-------|-------|
| 1 | Very Poor | 😡 |
| 2 | Poor | 😞 |
| 3 | Average | 😐 |
| 4 | Good | 😊 |
| 5 | Excellent | 😍 |

**Guard:** Feedback can **only** be submitted on `verified` complaints. A `creating` event throws a `LogicException` if the complaint is not in a terminal resolved state.

**Key Methods:**
- `getRatingLabelAttribute()` — "Excellent", "Good", etc.
- `getRatingEmojiAttribute()` — returns the emoji for the rating
- `getStarsAttribute()` — "★★★★☆" star string
- `averageForComplaint($id)` — static, returns avg rating for a complaint
- `distributionForComplaint($id)` — static, returns `[1=>2, 2=>1, 3=>5, ...]`

---

### 5.6 `Category` Model
**File:** `app/Models/Category.php`

Defines the **types of road issues** that citizens can report.

**Default Categories (seeded):**

| # | Category | Icon | Color |
|---|----------|------|-------|
| 1 | Pothole | 🕳️ | Red |
| 2 | Waterlogging | 🌊 | Blue |
| 3 | Broken Divider | 🚧 | Orange |
| 4 | Cracked Road | ⚠️ | Yellow |
| 5 | Drainage Issue | 🚿 | Teal |
| 6 | Street Light | 💡 | Purple |
| 7 | Road Collapse | 🏚️ | Rose |
| 8 | Other | 📌 | Gray |

**Key Fields:** `name`, `slug`, `icon`, `color`, `description`, `is_active`, `sort_order`

**Accessors:**
- `getComplaintsCountAttribute()` — total complaints in this category
- `getResolutionRateAttribute()` — e.g., `"72%"` (verified / total)

---

## 🔄 6. Complete Workflows

### 6.1 Citizen Complaint Workflow

```
1. Citizen registers/logs in
2. Navigates to /citizen/complaints/create
3. Fills form:
   - Title, Description (20–2000 chars)
   - Category (dropdown from /api/v1/categories)
   - Severity (low/medium/high/emergency)
   - GPS location (latitude, longitude, readable address)
   - Optionally checks "submit anonymously"
   - Uploads 1–5 "before" images (max 10MB each)
   - Optionally uploads up to 2 videos (max 50MB each)
4. On submit → POST /api/v1/citizen/complaints
   - Complaint created with status = "pending"
   - complaint_number auto-generated (e.g., RW-2026-00042)
   - Files uploaded to storage/public/complaints/{id}/images/
   - First StatusHistory entry auto-created
5. Citizen can view their complaints at /citizen/complaints
6. Citizen can delete a complaint ONLY if status is still "pending"
7. After complaint is "verified" → citizen can submit 1–5 star feedback
```

---

### 6.2 Admin Workflow

```
1. Admin logs in → redirected to /admin/dashboard
2. Views all complaints at /admin/complaints (filterable by status/severity)
3. Opens a complaint → slide-over detail panel shows:
   - Before photos/videos from citizen
   - Status history timeline
   - Citizen and engineer details
4. Assigns an engineer → PATCH /api/v1/admin/complaints/{id}/assign
   - Validates the assigned user has role = "engineer"
5. Moves complaint to "under_review" → PATCH /api/v1/admin/complaints/{id}/status
6. After engineer uploads after-work evidence and marks "awaiting_verification":
   a. Admin reviews before + after media side by side
   b. Satisfied? → sets status to "verified" (terminal — complaint closed)
   c. Not satisfied? → sends back to "in_progress" with MANDATORY remarks
      (Engineer must redo the work)
7. Admin can also outright reject any complaint (status → "rejected")
8. After verification, admin can rate engineer's work:
   POST /api/v1/admin/complaints/{id}/rate
   - Rating: 1–5 stars
   - Optional comment
   - Sealed once citizen also submits feedback
9. Admin manages users at /admin/users:
   - View all users
   - Change user roles → PATCH /api/v1/admin/users/{id}/role
```

---

### 6.3 Engineer Workflow

```
1. Engineer logs in → redirected to /engineer/complaints
2. Views assigned complaints (active tab = non-terminal statuses)
3. Opens a complaint → sees citizen's before photos
4. Updates status to "in_progress" → PATCH /api/v1/engineer/complaints/{id}/status
5. Works on the physical road issue
6. Uploads "after" evidence (photos/videos) as proof:
   POST /api/v1/engineer/complaints/{id}/media
   - stage = "after"
7. Marks complaint as "awaiting_verification"
   - resolved_at timestamp is auto-set
   - Engineer can no longer take actions — blocked until admin responds
8. Admin either:
   a. Verifies → complaint is closed. Engineer sees it on "Completed" tab.
   b. Sends back → resolved_at is cleared, engineer must redo and re-upload
```

---

### 6.4 Google OAuth Workflow

```
1. User clicks "Continue with Google" → GET /auth/google
   → Redirected to Google consent screen (scopes: openid, profile, email)
2. Google sends code back → GET /auth/google/callback

   Case A: Existing google_id found → update avatar, log in
   Case B: Email exists (merge) → link google_id, log in
   Case C: New user → create account with:
             role = citizen, password = null, password_set = false

3. If password_set = false → redirect to /set-password (mandatory)
   - EnsurePasswordIsSet middleware enforces this on all protected routes
4. User sets a password → password_set = true
5. Future logins proceed normally
```

---

## 🌐 7. API Reference Summary

**Base URL:** `/api/v1`  
**Authentication:** Laravel session cookie (`auth:web`) — same session as web routes. No tokens needed.

### Public Endpoints (No Auth)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/categories` | List all active complaint categories |
| GET | `/api/v1/complaint-options` | Get severity levels, statuses, etc. |

### Authenticated Endpoints (All Roles)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/me` | Get current authenticated user's profile |
| POST | `/api/v1/profile/photo` | Upload profile photo |
| POST | `/api/v1/profile/banner` | Upload profile banner image |
| DELETE | `/api/v1/profile/banner` | Remove profile banner |

### Citizen API (`role:citizen`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/citizen/complaints` | List own complaints (paginated) |
| POST | `/api/v1/citizen/complaints` | Create a new complaint with media |
| GET | `/api/v1/citizen/complaints/{id}` | Show a single complaint (own only) |
| DELETE | `/api/v1/citizen/complaints/{id}` | Delete (only if status = pending) |
| POST | `/api/v1/citizen/complaints/{id}/media` | Upload additional media |
| DELETE | `/api/v1/citizen/media/{id}` | Delete an uploaded media file |
| POST | `/api/v1/citizen/complaints/{id}/feedback` | Submit 1–5 star rating (verified only) |

### Engineer API (`role:engineer`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/engineer/complaints` | List assigned complaints (filter by status/completed) |
| GET | `/api/v1/engineer/complaints/{id}` | View complaint detail (assigned to me only) |
| PATCH | `/api/v1/engineer/complaints/{id}/status` | Update status (guarded transitions) |
| POST | `/api/v1/engineer/complaints/{id}/media` | Upload "after" work evidence |
| DELETE | `/api/v1/engineer/media/{id}` | Delete uploaded media |

### Admin API (`role:admin`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/complaints` | List all complaints (filter by status/severity) |
| GET | `/api/v1/admin/complaints/{id}` | Full complaint detail with all relations |
| PATCH | `/api/v1/admin/complaints/{id}/assign` | Assign an engineer |
| PATCH | `/api/v1/admin/complaints/{id}/status` | Update status (admin has full control) |
| POST | `/api/v1/admin/complaints/{id}/rate` | Rate engineer's work (1–5 stars) |
| GET | `/api/v1/admin/users` | List all users |
| PATCH | `/api/v1/admin/users/{id}/role` | Change a user's role |

---

## 🔐 8. Authentication & Authorization

### Authentication Methods
1. **Email + Password** — standard Laravel Breeze authentication with email verification
2. **Google OAuth** — via Laravel Socialite; handles three cases: returning Google user, email merge, and new account creation

### Authorization — Middleware Stack

| Middleware | File | Purpose |
|-----------|------|---------|
| `auth` | Laravel built-in | Requires login |
| `password.setup` | `EnsurePasswordIsSet` | Redirects Google users to /set-password if `password_set = false` |
| `role:citizen` | `CheckRole` | Allows only users with `role = 'citizen'` |
| `role:engineer` | `CheckRole` | Allows only engineers |
| `role:admin` | `CheckRole` | Allows only admins |

`CheckRole` reads the `role` column directly from the `users` table (not Spatie DB roles). It supports comma-separated multi-role checks: `role:admin,engineer`.

### Additional Security in Controllers
- Citizen complaint endpoints verify `complaint->user_id === auth()->id()` (ownership check)
- Engineer endpoints verify `complaint->assigned_to === auth()->id()` (assignment check)
- Engineers **cannot** set status to `verified` or `rejected` — admin-only actions
- Engineers are blocked from taking any action while a complaint is `awaiting_verification`
- Admin ratings are sealed once both admin and citizen have submitted ratings

---

## 🖥️ 9. Web Routes (Blade Views)

| Route | View | Middleware | Description |
|-------|------|-----------|-------------|
| `GET /` | `welcome` | Public | Landing page |
| `GET /auth/google` | — | Public | Google OAuth redirect |
| `GET /auth/google/callback` | — | Public | Google OAuth callback |
| `GET /set-password` | auth/set-password | `auth` | Password setup screen |
| `GET /profile` | profile/edit | `auth, password.setup` | Edit profile |
| `GET /citizen/complaints` | citizen/complaints | `role:citizen` | My complaints list |
| `GET /citizen/complaints/create` | citizen/create | `role:citizen` | New complaint form |
| `GET /citizen/complaints/{id}` | citizen/show | `role:citizen` | Complaint detail |
| `GET /engineer/complaints` | engineer/complaints/index | `role:engineer` | Active assignments |
| `GET /engineer/complaints/completed` | engineer/complaints/completed | `role:engineer` | Completed tasks |
| `GET /engineer/complaints/{id}` | engineer/complaints/show | `role:engineer` | Assignment detail |
| `GET /admin/dashboard` | admin/dashboard | `role:admin` | Admin overview |
| `GET /admin/complaints` | admin/complaints/index | `role:admin` | All complaints |
| `GET /admin/users` | admin/users/index | `role:admin` | User management |
| `GET /admin/categories` | admin/categories/index | `role:admin` | Category management |

---

## 📊 10. Key Design Decisions

### State Machine on `Complaint`
The `VALID_TRANSITIONS` constant defines every allowed status move. `updateStatus()` enforces this in a DB transaction and simultaneously writes to `StatusHistory` — you can never get a status change without an audit log entry.

### Immutable Audit Log
`StatusHistory` has no `updated_at` column (`const UPDATED_AT = null`). Entries are write-once. The model auto-calculates `time_in_previous_status` in seconds on every creation event.

### Complaint Number Generation
Uses `MAX()` on the numeric suffix (not `COUNT()`), so gaps from deleted/rolled-back records never cause duplicates. Uses `lockForUpdate()` to serialize concurrent requests inside a transaction.

### Anonymous Complaints
Citizens can mark a complaint `is_anonymous = true`. Both the complaint and feedback models support this and return `'Anonymous Citizen'` instead of the real name.

### Media Stages
`ComplaintMedia.stage` can be:
- `before` — uploaded by citizen when filing
- `during` — engineer uploads mid-fix progress
- `after` — engineer uploads proof of completion

Admin reviews `before` vs `after` side-by-side before verifying.

### Dual Storage Backend
The `ComplaintMedia` model supports `s3`, `cloudinary`, or `public` (local) disks. The `buildPublicUrl()` helper in controllers constructs correct public URLs without ACLs for S3 (relies on bucket policy instead).

### Engineer Rating Lock
After a complaint is verified, the admin can rate the engineer's work (1–5 stars). Once **both** admin has rated and citizen has submitted feedback, the rating is locked (`423 HTTP status` returned).

---

## 🧪 11. Seeded Test Data

Running `php artisan migrate --seed` creates:

| Role | Email | Password |
|------|-------|---------|
| Admin | `admin@roadwatch.test` | `password` |
| Engineer | `engineer@roadwatch.test` | `password` |
| Citizen | `citizen@roadwatch.test` | `password` |

**8 default categories** are seeded: Pothole, Waterlogging, Broken Divider, Cracked Road, Drainage Issue, Street Light, Road Collapse, Other.

---

## 📦 12. Dependencies

### Production (`require`)

| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/framework` | ^13.8 | Core framework |
| `laravel/breeze` | ^2.4 | Auth scaffolding (login, register, email verify) |
| `laravel/socialite` | ^5.27 | Google OAuth integration |
| `laravel/tinker` | ^3.0 | REPL for development |
| `league/flysystem-aws-s3-v3` | ^3.0 | AWS S3 file storage driver |
| `spatie/laravel-permission` | ^7.4 | RBAC role/permission tables |

### Development (`require-dev`)

| Package | Purpose |
|---------|---------|
| `fakerphp/faker` | Fake data for factories |
| `laravel/pail` | Real-time log viewer |
| `laravel/pint` | Code style fixer |
| `phpunit/phpunit` | Testing framework |
| `mockery/mockery` | Mock objects in tests |
| `nunomaduro/collision` | Better error reporting in CLI |

---

## 🚀 13. Running the Project

```bash
# Clone and install
git clone https://github.com/GariFFon/RoadWatch.git
cd RoadWatch
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database
touch database/database.sqlite
php artisan migrate --seed

# Storage symlink for uploaded media
php artisan storage:link

# Run (all services in parallel)
composer run dev
# OR individually:
php artisan serve        # http://localhost:8000
npm run dev              # Vite HMR
```

### Docker Support
A `Dockerfile` and `docker-start.sh` are included for containerized deployment.

---

## 🔗 14. Key URLs

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

*Report generated from source code analysis of the RoadWatch repository.*
*For full API documentation, see [`docs/API_REFERENCE.md`](docs/API_REFERENCE.md).*
