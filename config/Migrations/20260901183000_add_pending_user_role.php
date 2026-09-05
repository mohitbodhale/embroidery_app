<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Allows public registrations to wait for an Administrator role assignment. */
final class AddPendingUserRole extends BaseMigration
{
    public function up(): void
    {
        if ($this->getAdapter()->getAdapterType() !== 'pgsql') {
            return;
        }

        $typeExists = $this->fetchRow("SELECT 1 FROM pg_type WHERE typname = 'user_role'");
        if ($typeExists) {
            $this->execute("ALTER TYPE user_role ADD VALUE IF NOT EXISTS 'pending'");
        }
    }

    public function down(): void
    {
        // PostgreSQL cannot safely remove a value from an enum in place.
    }
}
