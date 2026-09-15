<?php
declare(strict_types=1);

use Migrations\BaseMigration;

final class RenameDigitizingStatuses extends BaseMigration
{
    public function change(): void
    {
        // Rename digitizer-specific statuses to generic, work-type-agnostic names
        $this->table('job_statuses')
            ->changeColumn('name', 'string', ['limit' => 64]);

        $this->execute("UPDATE job_statuses SET name = 'in_progress', label = 'In Progress', color = '#0d6efd' WHERE name = 'in_digitizing'");
        $this->execute("UPDATE job_statuses SET name = 'ready_for_qc', label = 'Ready for QC', color = '#6610f2' WHERE name = 'digitized'");

        $this->execute("UPDATE jobs SET status = 'in_progress' WHERE status = 'in_digitizing'");
        $this->execute("UPDATE jobs SET status = 'ready_for_qc' WHERE status = 'digitized'");
    }
}
