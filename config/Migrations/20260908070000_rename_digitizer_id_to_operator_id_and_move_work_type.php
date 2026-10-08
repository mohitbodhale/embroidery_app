<?php
declare(strict_types=1);

use Migrations\BaseMigration;

final class RenameDigitizerIdToOperatorIdAndMoveWorkType extends BaseMigration
{
    private function columnExists(string $table, string $column): bool
    {
        try {
            $rows = $this->fetchAll("PRAGMA table_info('{$table}')");
            foreach ($rows as $row) {
                $name = $row['name'] ?? $row['COLUMN_NAME'] ?? null;
                if ($name === $column) {
                    return true;
                }
            }
        } catch (\Throwable) {
            // SQLite metadata lookup is not available to all databases.
        }

        try {
            $row = $this->fetchRow(
                "SELECT 1 FROM information_schema.columns
                 WHERE table_name = '{$table}' AND column_name = '{$column}'"
            );
            return (bool) $row;
        } catch (\Throwable) {
            return false;
        }
    }

    public function change(): void
    {
        if (!$this->table('jobs')->exists() || !$this->table('user_details')->exists()) {
            return;
        }

        // 1) Add operator_id to jobs if missing.
        if (!$this->columnExists('jobs', 'operator_id')) {
            try {
                $this->execute('ALTER TABLE jobs ADD COLUMN operator_id INTEGER NULL');
            } catch (\Throwable $e) {
            }
        }

        // 2) Copy existing digitizer_id data to operator_id.
        try {
            $this->execute('UPDATE jobs SET operator_id = digitizer_id WHERE digitizer_id IS NOT NULL');
        } catch (\Throwable $e) {
        }

        // 3) Drop old digitizer_id column if it is still present.
        if ($this->columnExists('jobs', 'digitizer_id')) {
            try {
                $this->execute('ALTER TABLE jobs DROP COLUMN digitizer_id');
            } catch (\Throwable $e) {
            }
        }

        // 4) Add work_type_id to user_details if missing.
        if (!$this->columnExists('user_details', 'work_type_id')) {
            try {
                $this->execute('ALTER TABLE user_details ADD COLUMN work_type_id INTEGER NULL');
            } catch (\Throwable $e) {
            }
        }

        // 5) Remove work_type from users if present (moved to user_details.work_type_id).
        if ($this->columnExists('users', 'work_type')) {
            try {
                $this->execute('ALTER TABLE users DROP COLUMN work_type');
            } catch (\Throwable $e) {
            }
        }

        try {
            $this->execute('CREATE INDEX IF NOT EXISTS idx_jobs_operator_id ON jobs(operator_id)');
        } catch (\Throwable $e) {
        }
    }
}
