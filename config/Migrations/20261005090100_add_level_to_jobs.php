<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Assign a pay level to a job. Both columns are nullable so existing jobs keep
 * working untouched and a job without a level simply earns nothing.
 *
 * level_payment is a snapshot of levels.payment_amount taken when the level is
 * set, so later edits to a level's rate never rewrite historical job earnings.
 */
final class AddLevelToJobs extends BaseMigration
{
    public function change(): void
    {
        if (!$this->table('jobs')->exists()) {
            return;
        }

        $table = $this->table('jobs');

        if (
            !$table->hasColumn('level_id') &&
            !$table->hasForeignKey('level_id', 'fk_jobs_level_id')
        ) {
            $table
                ->addColumn('level_id', 'integer', ['null' => true, 'after' => 'qc_id'])
                ->addIndex(['level_id'], ['name' => 'idx_jobs_level_id'])
                ->addForeignKey('level_id', 'levels', 'id', [
                    'delete' => 'SET_NULL',
                    'update' => 'NO_ACTION',
                    'constraint' => 'fk_jobs_level_id',
                ])
                ->update();
        }

        if (!$table->hasColumn('level_payment')) {
            $table
                ->addColumn('level_payment', 'decimal', [
                    'precision' => 12,
                    'scale' => 2,
                    'null' => true,
                    'after' => 'level_id',
                ])
                ->update();
        }
    }
}