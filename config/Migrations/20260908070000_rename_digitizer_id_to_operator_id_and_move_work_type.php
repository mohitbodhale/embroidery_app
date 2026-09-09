<?php
declare(strict_types=1);

use Migrations\BaseMigration;

final class RenameDigitizerIdToOperatorIdAndMoveWorkType extends BaseMigration
{
    public function change(): void
    {
        if (!$this->table('jobs')->exists() || !$this->table('user_details')->exists()) {
            return;
        }

        $connection = $this->getConnection();

        // 1) Add operator_id to jobs if missing
        $row = $this->fetchRow("
            SELECT 1 FROM information_schema.columns
            WHERE table_name = 'jobs' AND column_name = 'operator_id'
        ");
        if (!$row) {
            $connection->execute('ALTER TABLE jobs ADD COLUMN operator_id INTEGER NULL AFTER created_by')->execute();
        }

        // 2) Copy existing digitizer_id data to operator_id
        $connection->execute('UPDATE jobs SET operator_id = digitizer_id WHERE digitizer_id IS NOT NULL')->execute();

        // 3) Drop old digitizer_id foreign keys and column if present
        try {
            $connection->execute('ALTER TABLE jobs DROP CONSTRAINT IF EXISTS fk_jobs_digitizer_id')->execute();
        } catch (\Throwable $e) {
        }

        $row = $this->fetchRow("
            SELECT 1 FROM information_schema.columns
            WHERE table_name = 'jobs' AND column_name = 'digitizer_id'
        ");
        if ($row) {
            $connection->execute('ALTER TABLE jobs DROP COLUMN digitizer_id')->execute();
        }

        // 4) Add work_type_id to user_details if missing
        $row = $this->fetchRow("
            SELECT 1 FROM information_schema.columns
            WHERE table_name = 'user_details' AND column_name = 'work_type_id'
        ");
        if (!$row) {
            $connection->execute('ALTER TABLE user_details ADD COLUMN work_type_id INTEGER NULL AFTER bio')->execute();
        }

        // 5) Add foreign key for operator_id -> users.id
        try {
            $connection->execute('ALTER TABLE jobs ADD CONSTRAINT fk_jobs_operator_id FOREIGN KEY (operator_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE NO ACTION')->execute();
        } catch (\Throwable $e) {
        }

        // 6) Add foreign key for work_type_id -> work_types.id
        try {
            $connection->execute('ALTER TABLE user_details ADD CONSTRAINT fk_user_details_work_type_id FOREIGN KEY (work_type_id) REFERENCES work_types(id) ON DELETE SET NULL ON UPDATE NO ACTION')->execute();
        } catch (\Throwable $e) {
        }

        // 7) Remove work_type from users if present (moved to user_details.work_type_id)
        $row = $this->fetchRow("
            SELECT 1 FROM information_schema.columns
            WHERE table_name = 'users' AND column_name = 'work_type'
        ");
        if ($row) {
            try {
                $connection->execute('ALTER TABLE users DROP COLUMN work_type')->execute();
            } catch (\Throwable $e) {
            }
        }

        // 8) Add index on operator_id for performance
        try {
            $connection->execute('CREATE INDEX IF NOT EXISTS idx_jobs_operator_id ON jobs(operator_id)')->execute();
        } catch (\Throwable $e) {
        }
    }
}
