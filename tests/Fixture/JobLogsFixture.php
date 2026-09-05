<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class JobLogsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'job_id' => 1,
                'user_id' => 1,
                'action' => 'created',
                'comments' => 'Job created and digitizer assigned.',
                'created_at' => '2025-01-01 08:00:00',
            ],
            [
                'id' => 2,
                'job_id' => 2,
                'user_id' => 2,
                'action' => 'submitted_for_qc',
                'comments' => 'EMB file submitted for QC review.',
                'created_at' => '2025-01-03 10:30:00',
            ],
        ];
        parent::init();
    }
}
