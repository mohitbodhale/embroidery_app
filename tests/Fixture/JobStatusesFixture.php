<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class JobStatusesFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            ['id' => 1, 'name' => 'draft',           'label' => 'Draft',           'description' => null, 'color' => '#6c757d', 'sort_order' => 10, 'is_active' => true, 'is_terminal' => false, 'created_at' => '2025-01-01 00:00:00'],
            ['id' => 2, 'name' => 'in_progress',     'label' => 'In Progress',     'description' => null, 'color' => '#0d6efd', 'sort_order' => 20, 'is_active' => true, 'is_terminal' => false, 'created_at' => '2025-01-01 00:00:00'],
            ['id' => 3, 'name' => 'ready_for_qc',     'label' => 'Ready for QC',     'description' => null, 'color' => '#6610f2', 'sort_order' => 30, 'is_active' => true, 'is_terminal' => false, 'created_at' => '2025-01-01 00:00:00'],
            ['id' => 4, 'name' => 'qc_approved',     'label' => 'QC Approved',     'description' => null, 'color' => '#198754', 'sort_order' => 40, 'is_active' => true, 'is_terminal' => false, 'created_at' => '2025-01-01 00:00:00'],
            ['id' => 5, 'name' => 'qc_rejected',     'label' => 'QC Rejected',     'description' => null, 'color' => '#dc3545', 'sort_order' => 50, 'is_active' => true, 'is_terminal' => false, 'created_at' => '2025-01-01 00:00:00'],
            ['id' => 6, 'name' => 'in_production',   'label' => 'In Production',   'description' => null, 'color' => '#fd7e14', 'sort_order' => 60, 'is_active' => true, 'is_terminal' => false, 'created_at' => '2025-01-01 00:00:00'],
            ['id' => 7, 'name' => 'completed',       'label' => 'Completed',       'description' => null, 'color' => '#20c997', 'sort_order' => 70, 'is_active' => true, 'is_terminal' => true,  'created_at' => '2025-01-01 00:00:00'],
            ['id' => 8, 'name' => 'cancelled',       'label' => 'Cancelled',       'description' => null, 'color' => '#6c757d', 'sort_order' => 80, 'is_active' => true, 'is_terminal' => true,  'created_at' => '2025-01-01 00:00:00'],
        ];
        parent::init();
    }
}
