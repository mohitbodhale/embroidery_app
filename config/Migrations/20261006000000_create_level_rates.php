<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Payment periods for a level. A level can carry any number of
 * intervals, each with its own amount, so the rate an operator earns
 * depends on when the job is scheduled. `valid_from`/`valid_to` are
 * inclusive and either may be left open (null) for an unbounded end.
 */
final class CreateLevelRates extends BaseMigration
{
    public function change(): void
    {
        if ($this->table('level_rates')->exists()) {
            return;
        }

        $this->table('level_rates')
            ->addColumn('level_id', 'integer', ['null' => false])
            ->addColumn('payment_amount', 'decimal', [
                'precision' => 12,
                'scale' => 2,
                'null' => false,
                'default' => 0,
            ])
            ->addColumn('valid_from', 'datetime', ['null' => true])
            ->addColumn('valid_to', 'datetime', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('modified_at', 'datetime', ['null' => true])
            ->addForeignKey('level_id', 'levels', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addIndex(['level_id', 'valid_from'], ['name' => 'idx_level_rates_level_from'])
            ->create();

        // Seed one open-ended interval per existing level so jobs keep
        // resolving a rate even before an administrator adds periods.
        $rows = [];
        foreach ($this->fetchAll('SELECT id FROM levels ORDER BY sort_order ASC') as $level) {
            $rows[] = [
                'level_id' => (int)$level['id'],
                'payment_amount' => 0,
                'valid_from' => null,
                'valid_to' => null,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }
        if ($rows !== []) {
            $this->table('level_rates')->insert($rows)->save();
        }
    }
}
