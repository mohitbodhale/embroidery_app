<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Level master: the pay grade of a job. Administrators maintain the list and the
 * payment amount earned per completed job at that level.
 */
final class CreateLevels extends BaseMigration
{
    public function change(): void
    {
        if ($this->table('levels')->exists()) {
            return;
        }

        $this->table('levels')
            ->addColumn('name', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('label', 'string', ['limit' => 128, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('payment_amount', 'decimal', [
                'precision' => 12,
                'scale' => 2,
                'null' => false,
                'default' => 0,
            ])
            ->addColumn('color', 'string', ['limit' => 16, 'null' => true])
            ->addColumn('sort_order', 'integer', ['null' => true])
            ->addColumn('is_active', 'boolean', ['null' => false, 'default' => true])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('modified_at', 'datetime', ['null' => true])
            ->addIndex(['name'], ['unique' => true, 'name' => 'idx_levels_name'])
            ->create();

        $now = date('Y-m-d H:i:s');
        $this->table('levels')->insert([
            [
                'name' => 'level_1',
                'label' => 'Level 1',
                'description' => 'Entry level. Set the payment amount before assigning jobs.',
                'payment_amount' => 0,
                'color' => '#6c757d',
                'sort_order' => 10,
                'is_active' => true,
                'created_at' => $now,
            ],
            [
                'name' => 'level_2',
                'label' => 'Level 2',
                'description' => 'Set the payment amount before assigning jobs.',
                'payment_amount' => 0,
                'color' => '#0dcaf0',
                'sort_order' => 20,
                'is_active' => true,
                'created_at' => $now,
            ],
            [
                'name' => 'level_3',
                'label' => 'Level 3',
                'description' => 'Set the payment amount before assigning jobs.',
                'payment_amount' => 0,
                'color' => '#3b82f6',
                'sort_order' => 30,
                'is_active' => true,
                'created_at' => $now,
            ],
            [
                'name' => 'level_4',
                'label' => 'Level 4',
                'description' => 'Set the payment amount before assigning jobs.',
                'payment_amount' => 0,
                'color' => '#6f42c1',
                'sort_order' => 40,
                'is_active' => true,
                'created_at' => $now,
            ],
            [
                'name' => 'level_5',
                'label' => 'Level 5',
                'description' => 'Set the payment amount before assigning jobs.',
                'payment_amount' => 0,
                'color' => '#20c997',
                'sort_order' => 50,
                'is_active' => true,
                'created_at' => $now,
            ],
        ])->save();
    }
}