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
    public function change(): void
    {
        if (!$this->table('users')->exists()) {
            return;
        }

        $columnExists = function (string $column): bool {
            $row = $this->fetchRow(
                "SELECT 1 FROM information_schema.columns
                 WHERE table_name = 'users' AND column_name = '{$column}'"
            );
            return (bool) $row;
        };

        if (!$columnExists('work_type')) {
            $this->table('users')
                ->addColumn('work_type', 'string', [
                    'limit'   => 64,
                    'null'    => true,
                    'after'   => 'role_id',
                ])
                ->update();
        }
    }
}
