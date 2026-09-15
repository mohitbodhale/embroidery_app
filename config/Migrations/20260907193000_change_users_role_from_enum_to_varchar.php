<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Change users.role from enum to varchar so any role from the roles master
 * table can be assigned dynamically.
 */
final class ChangeUsersRoleFromEnumToVarchar extends BaseMigration
{
    public function change(): void
    {
        if (!$this->table('users')->exists()) {
            return;
        }

        $row = $this->fetchRow("SELECT data_type FROM information_schema.columns WHERE table_name = 'users' AND column_name = 'role'");
        if ($row && $row['data_type'] === 'USER-DEFINED') {
            try {
                $this->execute("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50) USING role::text");
            } catch (\Throwable $e) {
            }
        }

        try {
            $row = $this->fetchRow("
                SELECT 1 FROM information_schema.constraint_column_usage
                WHERE table_name = 'users' AND column_name = 'role'
            ");
            if ($row) {
                try {
                    $this->execute("ALTER TABLE users DROP CONSTRAINT user_role");
                } catch (\Throwable $e) {
                }
            }
        } catch (\Throwable $e) {
        }
    }
}
