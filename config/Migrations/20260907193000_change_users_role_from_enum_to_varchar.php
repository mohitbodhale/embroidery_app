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

        $connection = $this->getConnection();

        // Drop the old enum constraint if it exists
        try {
            $connection->execute("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50) USING role::text");
        } catch (\Throwable $e) {
            // If it fails because the constraint/type is different, ignore
        }

        // Try to drop the enum constraint by name if it exists
        $row = $this->fetchRow("
            SELECT 1 FROM information_schema.constraint_column_usage
            WHERE table_name = 'users' AND column_name = 'role'
        ");
        if ($row) {
            try {
                $connection->execute("ALTER TABLE users DROP CONSTRAINT user_role");
            } catch (\Throwable $e) {
                // Constraint may not exist or have a different name
            }
        }
    }
}
