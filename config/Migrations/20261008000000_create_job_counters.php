<?php
declare(strict_types=1);

use Migrations\BaseMigration;

final class CreateJobCounters extends BaseMigration
{
    public function change(): void
    {
        if ($this->table('job_counters')->exists()) {
            return;
        }

        $this->table('job_counters')
            ->addColumn('last_number', 'integer', ['null' => false, 'default' => 0])
            ->create();
    }
}