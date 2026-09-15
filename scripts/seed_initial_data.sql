-- Seed initial organization, roles lookup, and admin user for Embroidery App
-- IMPORTANT: generate bcrypt password hash using PHP and replace PASSWORD_HASH_PLACEHOLDER.

BEGIN;

-- Create a default organization if not exists
INSERT INTO organizations (id, name, domain_or_slug, status)
VALUES (1, 'Default Org', 'default', 'active')
ON CONFLICT (id) DO UPDATE SET name = EXCLUDED.name;

-- Insert roles (safe to re-run)
INSERT INTO roles (name, label, color, sort_order, is_active) VALUES
    ('pending',        'Pending',        '#6c757d', 0,  true),
    ('admin',          'Admin',          '#dc3545', 10, true),
    ('scheduler',      'Scheduler',      '#0d6efd', 20, true),
    ('digitizer',      'Digitizer',      '#198754', 30, true),
    ('quality_checker','Quality Checker','#ffc107', 40, true),
    ('production',     'Production',     '#6f42c1', 50, true)
ON CONFLICT (name) DO NOTHING;

-- Insert job statuses (safe to re-run)
INSERT INTO job_statuses (name, label, color, sort_order, is_active, is_terminal) VALUES
    ('draft',           'Draft',           '#6c757d', 10, true, false),
    ('in_progress',     'In Progress',     '#0d6efd', 20, true, false),
    ('ready_for_qc',     'Ready for QC',     '#6610f2', 30, true, false),
    ('qc_approved',     'QC Approved',     '#198754', 40, true, false),
    ('qc_rejected',     'QC Rejected',     '#dc3545', 50, true, false),
    ('in_production',   'In Production',   '#fd7e14', 60, true, false),
    ('completed',       'Completed',       '#20c997', 70, true, true),
    ('cancelled',       'Cancelled',       '#6c757d', 80, true, true)
ON CONFLICT (name) DO NOTHING;

-- Replace the password placeholder with actual bcrypt hash before running this file
-- Example: INSERT INTO users (id, name, email, password, role, organization_id, role_id, created_at)
-- VALUES (1, 'Administrator', 'admin@stitchcraft.com', '$2y$10$...hash...', 'admin', 1, 10, now());
INSERT INTO users (id, name, email, password, role, organization_id, role_id, created_at)
VALUES (1, 'Administrator', 'admin@stitchcraft.com', 'PASSWORD_HASH_PLACEHOLDER', 'admin', 1, 10, now())
ON CONFLICT (id) DO NOTHING;

-- Ensure sequences are set correctly
SELECT setval(pg_get_serial_sequence('organizations','id'), (SELECT COALESCE(MAX(id),0) FROM organizations));
SELECT setval(pg_get_serial_sequence('roles','id'), (SELECT COALESCE(MAX(id),0) FROM roles));
SELECT setval(pg_get_serial_sequence('job_statuses','id'), (SELECT COALESCE(MAX(id),0) FROM job_statuses));
SELECT setval(pg_get_serial_sequence('users','id'), (SELECT COALESCE(MAX(id),0) FROM users));
SELECT setval(pg_get_serial_sequence('jobs','id'), (SELECT COALESCE(MAX(id),0) FROM jobs));
SELECT setval(pg_get_serial_sequence('job_attachments','id'), (SELECT COALESCE(MAX(id),0) FROM job_attachments));
SELECT setval(pg_get_serial_sequence('job_logs','id'), (SELECT COALESCE(MAX(id),0) FROM job_logs));

COMMIT;

-- After running this seed (with proper password hash), login with the admin credentials and change the password.
