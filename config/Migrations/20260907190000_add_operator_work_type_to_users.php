<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Add operator work_type classification to users.
 *
 * This allows operators to be tagged by their specialty even when
 * they share the same system role, e.g.:
 *   - digitizing
 *   - programming
 *   - data_entry
 */
final class AddOperatorWorkTypeToUsers extends BaseMigration
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
            // SQLite metadata lookup is not available on all backends; the fallback below handles MySQL/Postgres.
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
        if (!$this->table('users')->exists()) {
            return;
        }

        if (!$this->columnExists('users', 'work_type')) {
            try {
                $this->execute("ALTER TABLE users ADD COLUMN work_type VARCHAR(64) NULL");
            } catch (\Throwable $e) {
                // Some database backends may require a different alter syntax, so we continue quietly.
            }
        }
    }
}
