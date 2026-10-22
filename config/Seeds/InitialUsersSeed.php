<?php
declare(strict_types=1);

use Cake\Database\Connection;
use Migrations\BaseSeed;

/**
 * Users seed for the vehicle manufacturing deployment.
 *
 * Creates the full user roster: one admin, three schedulers, nineteen
 * operators spread across the eight vehicle manufacturing work types, three
 * quality checkers and one production user.
 *
 * Every user gets the shared default password "Password123!" (bcrypt).
 * The seed is idempotent - users that already exist are updated in place
 * rather than duplicated.
 *
 * Run with:  bin/cake seeds run InitialUsersSeed
 */
class InitialUsersSeed extends BaseSeed
{
    /**
     * Default password shared by every seeded account.
     */
    private const DEFAULT_PASSWORD = 'Password123!';

    /**
     * Run the seed.
     */
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $hash = password_hash(self::DEFAULT_PASSWORD, PASSWORD_BCRYPT);

        $users = [
            // --- Admins -----------------------------------------------------
            ['id' => 8, 'name' => 'Admin User', 'email' => 'admin@stitchcraft.com', 'role' => 'admin', 'role_id' => 1, 'work_type' => 'vehicle_design'],

            // --- Schedulers --------------------------------------------------
            ['id' => 1, 'name' => 'Scheduler User', 'email' => 'scheduler@stitchcraft.com', 'role' => 'scheduler', 'role_id' => 2, 'work_type' => null],
            ['id' => 27, 'name' => 'Production Planner 1', 'email' => 'planner1@stitchcraft.com', 'role' => 'scheduler', 'role_id' => 2, 'work_type' => 'production_planning'],
            ['id' => 28, 'name' => 'Production Planner 2', 'email' => 'planner2@stitchcraft.com', 'role' => 'scheduler', 'role_id' => 2, 'work_type' => 'production_planning'],

            // --- Vehicle design operators ------------------------------------
            ['id' => 5, 'name' => 'Design Engineer 1', 'email' => 'design1@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'vehicle_design'],
            ['id' => 10, 'name' => 'Design Engineer 2', 'email' => 'design2@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'vehicle_design'],
            ['id' => 11, 'name' => 'CAD Specialist', 'email' => 'cad_specialist@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'vehicle_design'],

            // --- Vehicle manufacturing operators -----------------------------
            ['id' => 12, 'name' => 'Assembly Tech 1', 'email' => 'assembly1@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'vehicle_manufacturing'],
            ['id' => 13, 'name' => 'Assembly Tech 2', 'email' => 'assembly2@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'vehicle_manufacturing'],
            ['id' => 14, 'name' => 'Production Line Lead', 'email' => 'prod_lead@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'vehicle_manufacturing'],
            ['id' => 15, 'name' => 'Manufacturing Operator', 'email' => 'mfg_operator@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'vehicle_manufacturing'],
            ['id' => 2, 'name' => 'Operator User', 'email' => 'operator@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'vehicle_manufacturing'],
            ['id' => 6, 'name' => 'Programmer', 'email' => 'programmer@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => null],
            ['id' => 7, 'name' => 'Accountant', 'email' => 'accountant@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => null],

            // --- Quality control ---------------------------------------------
            ['id' => 3, 'name' => 'QC Inspector 1', 'email' => 'qc1@stitchcraft.com', 'role' => 'quality_checker', 'role_id' => 4, 'work_type' => 'quality_control'],
            ['id' => 16, 'name' => 'QC Inspector 2', 'email' => 'qc2@stitchcraft.com', 'role' => 'quality_checker', 'role_id' => 4, 'work_type' => 'quality_control'],
            ['id' => 17, 'name' => 'Quality Engineer', 'email' => 'quality_eng@stitchcraft.com', 'role' => 'quality_checker', 'role_id' => 4, 'work_type' => 'quality_control'],

            // --- Maintenance ---------------------------------------------------
            ['id' => 18, 'name' => 'Maintenance Tech 1', 'email' => 'maint1@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'maintenance'],
            ['id' => 19, 'name' => 'Maintenance Tech 2', 'email' => 'maint2@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'maintenance'],

            // --- Assembly ------------------------------------------------------
            ['id' => 20, 'name' => 'Assembly Tech 3', 'email' => 'assembly3@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'assembly'],
            ['id' => 21, 'name' => 'Final Assembly Lead', 'email' => 'final_assembly@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'assembly'],
            ['id' => 4, 'name' => 'Production User', 'email' => 'production@stitchcraft.com', 'role' => 'production', 'role_id' => 5, 'work_type' => 'assembly'],

            // --- Testing --------------------------------------------------------
            ['id' => 22, 'name' => 'Test Engineer 1', 'email' => 'test_eng1@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'testing'],
            ['id' => 23, 'name' => 'Test Engineer 2', 'email' => 'test_eng2@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'testing'],

            // --- Logistics -------------------------------------------------------
            ['id' => 24, 'name' => 'Logistics Coordinator', 'email' => 'logistics_coord@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'logistics'],
            ['id' => 25, 'name' => 'Warehouse Operator', 'email' => 'warehouse_op@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'logistics'],
            ['id' => 26, 'name' => 'Shipping Clerk', 'email' => 'shipping@stitchcraft.com', 'role' => 'operator', 'role_id' => 7, 'work_type' => 'logistics'],
        ];

        $existing = [];
        foreach ($this->connection()->execute('SELECT id FROM users')->fetchAll('assoc') as $row) {
            $existing[(int)$row['id']] = true;
        }

        foreach ($users as $user) {
            $row = [
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => $hash,
                'role' => $user['role'],
                'organization_id' => 1,
                'role_id' => $user['role_id'],
                'work_type' => $user['work_type'],
                'must_change_password' => false,
            ];

            if (isset($existing[$user['id']])) {
                $this->updateRow('users', $row, ['id' => $user['id']]);
            } else {
                $this->insertRow('users', $row + ['id' => $user['id'], 'created_at' => $now]);
            }
        }

        $this->seedOperatorWallets($now);

        $this->getIo()?->out('Seeded ' . count($users) . ' users. Default password: ' . self::DEFAULT_PASSWORD);
    }

    /**
     * Give every operator a wallet row, so the wallet screens have something
     * to display.
     */
    private function seedOperatorWallets(string $now): void
    {
        $operatorIds = [2, 6, 7, 11, 12, 13, 14, 15, 18, 19, 20, 21, 22, 23, 24, 25, 26];

        $existing = [];
        foreach ($this->connection()->execute('SELECT user_id FROM operator_wallets')->fetchAll('assoc') as $row) {
            $existing[(int)$row['user_id']] = true;
        }

        foreach ($operatorIds as $userId) {
            if (isset($existing[$userId])) {
                continue;
            }
            $this->insertRow('operator_wallets', [
                'user_id' => $userId,
                'balance' => 0,
                'total_earned' => 0,
                'total_paid' => 0,
                'notes' => null,
                'created_at' => $now,
                'modified_at' => $now,
            ]);
        }
    }

    /**
     * The database connection the seed runs against.
     */
    private function connection(): Connection
    {
        return $this->getAdapter()->getConnection();
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

    /**
     * Update a single row with bound parameters.
     */
    private function updateRow(string $table, array $data, array $where): void
    {
        $assignments = [];
        $params = [];
        foreach ($data as $column => $value) {
            if ($value === null) {
                $assignments[] = sprintf('%s = NULL', $column);
                continue;
            }
            if (is_bool($value)) {
                $assignments[] = sprintf('%s = %s', $column, $value ? 'TRUE' : 'FALSE');
                continue;
            }
            $key = 'v_' . $column;
            $assignments[] = sprintf('%s = :%s', $column, $key);
            $params[$key] = $value;
        }

        $conditions = [];
        foreach ($where as $column => $value) {
            $key = 'w_' . $column;
            $conditions[] = sprintf('%s = :%s', $column, $key);
            $params[$key] = $value;
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            implode(', ', $assignments),
            implode(' AND ', $conditions)
        );

        $this->connection()->execute($sql, $params);
    }
}
