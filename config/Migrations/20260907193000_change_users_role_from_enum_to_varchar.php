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

        $users = $this->table('users');
        // Change column type to string/varchar (works on both MySQL and PostgreSQL)
        // CakePHP will generate appropriate ALTER TABLE for each driver
        $users->changeColumn('role', 'string', [
            'limit' => 50,
            'null' => false,
            'default' => 'digitizer',
        ]);
        $users->update();
    }
}
