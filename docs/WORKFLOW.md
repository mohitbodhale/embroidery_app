# TrackBridge Workflow Documentation

## Overview

TrackBridge is an embroidery job management system with role-based access control. It manages the complete lifecycle of embroidery jobs from creation through digitization, quality control, and production.

**Server**: `http://localhost:8765`

---

## User Roles

| Role | Email | Password | Description |
|------|-------|----------|-------------|
| Admin | admin@stitchcraft.com | Password123! | Full system access, user/role management |
| Scheduler | scheduler@stitchcraft.com | Password123! | Creates and assigns jobs |
| Digitizer | digitizer1@stitchcraft.com | Password123! | Performs digitization work |
| Quality Checker (QC) | qc1@stitchcraft.com | Password123! | Reviews and approves/rejects work |
| Production | production@stitchcraft.com | Password123! | Moves approved jobs through production |
| Pending | (new registrations) | — | Awaiting role assignment by admin |

---

## Job Lifecycle

```
┌─────────┐     ┌──────────────┐     ┌───────────┐     ┌────────────┐     ┌────────────────┐     ┌───────────┐
│  DRAFT  │────▶│IN_DIGITIZING │────▶│ DIGITIZED │────▶│ QC_APPROVED│────▶│IN_PRODUCTION   │────▶│ COMPLETED │
└─────────┘     └──────┬───────┘     └─────┬──────┘     └────────────┘     └───────┬────────┘     └───────────┘
                        │                    │                                       │
                        │            ┌───────┴───────┐                               │
                        │            │               │                               │
                        │            ▼               ▼                               │
                        │     ┌──────────┐   ┌────────────┐                         │
                        └────▶│QC_REJECTED│   │  REWORK    │◀────────────────────────┘
                              └──────────┘   └────────────┘
```

### Status Definitions

| Status | Description | Action |
|--------|-------------|--------|
| `draft` | Job created by scheduler, not started | Edit/Assign |
| `in_digitizing` | Assigned to digitizer, work in progress | Upload EMB |
| `digitized` | EMB file uploaded, awaiting QC | QC Review |
| `qc_approved` | QC passed, ready for production | Start Production |
| `qc_rejected` | QC failed, returned to digitizer | Resubmit |
| `in_production` | Production has started | Complete |
| `completed` | Job finished | — |

---

## Role Permissions Matrix

| Feature | Admin | Scheduler | Digitizer | QC | Production | Pending |
|---------|:------:|:---------:|:---------:|:--:|:-----------:|:-------:|
| View all jobs | ✓ | ✓ | Own only | Assigned | Assigned | ✗ |
| Create job | ✗ | ✓ | ✗ | ✗ | ✗ | ✗ |
| Edit job | ✓ | ✓ | Assigned | Assigned | ✗ | ✗ |
| Assign job | ✓ | ✓ | ✗ | ✗ | ✗ | ✗ |
| Upload EMB file | ✗ | ✗ | ✓ | ✗ | ✗ | ✗ |
| Submit for QC | ✗ | ✗ | ✓ | ✗ | ✗ | ✗ |
| QC Review (approve/reject) | ✗ | ✗ | ✗ | ✓ | ✗ | ✗ |
| Start production | ✗ | ✗ | ✗ | ✗ | ✓ | ✗ |
| Mark complete | ✗ | ✗ | ✗ | ✗ | ✓ | ✗ |
| User management | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Role management | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Job status management | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| Activity logs | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |
| System configuration | ✓ | ✗ | ✗ | ✗ | ✗ | ✗ |

---

## Navigation by Role

### Admin
- **Operations Dashboard** (`/pages/admin-dashboard`) — KPIs, user workload, job distribution
- **Users** (`/users`) — CRUD users, assign roles
- **Roles** (`/roles`) — Manage roles
- **Job Statuses** (`/job-statuses`) — Manage job workflow statuses
- **Activity Logs** (`/job-logs`) — View all system activity

### Scheduler
- **Jobs** (`/jobs`) — View all organization jobs
- **Create Job** (`/jobs/add`) — Post new jobs with files and instructions
- **Edit Job** (`/jobs/edit/{id}`) — Edit draft jobs
- **Assign Job** (`/jobs/assign/{id}`) — Assign digitizers and QC

### Digitizer
- **My Digitizing Queue** (`/jobs`) — Jobs assigned to this digitizer
- **View Job** (`/jobs/view/{id}`) — View assigned job details
- **Download Files** — Download reference images/artwork
- **Upload EMB** — Upload completed digitizing file
- **Submit for QC** — Mark job ready for quality review

### Quality Checker (QC)
- **QC Review Queue** (`/jobs`) — Jobs with `digitized` status assigned to this QC
- **View Job** (`/jobs/view/{id}`) — View job details
- **Download EMB** — Download EMB files for review
- **Approve** (`/jobs/approve/{id}`) — Approve and move to production
- **Reject** (`/jobs/reject/{id}`) — Reject with required comment (returns to digitizer)

### Production
- **Production Queue** (`/jobs`) — Jobs with `qc_approved` or `in_production` status
- **Start Production** (`/jobs/start-production/{id}`) — Move `qc_approved` to `in_production`
- **Complete** (`/jobs/complete/{id}`) — Mark `in_production` as `completed`

### Pending
- **Awaiting Approval** (`/users/awaiting-approval`) — Shown until admin assigns a role
- No access to any workflow functions

---

## Workflow Sequence

### 1. Job Creation (Scheduler)
1. Scheduler creates job at `/jobs/add`
2. Fills in title, instructions, attaches reference files
3. Job created with `draft` status

### 2. Job Assignment (Scheduler)
1. Scheduler assigns job at `/jobs/assign/{id}`
2. Selects digitizer and QC from dropdown
3. Status changes to `in_digitizing`

### 3. Digitization (Digitizer)
1. Digitizer sees job in "My Digitizing Queue"
2. Downloads reference files
3. Creates EMB digitizing file
4. Uploads EMB file via job edit
5. Submits for QC

### 4. Quality Control (QC)
1. QC sees job in "QC Review Queue"
2. Downloads EMB file for review
3. **Approves** → Status becomes `qc_approved`
4. **OR Rejects** → Status becomes `qc_rejected` with comment

### 5. Rework Loop (if rejected)
1. Digitizer sees rejected job in queue
2. Reviews QC comment
3. Makes corrections
4. Resubmits for QC

### 6. Production (Production)
1. Production sees `qc_approved` jobs in queue
2. Starts production → Status becomes `in_production`
3. Completes job → Status becomes `completed`

---

## Authentication & Security

### Login Flow
- URL: `POST /users/login`
- Fields: `email`, `password`, `remember_me`, `_csrfToken`
- Session cookie required for authentication
- CSRF protection on all POST requests

### Role Normalization
The system normalizes roles as follows:
| Database Role | Normalized Role |
|--------------|-----------------|
| `admin` | `admin` |
| `scheduler` | `scheduler` |
| `digitizer` | `digitizer` |
| `quality_checker` | `quality_checker` |
| `production` | `production` |
| `pending` | `pending` |

### Authorization Layers
1. **Controller-level** (`requireRole()` in AppController) — Role-based access
2. **Action-level** (`JobPolicy`) — Fine-grained resource permissions

---

## Key URLs

| URL | Method | Description |
|-----|--------|-------------|
| `/users/login` | GET/POST | Login page |
| `/users/logout` | GET | Logout |
| `/users/register` | GET/POST | Registration (role=pending) |
| `/users/awaiting-approval` | GET | Pending users landing |
| `/users` | GET | User management (admin) |
| `/roles` | GET | Role management (admin) |
| `/job-statuses` | GET | Job status management (admin) |
| `/job-logs` | GET | Activity logs (admin) |
| `/pages/admin-dashboard` | GET | Admin dashboard |
| `/jobs` | GET | Jobs list (role-filtered) |
| `/jobs/add` | GET/POST | Create job (scheduler) |
| `/jobs/edit/{id}` | GET/POST | Edit job |
| `/jobs/assign/{id}` | GET/POST | Assign job |
| `/jobs/approve/{id}` | POST | Approve job (QC) |
| `/jobs/reject/{id}` | POST | Reject job (QC) |
| `/jobs/start-production/{id}` | POST | Start production (production) |
| `/jobs/complete/{id}` | POST | Complete job (production) |

---

## Database Schema

### Users Table
- `id`, `name`, `email`, `password`, `role`, `organization_id`, `created_at`

### Jobs Table
- `id`, `title`, `instructions`, `status`, `digitizer_id`, `qc_id`, `organization_id`, `created_at`

### Roles Table
- `id`, `name`, `label`, `sort_order`, `hierarchy`

### Job Statuses Table
- `id`, `name`, `label`, `color`, `is_terminal`, `sort_order`

### Organizations Table
- `id`, `name`, `domain_or_slug`, `status`, `created_at`

---

## Troubleshooting

### Pending Users Accessing Jobs
**Issue**: Pending users could access `/jobs` before role assignment.
**Fix**: Role check in `JobsController::index()` redirects pending users to awaiting-approval page.

### Admin Cannot Create Jobs
**By Design**: Admin manages users and system, not job creation. Use Scheduler role for job creation.

### CSRF Errors in Testing
CakePHP's CSRF middleware blocks automated form submissions. For testing, ensure proper cookie handling.

---

## Seed Data

Default test users are created via `config/Seeds/InitialUsersSeed.php`:
- Admin: admin@stitchcraft.com
- Scheduler: scheduler@stitchcraft.com
- Digitizer: digitizer1@stitchcraft.com
- QC: qc1@stitchcraft.com
- Production: production@stitchcraft.com

All seed users have password: `Password123!`

---

## File Structure

```
src/
├── Controller/
│   ├── AppController.php         # Base controller with auth
│   ├── JobsController.php       # Job CRUD and workflow actions
│   ├── UsersController.php      # User management, login, register
│   └── RolesController.php      # Role management
├── Model/
│   ├── Table/
│   │   ├── JobsTable.php
│   │   ├── UsersTable.php
│   │   └── RolesTable.php
│   └── Entity/
│       ├── Job.php
│       ├── User.php
│       └── Role.php
└── Policy/
    └── JobPolicy.php            # Fine-grained job permissions

templates/
├── layout/
│   └── adminlte.php            # Main layout with role-based nav
├── Jobs/
│   ├── index.php               # Jobs list (role-filtered)
│   ├── view.php                # Job detail
│   ├── add.php                 # Create job form
│   └── edit.php                # Edit job form
└── Users/
    ├── login.php               # Login form
    ├── register.php            # Registration form
    └── awaiting-approval.php   # Pending user page
```
