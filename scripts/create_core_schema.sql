-- Core schema for Embroidery App (PostgreSQL)
-- Creates organizations, roles, job_statuses, users, jobs, job_attachments, job_logs
-- and all supporting indexes / foreign keys

BEGIN;

-- 1) Organizations
CREATE TABLE IF NOT EXISTS organizations (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    domain_or_slug VARCHAR(255) NOT NULL,
    status VARCHAR(32) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()
);
CREATE UNIQUE INDEX IF NOT EXISTS organizations_domain_or_slug_idx ON organizations (domain_or_slug);

-- 2) Roles (master lookup)
CREATE TABLE IF NOT EXISTS roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(64) NOT NULL,
    label VARCHAR(128) NOT NULL,
    description TEXT,
    color VARCHAR(16),
    sort_order INTEGER,
    is_active BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()
);
CREATE UNIQUE INDEX IF NOT EXISTS roles_name_idx ON roles (name);

INSERT INTO roles (name, label, color, sort_order, is_active) VALUES
    ('pending',        'Pending',        '#6c757d', 0,  true),
    ('admin',          'Admin',          '#dc3545', 10, true),
    ('scheduler',      'Scheduler',      '#0d6efd', 20, true),
    ('digitizer',      'Digitizer',      '#198754', 30, true),
    ('quality_checker','Quality Checker','#ffc107', 40, true),
    ('production',     'Production',     '#6f42c1', 50, true)
ON CONFLICT (name) DO NOTHING;

-- 3) Job statuses (workflow master lookup)
CREATE TABLE IF NOT EXISTS job_statuses (
    id SERIAL PRIMARY KEY,
    name VARCHAR(64) NOT NULL,
    label VARCHAR(128) NOT NULL,
    description TEXT,
    color VARCHAR(16),
    sort_order INTEGER,
    is_active BOOLEAN NOT NULL DEFAULT true,
    is_terminal BOOLEAN NOT NULL DEFAULT false,
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()
);
CREATE UNIQUE INDEX IF NOT EXISTS job_statuses_name_idx ON job_statuses (name);

INSERT INTO job_statuses (name, label, color, sort_order, is_active, is_terminal) VALUES
    ('draft',           'Draft',           '#6c757d', 10, true, false),
    ('in_digitizing',   'In Digitizing',   '#0d6efd', 20, true, false),
    ('digitized',       'Digitized',       '#6610f2', 30, true, false),
    ('qc_approved',     'QC Approved',     '#198754', 40, true, false),
    ('qc_rejected',     'QC Rejected',     '#dc3545', 50, true, false),
    ('in_production',   'In Production',   '#fd7e14', 60, true, false),
    ('completed',       'Completed',       '#20c997', 70, true, true),
    ('cancelled',       'Cancelled',       '#6c757d', 80, true, true)
ON CONFLICT (name) DO NOTHING;

-- 4) Users
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password TEXT NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'digitizer',
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE RESTRICT,
    role_id INTEGER REFERENCES roles(id) ON DELETE SET NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()
);
CREATE UNIQUE INDEX IF NOT EXISTS users_unique_email_per_org ON users (organization_id, email);
CREATE INDEX IF NOT EXISTS users_org_idx ON users (organization_id);
CREATE INDEX IF NOT EXISTS users_role_id_idx ON users (role_id);

-- 5) Jobs
CREATE TABLE IF NOT EXISTS jobs (
    id SERIAL PRIMARY KEY,
    job_number VARCHAR(64) NOT NULL,
    title VARCHAR(255) NOT NULL,
    instructions TEXT,
    status VARCHAR(32) NOT NULL DEFAULT 'draft',
    created_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    operator_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    qc_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    scheduled_date TIMESTAMP WITHOUT TIME ZONE,
    status_id INTEGER REFERENCES job_statuses(id) ON DELETE SET NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now(),
    updated_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now(),
    organization_id INTEGER NOT NULL REFERENCES organizations(id) ON DELETE CASCADE
);
CREATE UNIQUE INDEX IF NOT EXISTS unique_job_number_per_org ON jobs (organization_id, job_number);
CREATE INDEX IF NOT EXISTS jobs_org_idx ON jobs (organization_id);
CREATE INDEX IF NOT EXISTS jobs_status_idx ON jobs (status);
CREATE INDEX IF NOT EXISTS jobs_status_id_idx ON jobs (status_id);

-- 6) Job Attachments
CREATE TABLE IF NOT EXISTS job_attachments (
    id SERIAL PRIMARY KEY,
    job_id INTEGER NOT NULL REFERENCES jobs(id) ON DELETE CASCADE,
    file_name VARCHAR(512) NOT NULL,
    file_path VARCHAR(1024) NOT NULL,
    file_type VARCHAR(255),
    file_size INTEGER,
    mime_type VARCHAR(255),
    comments TEXT,
    uploaded_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()
);
CREATE INDEX IF NOT EXISTS job_attachments_job_idx ON job_attachments (job_id);

-- 7) Job Logs (activity trail)
CREATE TABLE IF NOT EXISTS job_logs (
    id SERIAL PRIMARY KEY,
    job_id INTEGER REFERENCES jobs(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(128) NOT NULL,
    comments TEXT,
    created_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT now()
);
CREATE INDEX IF NOT EXISTS job_logs_job_idx ON job_logs (job_id);
CREATE INDEX IF NOT EXISTS job_logs_user_idx ON job_logs (user_id);

-- 8) Seed initial organization and admin user
INSERT INTO organizations (id, name, domain_or_slug, status)
VALUES (1, 'Default Org', 'default', 'active')
ON CONFLICT (id) DO UPDATE SET name = EXCLUDED.name;

-- Replace the password placeholder with actual bcrypt hash before running this command:
-- php -r "echo password_hash('password123', PASSWORD_BCRYPT);"
INSERT INTO users (id, name, email, password, role, organization_id, role_id, created_at)
VALUES (1, 'Administrator', 'admin@stitchcraft.com', 'PASSWORD_HASH_PLACEHOLDER', 'admin', 1, 10, now())
ON CONFLICT (id) DO NOTHING;

-- Ensure sequences are set correctly
SELECT setval(pg_get_serial_sequence('organizations','id'), (SELECT COALESCE(MAX(id),0) FROM organizations));
SELECT setval(pg_get_serial_sequence('users','id'), (SELECT COALESCE(MAX(id),0) FROM users));
SELECT setval(pg_get_serial_sequence('jobs','id'), (SELECT COALESCE(MAX(id),0) FROM jobs));
SELECT setval(pg_get_serial_sequence('job_attachments','id'), (SELECT COALESCE(MAX(id),0) FROM job_attachments));
SELECT setval(pg_get_serial_sequence('job_logs','id'), (SELECT COALESCE(MAX(id),0) FROM job_logs));

COMMIT;
