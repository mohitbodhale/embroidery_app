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

        try {
            $this->execute('ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(50) USING role::text');
        } catch (\Throwable $e) {
        }

        try {
            $this->execute('ALTER TABLE users MODIFY role VARCHAR(50)');
        } catch (\Throwable $e) {
        }
    }
}
