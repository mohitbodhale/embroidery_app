<?php
declare(strict_types=1);

use Migrations\BaseMigration;

final class CreateWorkTypes extends BaseMigration
{
    public function change(): void
    {
        if ($this->table('work_types')->exists()) {
            return;
        }

        $this->table('work_types')
            ->addColumn('name', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('label', 'string', ['limit' => 128, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('color', 'string', ['limit' => 16, 'null' => true])
            ->addColumn('sort_order', 'integer', ['null' => true])
            ->addColumn('is_active', 'boolean', ['null' => false, 'default' => true])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['name'], ['unique' => true])
            ->create();

        $this->table('work_types')->insert([
            ['name' => 'digitizing', 'label' => 'Digitizing', 'description' => 'Embroidery digitizing work', 'color' => '#0dcaf0', 'sort_order' => 10, 'is_active' => true, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'programming', 'label' => 'Programming', 'description' => 'Machine programming and stitch paths', 'color' => '#3b82f6', 'sort_order' => 20, 'is_active' => true, 'created_at' => date('Y-m-d H:i:s')],
            ['name' => 'data_entry', 'label' => 'Data Entry', 'description' => 'Pricing, invoicing, and billing data entry', 'color' => '#10b981', 'sort_order' => 30, 'is_active' => true, 'created_at' => date('Y-m-d H:i:s')],
        ])->save();
    }
}
