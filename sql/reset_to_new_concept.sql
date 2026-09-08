-- TrackBridge DB reset for new operator/work_type concept
-- Roles: admin, scheduler, operator, quality_checker, production
-- Work types: digitizing, programming, data_entry
-- Run this in phpMyAdmin or MySQL console when ready.

-- 1) Disable FK checks for cleanup
SET FOREIGN_KEY_CHECKS = 0;

-- 2) Remove all job-related data
TRUNCATE TABLE job_qc_reviews;
TRUNCATE TABLE job_assignments;
TRUNCATE TABLE job_logs;
TRUNCATE TABLE job_attachments;
TRUNCATE TABLE jobs;

-- 3) Remove all users except admin
DELETE FROM users WHERE role <> 'admin';

-- 4) Remove all roles except admin
DELETE FROM roles WHERE name <> 'admin';

-- 5) Remove all work types (will reseed)
TRUNCATE TABLE work_types;

-- 6) Re-enable FK checks
SET FOREIGN_KEY_CHECKS = 1;

-- 7) Ensure work_types master data exists
INSERT INTO work_types (name, label, description, color, sort_order, is_active, created_at) VALUES
  ('digitizing', 'Digitizing', 'Embroidery digitizing work', '#0dcaf0', 10, true, NOW()),
  ('programming', 'Programming', 'Machine programming and stitch paths', '#3b82f6', 20, true, NOW()),
  ('data_entry', 'Data Entry', 'Pricing, invoicing, and billing data entry', '#10b981', 30, true, NOW())
  ON DUPLICATE KEY UPDATE label = VALUES(label);

-- 8) Ensure non-admin roles exist
INSERT INTO roles (name, label, description, sort_order, is_active, created_at) VALUES
  ('scheduler', 'Scheduler', 'Creates and schedules jobs', 10, true, NOW()),
  ('operator', 'Operator', 'General operator role with work type specialization', 20, true, NOW()),
  ('quality_checker', 'Quality Checker (QC)', 'Reviews and approves/rejects work', 30, true, NOW()),
  ('production', 'Production', 'Moves approved jobs through production', 40, true, NOW()),
  ('pending', 'Pending', 'Awaiting role assignment', 50, true, NOW())
  ON DUPLICATE KEY UPDATE label = VALUES(label);

-- 9) Seed sample users (password: Password123! hashed)
INSERT INTO users (name, email, password, role, organization_id, role_id, created_at) VALUES
  ('Scheduler User', 'scheduler@stitchcraft.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'scheduler', 1, (SELECT id FROM roles WHERE name = 'scheduler'), NOW()),
  ('Operator User', 'operator@stitchcraft.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'operator', 1, (SELECT id FROM roles WHERE name = 'operator'), NOW()),
  ('QC User', 'qc1@stitchcraft.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'quality_checker', 1, (SELECT id FROM roles WHERE name = 'quality_checker'), NOW()),
  ('Production User', 'production@stitchcraft.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'production', 1, (SELECT id FROM roles WHERE name = 'production'), NOW())
  ON DUPLICATE KEY UPDATE name = VALUES(name);

-- 10) Link work_type to operator via user_details
INSERT INTO user_details (user_id, work_type_id, created_at)
SELECT u.id, wt.id, NOW()
FROM users u
JOIN work_types wt ON wt.name = 'digitizing'
WHERE u.email = 'operator@stitchcraft.com';

-- 11) Seed sample job for testing
INSERT INTO jobs (title, instructions, status, operator_id, qc_id, organization_id, created_by, created_at) VALUES
  ('Sample Embroidery Job', 'Complete digitizing and submit for QC', 'in_digitizing', 
   (SELECT id FROM users WHERE email = 'operator@stitchcraft.com'),
   (SELECT id FROM users WHERE email = 'qc1@stitchcraft.com'),
   1,
   (SELECT id FROM users WHERE email = 'scheduler@stitchcraft.com'),
   NOW());
