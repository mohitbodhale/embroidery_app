<?php
declare(strict_types=1);

use Cake\Database\Connection;
use Migrations\BaseSeed;

/**
 * Reference data seed for the vehicle manufacturing deployment.
 *
 * Restores every lookup / master table the application needs in order to run:
 * organizations, roles, work types, job statuses, levels, level rates and the
 * job counter.
 *
 * The seed is idempotent - every row is guarded by an existence check, so
 * running it against a database that already holds the data leaves it
 * untouched. All statements go through Cake's connection with bound
 * parameters, so the same code runs on MySQL and PostgreSQL.
 *
 * Run with:  bin/cake seeds run ReferenceDataSeed
 */
class ReferenceDataSeed extends BaseSeed
{
    /**
     * Run the seed.
     */
    public function run(): void
    {
        $this->seedOrganizations();
        $this->seedRoles();
        $this->seedWorkTypes();
        $this->seedJobStatuses();
        $this->seedLevels();
        $this->seedLevelRates();
        $this->seedJobCounters();

        $this->getIo()?->out('Reference data seeded.');
    }

    private function seedOrganizations(): void
    {
        if ($this->rowExists('organizations', 'id', 1)) {
            return;
        }
        $this->insertRow('organizations', [
            'id' => 1,
            'name' => 'Default Organization',
            'domain_or_slug' => 'default-organization',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function seedRoles(): void
    {
        $roles = [
            ['id' => 1, 'name' => 'admin', 'label' => 'Admin', 'description' => null, 'color' => null, 'sort_order' => 10],
            ['id' => 2, 'name' => 'scheduler', 'label' => 'Scheduler', 'description' => null, 'color' => null, 'sort_order' => 20],
            ['id' => 4, 'name' => 'quality_checker', 'label' => 'Quality Checker', 'description' => null, 'color' => null, 'sort_order' => 40],
            ['id' => 5, 'name' => 'production', 'label' => 'Production', 'description' => null, 'color' => null, 'sort_order' => 50],
            ['id' => 7, 'name' => 'operator', 'label' => 'Operator', 'description' => 'General operator role. Use work_type to define the specialty.', 'color' => null, 'sort_order' => 35],
        ];

        foreach ($roles as $role) {
            if ($this->rowExists('roles', 'id', $role['id'])) {
                continue;
            }
            $this->insertRow('roles', $role + [
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedWorkTypes(): void
    {
        $workTypes = [
            ['id' => 1, 'name' => 'vehicle_design', 'label' => 'Vehicle Design', 'description' => 'CAD modeling, specifications, and design reviews', 'color' => '#3B82F6', 'sort_order' => 1],
            ['id' => 2, 'name' => 'vehicle_manufacturing', 'label' => 'Vehicle Manufacturing', 'description' => 'Assembly, production line operations', 'color' => '#10B981', 'sort_order' => 2],
            ['id' => 3, 'name' => 'quality_control', 'label' => 'Quality Control', 'description' => 'Inspection, testing, and compliance', 'color' => '#F59E0B', 'sort_order' => 3],
            ['id' => 4, 'name' => 'maintenance', 'label' => 'Maintenance', 'description' => 'Preventive and corrective maintenance', 'color' => '#EF4444', 'sort_order' => 4],
            ['id' => 5, 'name' => 'assembly', 'label' => 'Assembly', 'description' => 'Final assembly and packaging', 'color' => '#8B5CF6', 'sort_order' => 5],
            ['id' => 6, 'name' => 'testing', 'label' => 'Testing', 'description' => 'Functional and performance testing', 'color' => '#EC4899', 'sort_order' => 6],
            ['id' => 7, 'name' => 'logistics', 'label' => 'Logistics', 'description' => 'Parts inventory, shipping, and distribution', 'color' => '#06B6D4', 'sort_order' => 7],
            ['id' => 8, 'name' => 'production_planning', 'label' => 'Production Planning', 'description' => 'Scheduling and resource allocation', 'color' => '#84CC16', 'sort_order' => 8],
        ];

        foreach ($workTypes as $workType) {
            if ($this->rowExists('work_types', 'id', $workType['id'])) {
                continue;
            }
            $this->insertRow('work_types', $workType + [
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedJobStatuses(): void
    {
        $statuses = [
            ['id' => 1, 'name' => 'draft', 'label' => 'Draft', 'description' => null, 'color' => '#6c757d', 'sort_order' => 10, 'is_terminal' => false],
            ['id' => 2, 'name' => 'in_progress', 'label' => 'In Progress', 'description' => null, 'color' => '#0d6efd', 'sort_order' => 20, 'is_terminal' => false],
            ['id' => 3, 'name' => 'ready_for_qc', 'label' => 'Ready for QC', 'description' => null, 'color' => '#6610f2', 'sort_order' => 30, 'is_terminal' => false],
            ['id' => 4, 'name' => 'qc_approved', 'label' => 'QC Approved', 'description' => null, 'color' => '#198754', 'sort_order' => 40, 'is_terminal' => false],
            ['id' => 5, 'name' => 'qc_rejected', 'label' => 'QC Rejected', 'description' => null, 'color' => '#dc3545', 'sort_order' => 50, 'is_terminal' => false],
            ['id' => 6, 'name' => 'in_production', 'label' => 'In Production', 'description' => null, 'color' => '#fd7e14', 'sort_order' => 60, 'is_terminal' => false],
            ['id' => 7, 'name' => 'completed', 'label' => 'Completed', 'description' => null, 'color' => '#20c997', 'sort_order' => 70, 'is_terminal' => true],
            ['id' => 8, 'name' => 'cancelled', 'label' => 'Cancelled', 'description' => null, 'color' => '#6c757d', 'sort_order' => 80, 'is_terminal' => true],
        ];

        foreach ($statuses as $status) {
            if ($this->rowExists('job_statuses', 'id', $status['id'])) {
                continue;
            }
            $this->insertRow('job_statuses', $status + [
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedLevels(): void
    {
        $levels = [
            ['id' => 1, 'name' => 'level_1', 'label' => 'Level 1', 'description' => 'Entry level. Set the payment amount before assigning jobs.', 'payment_amount' => 100.00, 'color' => '#6c757d', 'sort_order' => 10],
            ['id' => 2, 'name' => 'level_2', 'label' => 'Level 2', 'description' => 'Set the payment amount before assigning jobs.', 'payment_amount' => 0.00, 'color' => '#0dcaf0', 'sort_order' => 20],
            ['id' => 3, 'name' => 'level_3', 'label' => 'Level 3', 'description' => 'Set the payment amount before assigning jobs.', 'payment_amount' => 0.00, 'color' => '#3b82f6', 'sort_order' => 30],
            ['id' => 4, 'name' => 'level_4', 'label' => 'Level 4', 'description' => 'Set the payment amount before assigning jobs.', 'payment_amount' => 0.00, 'color' => '#6f42c1', 'sort_order' => 40],
            ['id' => 5, 'name' => 'level_5', 'label' => 'Level 5', 'description' => 'Set the payment amount before assigning jobs.', 'payment_amount' => 0.00, 'color' => '#20c997', 'sort_order' => 50],
        ];

        foreach ($levels as $level) {
            if ($this->rowExists('levels', 'id', $level['id'])) {
                continue;
            }
            $this->insertRow('levels', $level + [
                'is_active' => true,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedLevelRates(): void
    {
        $rates = [
            ['id' => 2, 'level_id' => 2, 'payment_amount' => 0.00, 'valid_from' => null, 'valid_to' => null],
            ['id' => 3, 'level_id' => 3, 'payment_amount' => 0.00, 'valid_from' => null, 'valid_to' => null],
            ['id' => 4, 'level_id' => 4, 'payment_amount' => 0.00, 'valid_from' => null, 'valid_to' => null],
            ['id' => 5, 'level_id' => 5, 'payment_amount' => 0.00, 'valid_from' => null, 'valid_to' => null],
            ['id' => 6, 'level_id' => 1, 'payment_amount' => 120.00, 'valid_from' => '2026-10-01 00:00:00', 'valid_to' => null],
            ['id' => 7, 'level_id' => 1, 'payment_amount' => 110.00, 'valid_from' => null, 'valid_to' => '2026-09-30 00:00:00'],
        ];

        foreach ($rates as $rate) {
            if ($this->rowExists('level_rates', 'id', $rate['id'])) {
                continue;
            }
            $this->insertRow('level_rates', $rate + [
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedJobCounters(): void
    {
        if ($this->rowExists('job_counters', 'id', 1)) {
            return;
        }
        $this->insertRow('job_counters', [
            'id' => 1,
            'last_number' => 0,
        ]);
    }

    /**
     * The database connection the seed runs against.
     */
    private function connection(): Connection
    {
        return $this->getAdapter()->getConnection();
    }

    /**
     * Check whether a row already exists for the given key.
     */
    private function rowExists(string $table, string $column, $value): bool
    {
        $sql = sprintf('SELECT %s FROM %s WHERE %s = :value', $column, $table, $column);
        $rows = $this->connection()->execute($sql, ['value' => $value])->fetchAll('assoc');

        return !empty($rows);
    }

    /**
     * Insert a single row with bound parameters.
     *
     * NULL and boolean values are inlined as SQL literals because PDO cannot
     * bind them portably - `TRUE`/`FALSE`/`NULL` are valid on both MySQL and
     * PostgreSQL.
     */
    private function insertRow(string $table, array $data): void
    {
        $columns = [];
        $placeholders = [];
        $params = [];
        foreach ($data as $column => $value) {
            $columns[] = $column;
            if ($value === null) {
                $placeholders[] = 'NULL';
                continue;
            }
            if (is_bool($value)) {
                $placeholders[] = $value ? 'TRUE' : 'FALSE';
                continue;
            }
            $key = 'v_' . $column;
            $placeholders[] = ':' . $key;
            $params[$key] = $value;
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $this->connection()->execute($sql, $params);
    }
}
