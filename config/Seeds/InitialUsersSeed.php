<?php
declare(strict_types=1);

use Migrations\BaseSeed;
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Postgres;

final class InitialUsersSeed extends BaseSeed
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $connection = $this->getAdapter()->getConnection();
        $driver = $connection->getDriver();

        // Create default organization if missing
        $orgs = $this->fetchAll("SELECT id FROM organizations WHERE id = 1");
        if (empty($orgs)) {
            $this->execute(
                "INSERT INTO organizations (id, name, domain_or_slug, status, created_at) VALUES (1, 'Default Org', 'default', 'active', ?)",
                [$now]
            );
        }

        $passwordPlain = 'Password123!';
        $hash = password_hash($passwordPlain, PASSWORD_BCRYPT);

        // Only insert users that the migration doesn't create
        // Migration creates: scheduler, operator, quality_checker, production
        // We add: admin, digitizer, programmer, accountant
        $users = [
            ['id' => 1, 'name' => 'Administrator', 'email' => 'admin@stitchcraft.com',       'role' => 'admin'],
            ['id' => 6, 'name' => 'Programmer',    'email' => 'programmer@stitchcraft.com',  'role' => 'programmer'],
            ['id' => 7, 'name' => 'Accountant',    'email' => 'accountant@stitchcraft.com',  'role' => 'accountant'],
            ['id' => 3, 'name' => 'Digitizer One', 'email' => 'digitizer1@stitchcraft.com',  'role' => 'digitizer'],
        ];

        foreach ($users as $u) {
            // Check by ID (primary key) - no parameter binding for fetchAll
            $exists = $this->fetchAll("SELECT id FROM users WHERE id = {$u['id']}");
            if (empty($exists)) {
                $this->execute(
                    "INSERT INTO users (id, name, email, password, role, organization_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$u['id'], $u['name'], $u['email'], $hash, $u['role'], 1, $now]
                );
            }
        }

        // Reset auto-increment/sequences for both MySQL and PostgreSQL
        $this->resetAutoIncrement('organizations', $driver);
        $this->resetAutoIncrement('users', $driver);

        $this->getIo()?->out('Seeded additional users. Default password: ' . $passwordPlain);
    }

    /**
     * Reset auto-increment/sequence to max(id)+1 for the given table.
     * Works on MySQL (AUTO_INCREMENT) and PostgreSQL (SEQUENCE).
     */
    private function resetAutoIncrement(string $table, $driver): void
    {
        try {
            if ($driver instanceof Postgres) {
                // PostgreSQL: reset sequence
                $seq = $this->fetchOne(
                    "SELECT pg_get_serial_sequence('{$table}', 'id')"
                )['pg_get_serial_sequence'] ?? null;
                if ($seq) {
                    $maxId = $this->fetchOne("SELECT COALESCE(MAX(id), 0) FROM {$table}")['max'] ?? 0;
                    $this->execute("SELECT setval(?, ?)", [$seq, $maxId + 1]);
                }
            } elseif ($driver instanceof Mysql) {
                // MySQL: reset AUTO_INCREMENT
                $maxId = $this->fetchOne("SELECT COALESCE(MAX(id), 0) FROM {$table}")['max'] ?? 0;
                $this->execute("ALTER TABLE {$table} AUTO_INCREMENT = ?", [$maxId + 1]);
            }
        } catch (\Throwable $e) {
            // Silently ignore if not supported
        }
    }
}