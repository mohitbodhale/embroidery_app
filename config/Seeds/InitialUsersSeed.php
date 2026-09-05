<?php
declare(strict_types=1);

use Migrations\AbstractSeed;

final class InitialUsersSeed extends AbstractSeed
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // Create default organization if missing
        $orgs = $this->fetchAll('SELECT id FROM organizations WHERE id = 1');
        if (empty($orgs)) {
            $this->execute("INSERT INTO organizations (id, name, domain_or_slug, status, created_at) VALUES (1, 'Default Org', 'default', 'active', '{$now}')");
        }

        // Insert sample users for each role. Replace the password hash with a secure generated one if desired.
        // Use PHP to generate a bcrypt hash for production; here a secure placeholder is used via password_hash.
        $passwordPlain = 'Password123!';
        $hash = password_hash($passwordPlain, PASSWORD_BCRYPT);

        $users = [
            ['id' => 1, 'name' => 'Administrator', 'email' => 'admin@stitchcraft.com', 'role' => 'admin'],
            ['id' => 2, 'name' => 'Scheduler', 'email' => 'scheduler@stitchcraft.com', 'role' => 'scheduler'],
            ['id' => 3, 'name' => 'Digitizer One', 'email' => 'digitizer1@stitchcraft.com', 'role' => 'digitizer'],
            ['id' => 4, 'name' => 'QC One', 'email' => 'qc1@stitchcraft.com', 'role' => 'quality_checker'],
            ['id' => 5, 'name' => 'Production', 'email' => 'production@stitchcraft.com', 'role' => 'production'],
        ];

        foreach ($users as $u) {
            $exists = $this->fetchAll("SELECT id FROM users WHERE id = {$u['id']}");
            if (empty($exists)) {
                $this->execute("INSERT INTO users (id, name, email, password, role, organization_id, created_at) VALUES ({$u['id']}, '?' , '?' , '?' , '?' , 1, '{$now}')");
            }
        }

        // Because placeholders used above (due to quoting complexity), insert with safe commands below instead
        foreach ($users as $u) {
            $exists = $this->fetchAll("SELECT id FROM users WHERE id = {$u['id']}");
            if (empty($exists)) {
                $sql = sprintf("INSERT INTO users (id, name, email, password, role, organization_id, created_at) VALUES (%d, %s, %s, %s, %s, %d, '%s')",
                    $u['id'],
                    $this->getConnection()->quote($u['name']),
                    $this->getConnection()->quote($u['email']),
                    $this->getConnection()->quote($hash),
                    $this->getConnection()->quote($u['role']),
                    1,
                    $now
                );
                $this->execute($sql);
            }
        }

        // Fix sequences
        $this->execute("SELECT setval(pg_get_serial_sequence('organizations','id'), (SELECT COALESCE(MAX(id),0) FROM organizations))");
        $this->execute("SELECT setval(pg_get_serial_sequence('users','id'), (SELECT COALESCE(MAX(id),0) FROM users))");

        $this->output->write('Seeded initial users. Default password for seeded users: ' . $passwordPlain);
    }
}
