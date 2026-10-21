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
    /**
     * Check if column exists using CakePHP's schema collection (works on MySQL, PostgreSQL, SQLite)
     */
    private function columnExists(string $table, string $column): bool
    {
        try {
            $schema = $this->connection()->getSchemaCollection();
            if ($schema->describe($table)->hasColumn($column)) {
                return true;
            }
        } catch (\Throwable) {
            // Table might not exist yet
        }
        return false;
    }

    public function change(): void
    {
        if (!$this->table('users')->exists()) {
            return;
        }

        if (!$this->columnExists('users', 'work_type')) {
            $users = $this->table('users');
            $users->addColumn('work_type', 'string', [
                'limit' => 64,
                'null' => true,
            ]);
            $users->update();
        }
    }
}