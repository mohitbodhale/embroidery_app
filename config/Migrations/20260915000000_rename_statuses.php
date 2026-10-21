<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Job statuses are now managed via the job_statuses lookup table (created in CreateCoreSchema).
 * This migration is a no-op since jobs.status was changed to VARCHAR and job_statuses
 * table already contains all required status values including 'in_progress', 'ready_for_qc', 'cancelled'.
 */
final class RenameStatuses extends BaseMigration
{
    public function change(): void
    {
        // No-op: All status values are seeded in job_statuses table by CreateCoreSchema
        // jobs.status is VARCHAR, not enum, so no ALTER TYPE needed
    }
}
