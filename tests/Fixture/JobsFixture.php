<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class JobsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'job_number' => 'JOB-2026-001',
                'title' => 'Floral design on polo',
                'instructions' => 'Embroider on light blue polo, use thread colours as marked.',
                'status' => 'in_digitizing',
                'created_by' => 1,
                'digitizer_id' => 2,
                'qc_id' => 3,
                'scheduled_date' => '2025-01-15 09:00:00',
                'status_id' => 2,
                'created_at' => '2025-01-01 08:00:00',
                'updated_at' => '2025-01-01 08:00:00',
                'organization_id' => 1,
            ],
            [
                'id' => 2,
                'job_number' => 'JOB-2026-002',
                'title' => 'Logo on cap',
                'instructions' => 'Small logo, single colour.',
                'status' => 'digitized',
                'created_by' => 1,
                'digitizer_id' => 2,
                'qc_id' => 3,
                'scheduled_date' => '2025-01-20 09:00:00',
                'status_id' => 3,
                'created_at' => '2025-01-02 08:00:00',
                'updated_at' => '2025-01-03 10:30:00',
                'organization_id' => 1,
            ],
        ];
        parent::init();
    }
}
