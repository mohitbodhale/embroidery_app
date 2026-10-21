<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** 
 * The 'pending' role is now handled by the roles lookup table (created in CreateCoreSchema).
 * This migration is a no-op since users.role was changed to VARCHAR.
 */
final class AddPendingUserRole extends BaseMigration
{
    public function up(): void
    {
        // No-op: 'pending' role is seeded in roles table by CreateCoreSchema
        // users.role is VARCHAR, not enum, so no ALTER TYPE needed
    }

    public function down(): void
    {
        // No-op
    }
}
