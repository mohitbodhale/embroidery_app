<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class RolesFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            ['id' => 0,  'name' => 'pending',        'label' => 'Pending',        'description' => null, 'color' => '#6c757d', 'sort_order' => 0,  'is_active' => true, 'created_at' => '2025-01-01 00:00:00'],
            ['id' => 10, 'name' => 'admin',          'label' => 'Admin',          'description' => null, 'color' => '#dc3545', 'sort_order' => 10, 'is_active' => true, 'created_at' => '2025-01-01 00:00:00'],
            ['id' => 20, 'name' => 'scheduler',      'label' => 'Scheduler',      'description' => null, 'color' => '#0d6efd', 'sort_order' => 20, 'is_active' => true, 'created_at' => '2025-01-01 00:00:00'],
            ['id' => 30, 'name' => 'digitizer',      'label' => 'Digitizer',      'description' => null, 'color' => '#198754', 'sort_order' => 30, 'is_active' => true, 'created_at' => '2025-01-01 00:00:00'],
            ['id' => 40, 'name' => 'quality_checker','label' => 'Quality Checker','description' => null, 'color' => '#ffc107', 'sort_order' => 40, 'is_active' => true, 'created_at' => '2025-01-01 00:00:00'],
            ['id' => 50, 'name' => 'production',     'label' => 'Production',     'description' => null, 'color' => '#6f42c1', 'sort_order' => 50, 'is_active' => true, 'created_at' => '2025-01-01 00:00:00'],
        ];
        parent::init();
    }
}
