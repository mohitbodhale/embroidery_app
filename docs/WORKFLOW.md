# TrackBridge Project Guide

This file is the project’s source-of-truth guide for architecture, setup, role permissions, workflow behavior, and the dated change history. Update the change log whenever application behavior or configuration changes.

## Overview

TrackBridge is an embroidery job management system with role-based access control. It manages the complete lifecycle of embroidery jobs from creation through digitization, quality control, and production.

**Local URL**: XAMPP commonly serves this checkout at `http://localhost/embroidery_app`. The CakePHP development server can also be started with `php bin/cake server -p 8765`.

**Stack**: PHP 8.1+, CakePHP 5, PostgreSQL, CakePHP Authentication, PHPUnit.

## Project Structure

| Path | Responsibility |
|------|----------------|
| `src/Controller/` | HTTP actions, authentication-aware request handling, workflow orchestration |
| `src/Policy/` | Job and attachment permission rules |
| `src/Model/Table/` | ORM associations, validation, query scoping, persistence hooks |
| `src/Model/Entity/` | Entity fields and mass-assignment rules |
| `templates/` | Role-aware pages, forms, layout and email templates |
| `config/` | Routes, middleware, local datasource and environment configuration |
| `config/Migrations/` | Database migrations and sample-role setup |
| `tests/` | PHPUnit fixtures and controller/model/policy tests |
| `uploads/attachments/` | Private job-file storage; serve files through authorized download actions |
| `docs/WORKFLOW.md` | This guide and change history |

---

## User Roles

| Role | Local sample account | Responsibility |
|------|-----------------------|----------------|
| `admin` | `admin@stitchcraft.com` | Cross-workspace administration, user/role management, masters, jobs and workflow actions |
| `scheduler` | `scheduler@stitchcraft.com` | Create and assign jobs; manage only jobs they created |
| `operator` | `operator.programming@stitchcraft.com` | Work on assigned jobs and upload output files |
| `quality_checker` | `qc@stitchcraft.com` | Review assigned jobs and approve or reject them |
| `production` | `production@stitchcraft.com` | Process QC-approved jobs through completion |
| `pending` | New registrations | Wait for an administrator to assign an approved role |

Sample credentials are development-only and are not documented here. Set or rotate passwords through the administrator workflow before using a deployment with real users.

**Work types** describe an operator’s specialty; they do not create separate authorization roles:
- `digitizing` — performs embroidery digitizing
- `programming` — writes machine programs/stitch paths
- `data_entry` — handles pricing/invoicing/billing data entry

---

## Job Lifecycle

```
┌─────────┐     ┌──────────────┐     ┌───────────┐     ┌────────────┐     ┌────────────────┐     ┌───────────┐
│  DRAFT  │────▶│ IN_PROGRESS  │────▶│ READY_QC │────▶│ QC_APPROVED│────▶│IN_PRODUCTION   │────▶│ COMPLETED │
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
| `draft` | Created, not started | Scheduler edits or assigns |
| `in_progress` | Assigned to an operator | Operator edits work and uploads files |
| `ready_for_qc` | Submitted for review | Assigned QC reviews |
| `qc_approved` | QC passed, ready for production | Start Production |
| `qc_rejected` | QC failed, returned for rework | Assigned operator edits and resubmits |
| `in_production` | Production has started | Complete |
| `completed` | Job finished | — |

Only the workflow actions change status. The general edit form treats status as read-only. A job must have an assigned QC reviewer before an operator can submit it.

---

## Role Permissions Matrix

| Feature | Admin | Scheduler | Operator | QC | Production | Pending |
|---------|:-----:|:---------:|:--------:|:--:|:----------:|:-------:|
| View jobs | All | Created by self | Assigned only | Assigned only | QC-approved/in production | No |
| Create jobs | Yes | Yes | No | No | No | No |
| Edit job details | All permitted jobs | Own draft jobs | Own in-progress/rework jobs | No | No | No |
| Assign operator/QC | Yes | Own jobs | No | No | No | No |
| Upload attachment | Yes | Own jobs | Own active work | Own ready-for-QC jobs | QC-approved/in production | No |
| Delete attachment | Own upload only | Own upload only | Own upload only | Own upload only | Own upload only | No |
| Delete job | Yes | Jobs created by self | No | No | No | No |
| Submit for QC | Yes | No | Own in-progress/rework job | No | No | No |
| Approve/reject QC | Yes | No | No | Assigned ready-for-QC job | No | No |
| Start/complete production | Yes | No | No | No | Production queue | No |
| Manage users and roles | Yes | No | No | No | No | No |
| Manage work types/statuses/levels | Yes | No | No | No | No | No |
| Read activity logs | All | Own jobs | Assigned jobs | Assigned jobs | Production queue | No |

---

## Navigation by Role

### Admin
- **Operations Dashboard** (`/pages/admin-dashboard`) — KPIs, user workload, job distribution
- **Jobs** (`/jobs`) and **Create Job** (`/jobs/add`) — administrators can manage all job operations
- **Users** (`/users`) — CRUD users, assign roles
- **Roles** (`/roles`) — Manage roles
- **Job Statuses** (`/job-statuses`) — Manage job workflow statuses
- **Work Types** (`/work-types`) and **Levels** (`/levels`) — Manage master data
- **Activity Logs** (`/job-logs`) — View all system activity

### Scheduler
- **Jobs** (`/jobs`) — View jobs created by this scheduler only
- **Create Job** (`/jobs/add`) — Post new jobs with files and instructions
- **Edit Job** (`/jobs/edit/{id}`) — Edit draft jobs
- **Assign Job** (`/jobs/assign/{id}`) — Assign operator and QC
- **Delete Job** (`/jobs/delete/{id}`) — Delete any job created by this scheduler

### Operator (work types: digitizing / programming / data entry)
- **Jobs** (`/jobs`) — View assigned jobs only; sidebar filters: In Progress, Sent for QC, Done
- **View Job** (`/jobs/view/{id}`) — View assigned job details
- **Edit Job** (`/jobs/edit/{id}`) — Edit only in-progress or QC-rejected assigned jobs
- **Download Files** — Download reference images/artwork
- **Upload Files** — Upload output files while the job is in progress or returned for rework
- **Submit for QC** — Mark job ready for quality review

### Quality Checker (QC)
- **Jobs** (`/jobs`) — View assigned jobs only; review ready-for-QC work
- **View Job** (`/jobs/view/{id}`) — View job details
- **Download Files** — Download work output files for review
- **Approve** (`/jobs/approve/{id}`) — Approve and move to production
- **Reject** (`/jobs/reject/{id}`) — Reject with required comment (returns to operator)

### Production
- **Jobs** (`/jobs`) — View jobs with `qc_approved` or `in_production` status, with status filters: In Progress, Done
- **Start Production** (`/jobs/start-production/{id}`) — Move `qc_approved` to `in_production`
- **Complete** (`/jobs/complete/{id}`) — Mark `in_production` as `completed`

### Pending
- **Awaiting Approval** (`/users/awaiting-approval`) — Shown until admin assigns a role
- No access to any workflow functions

### Attachments and privacy
- Scheduler and operator/QC/production attachment lists are scoped to jobs they may view.
- Any user may delete only files they uploaded, including administrators.
- Uploads are extension-checked and size-limited; files are stored outside the public webroot and downloaded through an authorization-checked action.
- Editing an attachment cannot change its job, uploader, storage path, or server-generated file metadata.

---

## Workflow Sequence

### 1. Job Creation (Scheduler)
1. Scheduler or admin creates job at `/jobs/add`; the creator is set from the authenticated session.
2. Fills in title, instructions, organization, optional operator/QC assignments, and reference files.
3. Non-admin users are pinned to their own organization; assignees must have the expected role and same organization.
4. Job starts as `draft`, or `in_progress` when an operator is assigned during creation.

### 2. Job Assignment (Scheduler)
1. Scheduler assigns an owned job at `/jobs/assign/{id}`
2. Selects an operator and QC reviewer belonging to the job organization
3. Status changes to `in_progress`

### 3. Work Completion (Operator)
1. Operator sees assigned work in Jobs and the In Progress filter
2. Downloads reference files
3. Creates work output files for their assigned work type
4. Uploads files via job edit; only active work/rework can be edited
5. Submits for QC

### 4. Quality Control (QC)
1. QC sees job in "QC Review Queue" (Ready for QC)
2. Downloads work file for review
3. **Approves** → Status becomes `qc_approved`
4. **OR Rejects** → Status becomes `qc_rejected` with comment

### 5. Rework Loop (if rejected)
1. Operator sees rejected job in queue
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
- CSRF protection is enabled for state-changing requests; forms must carry the CakePHP CSRF token.
- System test diagnostics are administrator-only and are not public endpoints.
- Debug mode defaults off in local configuration. Provide a unique `SECURITY_SALT` and environment-specific datasource and mail settings for deployment.
- Keep `config/app_local.php` and `config/.env` out of version control. The checked-in `.env.example` is a template, not a secrets file.

### Role Normalization
The system normalizes roles as follows:
| Database Role | Normalized Role |
|--------------|-----------------|
| `admin` | `admin` |
| `scheduler` | `scheduler` |
| `operator` | `operator` |
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
| `/jobs/add` | GET/POST | Create job (admin or scheduler) |
| `/jobs/edit/{id}` | GET/POST | Edit job |
| `/jobs/assign/{id}` | GET/POST | Assign job |
| `/jobs/approve/{id}` | POST | Approve job (QC) |
| `/jobs/reject/{id}` | POST | Reject job (QC) |
| `/jobs/start-production/{id}` | POST | Start production (production) |
| `/jobs/complete/{id}` | POST | Complete job (production) |

---

## Database Schema

### Users Table
- `id`, `name`, `email`, `password`, `role`, `organization_id`, `role_id`, `created_at`

### Jobs Table
- `id`, `title`, `instructions`, `status`, `operator_id`, `qc_id`, `organization_id`, `created_by`, `assigned_to`, `assigned_by`, `assigned_at`, `qc_status`, `qc_review_notes`, `qc_reviewed_at`, `revision_count`, `created_at`

### Roles Table
- `id`, `name`, `label`, `description`, `color`, `sort_order`, `is_active`, `created_at`

### Job Statuses Table
- `id`, `name`, `label`, `description`, `color`, `sort_order`, `is_active`, `is_terminal`, `created_at`

### Organizations Table
- `id`, `name`, `domain_or_slug`, `status`, `created_at`

### Job Attachments Table
- `id`, `job_id`, `file_name`, `file_path`, `file_type`, `file_size`, `mime_type`, `uploaded_by`, `created_at`

### Job Logs Table
- `id`, `job_id`, `user_id`, `action`, `comments`, `created_at`

### Job Assignments Table
- `id`, `job_id`, `assigned_to`, `assigned_by`, `assigned_at`, `completed_at`, `note`, `created_at`

### Job QC Reviews Table
- `id`, `job_id`, `qc_user_id`, `assigned_to_user_id`, `decision`, `notes`, `reviewed_at`, `created_at`

### Password Reset Tokens Table
- `id`, `user_id`, `token`, `expires_at`, `used`, `created_at`

### User Details Table
- `id`, `user_id`, `avatar`, `phone`, `location`, `website`, `bio`, `work_type_id`, `created_at`

---

## Configuration and Development

Set `DEBUG`, `SECURITY_SALT`, `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`, `DB_NAME`, and `EMAIL_TRANSPORT_*` in the process environment or local `config/.env`. The environment template is `config/.env.example`; `.env` loading is enabled by `config/bootstrap.php` when `APP_NAME` is not already set.

Use a least-privilege PostgreSQL account outside local development. Configure a real mail transport for password reset delivery; `DebugTransport` is for development only.

Useful checks:

```powershell
php vendor/bin/phpunit
composer check
```

On XAMPP where `php` is not on `PATH`, run PHPUnit with `C:\xampp82\php\php.exe`.

## Troubleshooting

### Pending Users Accessing Jobs
**Issue**: Pending users could access `/jobs` before role assignment.
**Fix**: Role check in `JobsController::index()` redirects pending users to awaiting-approval page.

### Scheduler Cannot Delete a Job
The scheduler may delete only jobs whose `created_by` matches the signed-in user. Confirm the job creator and submit deletion with a valid CSRF token.

### Operator Cannot Upload a File
The operator must be assigned to the job, and its status must be `in_progress` or `qc_rejected`. The edit page must be saved as a multipart form, and the upload must use an allowed extension and be no larger than 20 MB.

### User Role Did Not Change
Only an administrator can assign roles. Role changes are saved through `/users/assign-role/{id}` and synchronized with `role_id` by `UsersTable`.

### CSRF Errors in Testing
Use the test suite’s `enableCsrfToken()` helper for integration tests that submit POST/PATCH/PUT/DELETE requests.

---

## Seed Data

Development sample accounts (when seeded):
- Admin: admin@stitchcraft.com
- Scheduler: scheduler@stitchcraft.com
- Operator: operator.programming@stitchcraft.com
- QC: qc@stitchcraft.com
- Production: production@stitchcraft.com

Do not rely on seeded accounts or shared sample passwords outside a local development database.

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

## Change Log

Record each user-visible behavior, permission, schema, or configuration change here, newest first. Keep entries concise and link implementation or tests where useful.

### 2026-10-08
- Enabled CSRF middleware and restricted system diagnostics to administrators.
- Restricted master-data edits and audit-log mutations to admins; scoped audit and attachment listings to jobs users can access.
- Restricted scheduler job edits/assignments to owned jobs; kept scheduler job deletion ownership-based.
- Pinned job creator and non-admin organization; validated assignee roles and organization; disallowed status changes through general edit.
- Enabled admins to use workflow actions; added operator queue filters and editable active-work uploads.
- Enforced uploader-only attachment deletion and protected attachment ownership/storage metadata.
- Made audit log writes transactional with job state changes; removed identity/session payloads from debug logs.
- Made local debug default off, added environment overrides and local environment-template loading.
- Added policy/controller regressions for access rules and corrected stale page/CSRF test expectations.

## Current Implementation Status

### Completed
- Role-based auth and policies (`src/Policy/JobPolicy.php`, `src/Policy/JobAttachmentPolicy.php`)
- Scheduler privacy: sees only own jobs; can delete only own jobs
- Operator sidebar: generic "Jobs" label with status filters (In Progress / Sent for QC / Done)
- Operator edit restriction: only in-progress assigned jobs are editable
- Attachment rules: upload allowed per `JobAttachmentPolicy::canAdd`; delete allowed only for owner/admin/scheduler
- Dynamic role validation: accepts any role present in the `roles` table
- Operator `work_type` field added to users via migration `config/Migrations/20260907190000_add_operator_work_type_to_users.php`

### Pending DB Migration
Run the following SQL when MySQL is available to add the `work_type` column to `users`:

```sql
ALTER TABLE users ADD COLUMN work_type VARCHAR(64) NULL AFTER role_id;
```
 so that many type of job operator can do 

so shedular is assigned the operator to any job according to what type of job is and which operator can do that type of job 

so basically roles are 
admin , shedular , operator , qc, production so operatos have multiple work type so check all database schema and ui is workin on that way,
if needed then modified the database also