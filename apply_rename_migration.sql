-- Apply rename migration manually
-- Add operator_id if missing
ALTER TABLE jobs ADD COLUMN IF NOT EXISTS operator_id INTEGER NULL;

-- Copy digitizer_id to operator_id
UPDATE jobs SET operator_id = digitizer_id WHERE digitizer_id IS NOT NULL;

-- Drop old digitizer_id constraint if exists
ALTER TABLE jobs DROP CONSTRAINT IF EXISTS fk_jobs_digitizer_id;

-- Add foreign key for operator_id
ALTER TABLE jobs ADD CONSTRAINT fk_jobs_operator_id FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE NO ACTION;

-- Add index on operator_id
CREATE INDEX IF NOT EXISTS idx_jobs_operator_id ON jobs(operator_id);

-- Add work_type_id to user_details if missing
ALTER TABLE user_details ADD COLUMN IF NOT EXISTS work_type_id INTEGER NULL;

-- Add foreign key for work_type_id
ALTER TABLE user_details ADD CONSTRAINT fk_user_details_work_type_id FOREIGN KEY (work_type_id) REFERENCES work_types(id) ON DELETE SET NULL ON UPDATE NO ACTION;

-- Remove work_type from users if present
ALTER TABLE users DROP COLUMN IF EXISTS work_type;