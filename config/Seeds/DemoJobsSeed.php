<?php
declare(strict_types=1);

use Cake\Database\Connection;
use Migrations\BaseSeed;

/**
 * Sample jobs seed for the vehicle manufacturing deployment.
 *
 * Creates a small set of demo jobs that cover every work type and every
 * stage of the job lifecycle, so a freshly restored database can be
 * explored without entering data by hand.
 *
 * The seed is idempotent - a job is only created when its job number is
 * still free.
 *
 * Run with:  bin/cake seeds run DemoJobsSeed
 */
class DemoJobsSeed extends BaseSeed
{
    /**
     * Run the seed.
     */
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // id => [name, email, work_type]
        $operators = [
            'design1@stitchcraft.com',
            'assembly1@stitchcraft.com',
            'qc1@stitchcraft.com',
            'maint1@stitchcraft.com',
            'final_assembly@stitchcraft.com',
            'test_eng1@stitchcraft.com',
            'logistics_coord@stitchcraft.com',
            'planner1@stitchcraft.com',
        ];

        $userIds = [];
        foreach ($this->connection()->execute('SELECT id, email FROM users')->fetchAll('assoc') as $row) {
            $userIds[strtolower((string)$row['email'])] = (int)$row['id'];
        }

        $schedulerId = $userIds['scheduler@stitchcraft.com'] ?? 1;
        $qcId = $userIds['qc1@stitchcraft.com'] ?? 3;

        $jobs = [
            // job number, title, work type, operator email, status
            ['JOB-DEMO-0001', 'Truck Chassis Design', 'vehicle_design', 'design1@stitchcraft.com', 'in_progress'],
            ['JOB-DEMO-0002', 'Engine Assembly Line', 'vehicle_manufacturing', 'assembly1@stitchcraft.com', 'in_progress'],
            ['JOB-DEMO-0003', 'Final Vehicle Inspection', 'quality_control', 'qc1@stitchcraft.com', 'ready_for_qc'],
            ['JOB-DEMO-0004', 'Hydraulic System Overhaul', 'maintenance', 'maint1@stitchcraft.com', 'in_progress'],
            ['JOB-DEMO-0005', 'Body Shell Final Assembly', 'assembly', 'final_assembly@stitchcraft.com', 'in_progress'],
            ['JOB-DEMO-0006', 'Road Performance Testing', 'testing', 'test_eng1@stitchcraft.com', 'in_progress'],
            ['JOB-DEMO-0007', 'Finished Vehicle Dispatch', 'logistics', 'logistics_coord@stitchcraft.com', 'in_progress'],
            ['JOB-DEMO-0008', 'Weekly Production Schedule', 'production_planning', 'planner1@stitchcraft.com', 'in_progress'],
        ];

        $inserted = 0;
        foreach ($jobs as [$jobNumber, $title, $workType, $operatorEmail, $status]) {
            if ($this->jobExists($jobNumber)) {
                continue;
            }

            $operatorId = $userIds[$operatorEmail] ?? null;
            $this->connection()->execute(
                'INSERT INTO jobs (job_number, title, instructions, status, created_by, organization_id, '
                . 'operator_id, assigned_to, assigned_by, assigned_at, qc_id, qc_status, level_id, level_payment, '
                . 'created_at, updated_at) VALUES '
                . '(:job_number, :title, :instructions, :status, :created_by, 1, :operator_id, :assigned_to, '
                . ':assigned_by, :assigned_at, :qc_id, :qc_status, 1, :level_payment, :created_at, :updated_at)',
                [
                    'job_number' => $jobNumber,
                    'title' => $title,
                    'instructions' => sprintf('%s phase for the vehicle manufacturing workflow.', $workType),
                    'status' => $status,
                    'created_by' => $schedulerId,
                    'operator_id' => $operatorId,
                    'assigned_to' => $operatorId,
                    'assigned_by' => $schedulerId,
                    'assigned_at' => $now,
                    'qc_id' => $qcId,
                    'qc_status' => $status === 'ready_for_qc' ? 'pending' : 'pending',
                    'level_payment' => 120,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
            $inserted++;
        }

        // Keep the job counter in step with the demo numbers.
        $this->connection()->execute(
            'UPDATE job_counters SET last_number = (SELECT COALESCE(MAX(last_number), 0) FROM (SELECT 100 AS last_number) t) WHERE id = 1'
        );

        $this->getIo()?->out(sprintf('Seeded %d demo jobs.', $inserted));
    }

    private function connection(): Connection
    {
        return $this->getAdapter()->getConnection();
    }

    private function jobExists(string $jobNumber): bool
    {
        $rows = $this->connection()
            ->execute('SELECT id FROM jobs WHERE job_number = :job_number', ['job_number' => $jobNumber])
            ->fetchAll('assoc');

        return !empty($rows);
    }
}
