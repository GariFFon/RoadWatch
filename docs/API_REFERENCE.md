# RoadWatch — REST API Reference

**Base URL:** `http://localhost:8000/api/v1` (dev) · `https://yourdomain.com/api/v1` (prod)
**Auth:** Laravel session cookie (`auth:web`) — axios sends `X-XSRF-TOKEN` automatically
**API Version:** v1 · **Last updated:** 2026-05-14

---

## Table of Contents

| # | Group | Endpoints |
|---|-------|-----------|
| 1 | [Public](#1-public) | Categories, Complaint Options |
| 2 | [Auth](#2-auth--me) | Me (profile) |
| 3 | [Citizen — Complaints](#3-citizen--complaints) | List, Create, Show, Delete |
| 4 | [Citizen — Media](#4-citizen--media) | Upload, Delete |
| 5 | [Citizen — Feedback](#5-citizen--feedback) | Submit rating |
| 6 | [Engineer — Complaints](#6-engineer--complaints) | List, Show, Update status |
| 7 | [Engineer — Media](#7-engineer--media) ⭐ | Upload evidence, Delete evidence |
| 8 | [Admin — Complaints](#8-admin--complaints) | List all, Show detail, Assign, Update status |
| 9 | [Admin — Users](#9-admin--users) | List, Change role |
| — | [Status Transitions](#status-transitions) | Valid transition rules |
| — | [Media Stages](#media-stages) | Before vs After |
| — | [Response Shapes](#standard-response-shapes) | Standard shapes |
| — | [Error Codes](#error-codes) | HTTP errors |

---

## Auth Roles

| Role | Middleware | Route prefix |
|------|-----------|--------------|
| `citizen` | `auth:web` + `role:citizen` | `/api/v1/citizen/...` |
| `engineer` | `auth:web` + `role:engineer` | `/api/v1/engineer/...` |
| `admin` | `auth:web` + `role:admin` | `/api/v1/admin/...` |

---

## 1. Public

### `GET /api/v1/categories`
> All active complaint categories. **No auth required.**

**Response `200`**
```json
{
  "data": [
    { "id": 1, "name": "Pothole",      "icon": "🕳️", "description": "Holes in road surface" },
    { "id": 2, "name": "Waterlogging", "icon": "🌊", "description": null }
  ]
}
```

**Axios**
```js
const { data } = await axios.get('/api/v1/categories');
// data.data → array of categories
```

---

### `GET /api/v1/complaint-options` ⭐
> Returns all valid status and severity values for filter dropdowns. **No auth required.** Used by admin and engineer panels to populate `<select>` elements dynamically — no hardcoding needed.

**Response `200`**
```json
{
  "statuses": [
    { "value": "pending",      "label": "Pending",      "icon": "⏳" },
    { "value": "under_review", "label": "Under Review", "icon": "🔍" },
    { "value": "in_progress",  "label": "In Progress",  "icon": "🔧" },
    { "value": "resolved",     "label": "Resolved",     "icon": "✅" },
    { "value": "rejected",     "label": "Rejected",     "icon": "❌" }
  ],
  "severities": [
    { "value": "low",       "label": "Low",       "icon": "🟢" },
    { "value": "medium",    "label": "Medium",    "icon": "🟡" },
    { "value": "high",      "label": "High",      "icon": "🟠" },
    { "value": "emergency", "label": "Emergency", "icon": "🔴" }
  ]
}
```

**Axios**
```js
const res  = await fetch('/api/v1/complaint-options');
const data = await res.json();
// data.statuses   → array
// data.severities → array
```

---

## 2. Auth — Me

### `GET /api/v1/me`
> Returns authenticated user's profile. Use this instead of embedding user data in HTML.

**Auth:** `auth:web`
**Request Payload:** none (GET)

**Response `200`**
```json
{
  "data": {
    "id": 1,
    "name": "Gourav Dash",
    "email": "gourav@example.com",
    "phone": "9876543210",
    "role": "citizen",
    "gender": "male",
    "profile_photo_url": "https://lh3.googleusercontent.com/...",
    "auth_provider": "google",
    "is_active": true,
    "created_at": "2026-05-14T02:00:00+00:00"
  }
}
```

**Axios**
```js
const { data } = await axios.get('/api/v1/me');
// data.data → user object
```

---

## 3. Citizen — Complaints

**Auth:** `role:citizen` — scoped to the authenticated citizen only.

---

### `GET /api/v1/citizen/complaints`
> Paginated list of the citizen's own complaints with category + thumbnail media.

**Request Payload:** none (GET)

**Query Params**
```
?page=1&per_page=10
```

| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `page` | integer | `1` | Page number |
| `per_page` | integer | `10` | Items per page |

**Response `200`**
```json
{
  "data": [
    {
      "id": 12,
      "complaint_number": "RW-2026-0012",
      "title": "Large pothole on MG Road",
      "description": "Deep pothole approximately 2 feet wide...",
      "status": "pending",
      "severity": "high",
      "location": "Near City Mall, MG Road",
      "latitude": 12.97194,
      "longitude": 77.59369,
      "is_anonymous": false,
      "votes_count": 3,
      "views_count": 7,
      "resolved_at": null,
      "created_at": "2026-05-14T02:15:00+00:00",
      "updated_at": "2026-05-14T02:15:00+00:00",
      "category": { "id": 1, "name": "Pothole", "icon": "🕳️" },
      "media": [
        {
          "id": 3,
          "file_type": "image",
          "stage": "before",
          "cloud_url": "http://localhost:8000/storage/complaints/12/images/photo.jpg",
          "original_name": "photo.jpg",
          "size_bytes": 204800,
          "sort_order": 0
        }
      ]
    }
  ],
  "meta": { "current_page": 1, "last_page": 3, "per_page": 10, "total": 27 },
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." }
}
```

**Axios**
```js
const { data } = await axios.get('/api/v1/citizen/complaints', {
  params: { page: 1, per_page: 10 }
});
// data.data → array  |  data.meta → pagination info
```

---

### `POST /api/v1/citizen/complaints`
> Create a new complaint with optional images/videos. Runs inside a DB transaction.

**Content-Type:** `multipart/form-data`

**Request Payload**
```
title          = "Large pothole near bus stop"        [string, required, max:255]
description    = "2-foot wide hole, very dangerous"  [string, required, min:20, max:2000]
category_id    = 1                                   [integer, required, exists:categories]
severity       = "high"                              [enum: low|medium|high|emergency]
latitude       = 12.97194                            [numeric, required, -90 to 90]
longitude      = 77.59369                            [numeric, required, -180 to 180]
location       = "Near City Mall, MG Road"           [string, required, max:500]
is_anonymous   = false                               [boolean, optional, default:false]
images[]       = <file>                              [optional, max 5 files, jpg/png/webp/heic, 10MB each]
videos[]       = <file>                              [optional, max 2 files, mp4/mov/webm, 50MB each]
```

**Response `201 Created`**
```json
{
  "message": "Complaint submitted successfully.",
  "complaint": {
    "id": 13,
    "complaint_number": "RW-2026-0013",
    "title": "Large pothole near bus stop",
    "status": "pending",
    "severity": "high",
    "category": { "id": 1, "name": "Pothole", "icon": "🕳️" },
    "created_at": "2026-05-14T02:30:00+00:00"
  }
}
```

**Response `422`** (validation failure)
```json
{
  "message": "The title field is required.",
  "errors": {
    "title":    ["The title field is required."],
    "latitude": ["The latitude field is required."]
  }
}
```

**Axios**
```js
const formData = new FormData();
formData.append('title',        'Large pothole near bus stop');
formData.append('description',  'A 2-foot wide pothole near the main junction.');
formData.append('category_id',  1);
formData.append('severity',     'high');
formData.append('latitude',     12.97194);
formData.append('longitude',    77.59369);
formData.append('location',     'Near City Mall, MG Road');
formData.append('is_anonymous', false);
// Optional files:
[...imageFiles].forEach(f => formData.append('images[]', f));
[...videoFiles].forEach(f => formData.append('videos[]', f));

const { data } = await axios.post('/api/v1/citizen/complaints', formData, {
  headers: { 'Content-Type': 'multipart/form-data' }
});
// data.complaint → created complaint object
```

---

### `GET /api/v1/citizen/complaints/{id}`
> Full detail — includes media, status history (audit trail), and feedback. Also increments `views_count`.

**Request Payload:** none (GET)

**Errors:** `403` if not your complaint · `404` if not found

**Response `200`**
```json
{
  "data": {
    "id": 12,
    "complaint_number": "RW-2026-0012",
    "title": "Large pothole on MG Road",
    "description": "Deep pothole approximately 2 feet wide near the main junction...",
    "status": "in_progress",
    "severity": "high",
    "location": "Near City Mall, MG Road",
    "latitude": 12.97194,
    "longitude": 77.59369,
    "is_anonymous": false,
    "votes_count": 5,
    "views_count": 15,
    "resolved_at": null,
    "created_at": "2026-05-14T02:15:00+00:00",
    "updated_at": "2026-05-14T09:00:00+00:00",
    "category": { "id": 1, "name": "Pothole", "icon": "🕳️" },
    "media": [
      {
        "id": 3, "file_type": "image", "stage": "before",
        "cloud_url": "http://localhost:8000/storage/complaints/12/images/photo.jpg",
        "original_name": "photo.jpg", "size_bytes": 204800, "sort_order": 0
      }
    ],
    "status_histories": [
      {
        "id": 2,
        "old_status": "under_review",
        "new_status": "in_progress",
        "remarks": "Engineer assigned and on-site work started.",
        "changed_by": { "id": 5, "name": "👷 Engineer", "role": "engineer" },
        "created_at": "2026-05-14T09:00:00+00:00"
      },
      {
        "id": 1,
        "old_status": "pending",
        "new_status": "under_review",
        "remarks": null,
        "changed_by": { "id": 2, "name": "🛡 Admin", "role": "admin" },
        "created_at": "2026-05-14T06:00:00+00:00"
      }
    ],
    "feedback": null
  }
}
```

**Axios**
```js
const { data } = await axios.get(`/api/v1/citizen/complaints/${id}`);
// data.data → full complaint object
```

---

### `DELETE /api/v1/citizen/complaints/{id}`
> Permanently deletes a complaint **and all its media files**. Only works when status is `pending`.

**Request Payload:** none

**Response `200`**
```json
{ "message": "Complaint deleted." }
```

**Errors:**
- `403` — not your complaint
- `422` — complaint is not in `pending` status

**Axios**
```js
const { data } = await axios.delete(`/api/v1/citizen/complaints/${id}`);
// data.message → "Complaint deleted."
```

---

## 4. Citizen — Media

### `POST /api/v1/citizen/complaints/{id}/media`
> Upload additional files to an existing complaint. File type is auto-detected by MIME.

**Content-Type:** `multipart/form-data`

**Request Payload**
```
files[]  = <file>      [required, max 5, jpg/png/webp/mp4/mov/webm, 50MB each]
stage    = "before"    [optional, enum: before|after, default: before]
```

**Response `201`**
```json
{
  "message": "Media uploaded.",
  "data": [
    { "id": 7, "file_type": "image", "cloud_url": "http://localhost:8000/storage/..." }
  ]
}
```

**Axios**
```js
const formData = new FormData();
[...files].forEach(f => formData.append('files[]', f));
formData.append('stage', 'after');   // 'before' or 'after'

const { data } = await axios.post(`/api/v1/citizen/complaints/${id}/media`, formData, {
  headers: { 'Content-Type': 'multipart/form-data' }
});
```

---

### `DELETE /api/v1/citizen/media/{mediaId}`
> Delete a single media file from storage and DB. Only the uploader can delete.

**Request Payload:** none

**Response `200`**
```json
{ "message": "Media deleted." }
```

**Axios**
```js
await axios.delete(`/api/v1/citizen/media/${mediaId}`);
```

---

## 5. Citizen — Feedback

### `POST /api/v1/citizen/complaints/{id}/feedback`
> Submit a star rating + optional comment for a **resolved** complaint. One per complaint.

**Content-Type:** `application/json`

**Request Payload**
```json
{
  "rating":  4,
  "comment": "Fixed quickly, very happy with the response!"
}
```

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `rating` | integer | ✅ | 1 to 5 |
| `comment` | string | ❌ | max 500 chars |

**Response `201`**
```json
{
  "message": "Feedback submitted.",
  "feedback": { "rating": 4, "comment": "Fixed quickly, very happy!" }
}
```

**Errors:**
- `403` — not your complaint
- `409` — feedback already submitted
- `422` — complaint is not `resolved` yet

**Axios**
```js
const { data } = await axios.post(`/api/v1/citizen/complaints/${id}/feedback`, {
  rating:  4,
  comment: 'Fixed quickly!'
});
```

---

## 6. Engineer — Complaints

**Auth:** `role:engineer` — scoped to complaints assigned to this engineer.

---

### `GET /api/v1/engineer/complaints`
> Paginated list of complaints assigned to this engineer.

**Query Params**

| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `page` | integer | `1` | Page number |
| `per_page` | integer | `15` | Items per page |
| `status` | string | — | Filter by status |

**Response `200`** — same shape as citizen complaint list with `submitted_by` included.

**Axios**
```js
const { data } = await axios.get('/api/v1/engineer/complaints', {
  params: { page: 1, per_page: 12, status: 'in_progress' }
});
```

---

### `GET /api/v1/engineer/complaints/{id}`
> Full detail of an assigned complaint with citizen info, media, and status history.

**Request Payload:** none (GET)

**Errors:** `403` if this complaint is not assigned to the logged-in engineer.

**Axios**
```js
const { data } = await axios.get(`/api/v1/engineer/complaints/${id}`);
```

---

### `PATCH /api/v1/engineer/complaints/{id}/status`
> Update complaint status. Enforces valid transition rules. Creates an audit record automatically.

**Content-Type:** `application/json`

**Request Payload**
```json
{
  "status":  "in_progress",
  "remarks": "Engineer on-site. Repair work in progress."
}
```

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `status` | string | ✅ | must be a valid transition from current status |
| `remarks` | string | ❌ | max 500 chars — shown in citizen's timeline |

**Valid status transitions**
```
pending      →  under_review   OR  rejected
under_review →  in_progress    OR  rejected
in_progress  →  resolved       OR  rejected
resolved     →  (terminal — no changes allowed)
rejected     →  (terminal — no changes allowed)
```

**Response `200`**
```json
{ "message": "Status updated.", "status": "in_progress" }
```

**Response `422`** (invalid transition)
```json
{ "message": "Cannot transition from pending to resolved." }
```

**Axios**
```js
const { data } = await axios.patch(`/api/v1/engineer/complaints/${id}/status`, {
  status:  'in_progress',
  remarks: 'Work has started on site.'
});
```

---

## 7. Engineer — Media ⭐

**Auth:** `role:engineer` — only the **assigned engineer** for a complaint can upload/delete its after-work evidence.

---

### `POST /api/v1/engineer/complaints/{id}/media`
> Upload after-work evidence (photos or videos) to prove the work is done. Files are always stored as `stage = "after"`, distinguishing them from the citizen's `before` photos. Admin reviews these before marking a complaint resolved.

**Content-Type:** `multipart/form-data`

**Request Payload**
```
files[]  = <file>    [required, 1–10 files, jpg/jpeg/png/webp/mp4/mov/webm, 50MB each]
```

**Response `201`**
```json
{
  "message": "3 file(s) uploaded as work evidence.",
  "data": [
    { "id": 9, "file_type": "image", "stage": "after", "cloud_url": "http://localhost:8000/storage/..." }
  ]
}
```

**Errors:**
- `403` — complaint is not assigned to you
- `422` — invalid file type or exceeds size limit

**Axios**
```js
const form = new FormData();
[...files].forEach(f => form.append('files[]', f));

const { data } = await axios.post(
  `/api/v1/engineer/complaints/${id}/media`, form,
  {
    headers: { 'Content-Type': 'multipart/form-data' },
    onUploadProgress: e => console.log(`${Math.round(e.loaded/e.total*100)}%`)
  }
);
```

---

### `DELETE /api/v1/engineer/media/{mediaId}`
> Remove a previously uploaded after-work evidence file. Only files the engineer uploaded (`uploaded_by === auth()->id()`) with `stage = "after"` can be deleted.

**Request Payload:** none

**Response `200`**
```json
{ "message": "File removed." }
```

**Errors:**
- `403` — you did not upload this file
- `422` — file is not `stage: after` (citizen before-photos cannot be deleted by engineer)

**Axios**
```js
await axios.delete(`/api/v1/engineer/media/${mediaId}`);
```

---

## 8. Admin — Complaints

**Auth:** `role:admin`

---

### `GET /api/v1/admin/complaints`
> All complaints platform-wide with optional filters. Includes citizen + engineer info.

**Query Params**

| Param | Type | Default | Description |
|-------|------|---------|-------------|
| `status` | string | — | `pending` / `under_review` / `in_progress` / `resolved` / `rejected` |
| `severity` | string | — | `low` / `medium` / `high` / `emergency` |
| `page` | integer | `1` | Page number |
| `per_page` | integer | `20` | Items per page (max 100) |

**Response `200`** — paginated `ComplaintResource` collection (same shape as citizen list, includes `submitted_by`).

**Axios**
```js
const { data } = await axios.get('/api/v1/admin/complaints', {
  params: { status: 'pending', severity: 'high', page: 1, per_page: 20 }
});
```

---

### `GET /api/v1/admin/complaints/{id}` ⭐
> Full detail of a single complaint including citizen info, assigned engineer, **all media (before + after)**, and complete status history. Used by the admin slide-over panel to review engineer's work evidence before changing status.

**Response `200`**
```json
{
  "data": {
    "id": 12,
    "complaint_number": "RW-2026-0012",
    "title": "Large pothole on MG Road",
    "description": "...",
    "status": "in_progress",
    "severity": "high",
    "location": "Near City Mall, MG Road",
    "category": { "id": 1, "name": "Pothole", "icon": "🕳️" },
    "media": [
      { "id": 3, "file_type": "image", "stage": "before", "cloud_url": "..." },
      { "id": 9, "file_type": "image", "stage": "after",  "cloud_url": "..." }
    ],
    "status_histories": [
      {
        "old_status": "under_review", "new_status": "in_progress",
        "remarks": "Crew dispatched",
        "changed_by": { "id": 5, "name": "Ravi Kumar", "role": "engineer" },
        "created_at": "2026-05-14T09:00:00+00:00"
      }
    ],
    "submitted_by": "Gourav Dash"
  }
}
```

**Axios**
```js
const { data } = await axios.get(`/api/v1/admin/complaints/${id}`);
// data.data.media → filter by stage: 'before' | 'after'
```

---

### `PATCH /api/v1/admin/complaints/{id}/assign`
> Assign a complaint to a specific engineer.

**Content-Type:** `application/json`

**Request Payload**
```json
{
  "engineer_id": 5
}
```

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `engineer_id` | integer | ✅ | must exist in `users` table |

**Response `200`**
```json
{ "message": "Complaint assigned." }
```

**Axios**
```js
const { data } = await axios.patch(`/api/v1/admin/complaints/${id}/assign`, {
  engineer_id: 5
});
```

---

### `PATCH /api/v1/admin/complaints/{id}/status` ⭐
> Admin sets complaint to **any** status directly. Unlike the engineer endpoint, this is **not** bound by `VALID_TRANSITIONS` — admin can jump to `resolved` after reviewing after-photos or revert to `in_progress` if work is unsatisfactory.

**Content-Type:** `application/json`

**Request Payload**
```json
{ "status": "resolved", "remarks": "Work verified. Pothole filled correctly." }
```

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `status` | string | ✅ | any valid status value |
| `remarks` | string | ❌ | max 500 chars — shown in timeline |

**Typical workflow:**
```
Admin opens complaint slide-over → reviews before + after photos
  ✅ Work is good  → PATCH status: "resolved"
  ❌ Work is poor  → PATCH status: "in_progress", remarks: "Rework required"
```

**Response `200`**
```json
{ "message": "Status updated.", "status": "resolved" }
```

**Axios**
```js
const { data } = await axios.patch(`/api/v1/admin/complaints/${id}/status`, {
  status:  'resolved',
  remarks: 'Pothole fixed and road surface is smooth.'
});
```

---

## 9. Admin — Users

**Auth:** `role:admin`

---

### `GET /api/v1/admin/users`
> Paginated list of all platform users with role and status.

**Request Payload:** none (GET)
```
?page=1
```

**Response `200`**
```json
{
  "data": [
    {
      "id": 1, "name": "Gourav Dash", "email": "gourav@example.com",
      "role": "citizen", "phone": "9876543210",
      "is_active": true, "created_at": "2026-05-14T02:00:00+00:00"
    }
  ],
  "meta": { "current_page": 1, "last_page": 2, "total": 18 }
}
```

**Axios**
```js
const { data } = await axios.get('/api/v1/admin/users', { params: { page: 1 } });
```

---

### `PATCH /api/v1/admin/users/{id}/role`
> Change a user's role. Use to promote a citizen to engineer or admin.

**Content-Type:** `application/json`

**Request Payload**
```json
{
  "role": "engineer"
}
```

| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `role` | string | ✅ | `citizen` / `engineer` / `admin` |

**Response `200`**
```json
{ "message": "User role updated to engineer." }
```

**Axios**
```js
const { data } = await axios.patch(`/api/v1/admin/users/${userId}/role`, {
  role: 'engineer'
});
```

---

## Status Transitions

### Engineer (enforced by `VALID_TRANSITIONS`):
```
pending  ──►  under_review  ──►  in_progress  ──►  resolved
   │               │                  │
   └──► rejected   └──► rejected       └──► rejected
```
Engineers **cannot skip steps** — must follow the order above.

### Admin (unrestricted):
Admin can set **any** status via `PATCH /api/v1/admin/complaints/{id}/status` — useful to revert, force-resolve, or reject at any stage.

---

## Media Stages

| `stage` | Uploaded by | When | Who can delete |
|---------|-------------|------|----------------|
| `before` | Citizen | When filing the complaint | Citizen (own files) |
| `after` | Engineer | After completing work | Engineer (own uploads only) |

- **Before** → `POST /api/v1/citizen/complaints` or `POST /api/v1/citizen/complaints/{id}/media`
- **After** → `POST /api/v1/engineer/complaints/{id}/media` (always `stage=after`)
- Admin sees **both stages side-by-side** in the slide-over detail panel before changing status

---

## Standard Response Shapes

### Single resource
```json
{ "data": { ...fields... } }
```

### Paginated collection
```json
{
  "data":  [ ...items... ],
  "meta":  { "current_page": 1, "last_page": 5, "per_page": 10, "total": 47 },
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." }
}
```

### Created / action with data
```json
{ "message": "...", "data": { ...resource... } }
```

### Deleted / simple action
```json
{ "message": "..." }
```

---

## Error Codes

| Code | Meaning | When |
|------|---------|------|
| `401` | Unauthorized | Session expired / not logged in |
| `403` | Forbidden | Wrong role or resource belongs to another user |
| `404` | Not Found | Invalid ID |
| `409` | Conflict | Duplicate action (e.g. feedback already submitted) |
| `422` | Unprocessable Entity | Validation failed or business rule violated |
| `429` | Too Many Requests | Rate limit hit (throttle:60,1 in production) |
| `500` | Server Error | Check `storage/logs/laravel.log` |

**Error response shape:**
```json
{
  "message": "The title field is required.",
  "errors": {
    "title":       ["The title field is required."],
    "category_id": ["The selected category id is invalid."]
  }
}
```

---

*Last updated: 2026-05-14 · RoadWatch API v1*
