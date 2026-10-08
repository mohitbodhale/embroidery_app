<?php
declare(strict_types=1);

use Migrations\BaseMigration;

final class SeedNewRolesAndSampleUsers extends BaseMigration
{
    private function columnExists(string $table, string $column): bool
    {
        try {
            $rows = $this->fetchAll("PRAGMA table_info('{$table}')");
            foreach ($rows as $row) {
                $name = $row['name'] ?? $row['COLUMN_NAME'] ?? null;
                if ($name === $column) {
                    return true;
                }
            }
        } catch (\Throwable) {
        }

        try {
            $row = $this->fetchRow(
                "SELECT 1 FROM information_schema.columns
                 WHERE table_name = '{$table}' AND column_name = '{$column}'"
            );
            return (bool) $row;
        } catch (\Throwable) {
            return false;
        }
    }

    public function change(): void
    {
        if (!$this->table('roles')->exists() || !$this->table('users')->exists() || !$this->table('work_types')->exists()) {
            return;
        }

        if (!$this->table('user_details')->exists()) {
            $this->table('user_details')
                ->addColumn('user_id', 'integer', ['null' => false])
                ->addColumn('phone', 'string', ['limit' => 20, 'null' => true])
                ->addColumn('bio', 'text', ['null' => true])
                ->addColumn('avatar', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('website', 'string', ['limit' => 255, 'null' => true])
                ->addColumn('location', 'string', ['limit' => 100, 'null' => true])
                ->addColumn('work_type_id', 'integer', ['null' => true])
                ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'datetime', ['null' => true])
                ->addIndex(['user_id'], ['unique' => true])
                ->create();
        }

        foreach (['user_id', 'phone', 'bio', 'avatar', 'website', 'location', 'work_type_id', 'created_at', 'updated_at'] as $column) {
            if (!$this->columnExists('user_details', $column)) {
                $sql = match ($column) {
                    'user_id' => 'ALTER TABLE user_details ADD COLUMN user_id INTEGER NOT NULL',
                    'phone' => 'ALTER TABLE user_details ADD COLUMN phone VARCHAR(20) NULL',
                    'bio' => 'ALTER TABLE user_details ADD COLUMN bio TEXT NULL',
                    'avatar' => 'ALTER TABLE user_details ADD COLUMN avatar VARCHAR(255) NULL',
                    'website' => 'ALTER TABLE user_details ADD COLUMN website VARCHAR(255) NULL',
                    'location' => 'ALTER TABLE user_details ADD COLUMN location VARCHAR(100) NULL',
                    'work_type_id' => 'ALTER TABLE user_details ADD COLUMN work_type_id INTEGER NULL',
                    'created_at' => 'ALTER TABLE user_details ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
                    'updated_at' => 'ALTER TABLE user_details ADD COLUMN updated_at DATETIME NULL',
                    default => null,
                };

                if ($sql !== null) {
                    try {
                        $this->execute($sql);
                    } catch (\Throwable) {
                    }
                }
            }
        }

        $now = date('Y-m-d H:i:s');

        // 1) Ensure required roles exist
        $existing = array_column($this->fetchAll('SELECT name FROM roles'), 'name');
        $rolesToInsert = [];
        foreach (['scheduler', 'operator', 'quality_checker', 'production', 'pending'] as $role) {
            if (!in_array($role, $existing, true)) {
                $label = ucwords(str_replace('_', ' ', $role));
                if ($role === 'quality_checker') {
                    $label = 'Quality Checker (QC)';
                }
                $rolesToInsert[] = [
                    'name' => $role,
                    'label' => $label,
                    'description' => '',
                    'sort_order' => 10,
                    'is_active' => true,
                    'created_at' => $now,
                ];
            }
        }
        if (!empty($rolesToInsert)) {
            $this->table('roles')->insert($rolesToInsert)->save();
        }

        // 2) Get or create a default organization
        $org = $this->fetchRow('SELECT id FROM organizations LIMIT 1');
        if (!$org) {
            $this->table('organizations')->insert([
                'name' => 'Default Organization',
                'domain_or_slug' => 'default-organization',
                'status' => 'active',
                'created_at' => $now,
            ])->save();
            $org = $this->fetchRow('SELECT id FROM organizations LIMIT 1');
        }
        $orgId = $org['id'] ?? 1;

        // 3) Ensure work_types exist
        $wtExisting = array_column($this->fetchAll('SELECT name FROM work_types'), 'name');
        $wtToInsert = [];
        foreach ([
            ['digitizing', 'Digitizing', 'Embroidery digitizing work', '#0dcaf0', 10],
            ['programming', 'Programming', 'Machine programming and stitch paths', '#3b82f6', 20],
            ['data_entry', 'Data Entry', 'Pricing, invoicing, and billing data entry', '#10b981', 30],
        ] as $wt) {
            if (!in_array($wt[0], $wtExisting, true)) {
                $wtToInsert[] = [
                    'name' => $wt[0],
                    'label' => $wt[1],
                    'description' => $wt[2],
                    'color' => $wt[3],
                    'sort_order' => $wt[4],
                    'is_active' => true,
                    'created_at' => $now,
                ];
            }
        }
        if (!empty($wtToInsert)) {
            $this->table('work_types')->insert($wtToInsert)->save();
        }

        // 4) Seed sample users with hashed password Password123!
        $defaultPassword = password_hash('Password123!', PASSWORD_DEFAULT);
        $roleIds = array_column($this->fetchAll('SELECT id, name FROM roles'), 'id', 'name');

        $usersToInsert = [
            ['scheduler', 'scheduler@stitchcraft.com', 'Scheduler User'],
            ['operator', 'operator@stitchcraft.com', 'Operator User'],
            ['quality_checker', 'qc1@stitchcraft.com', 'QC User'],
            ['production', 'production@stitchcraft.com', 'Production User'],
        ];

        foreach ($usersToInsert as $u) {
            $roleId = $roleIds[$u[0]] ?? null;
            if (!$roleId) {
                continue;
            }
            $this->table('users')->insert([
                'name' => $u[2],
                'email' => $u[1],
                'password' => $defaultPassword,
                'role' => $u[0],
                'organization_id' => $orgId,
                'role_id' => $roleId,
                'created_at' => $now,
            ])->save();
        }

        // 5) Assign work_type to operator via user_details
        $operator = $this->fetchRow("SELECT id FROM users WHERE email = 'operator@stitchcraft.com'");
        if ($operator) {
            $wtDigitizing = $this->fetchRow("SELECT id FROM work_types WHERE name = 'digitizing' LIMIT 1");
            if ($wtDigitizing) {
                $this->table('user_details')->insert([
                    'user_id' => $operator['id'],
                    'work_type_id' => $wtDigitizing['id'],
                    'created_at' => $now,
                ])->save();
            }
        }
    }
}
