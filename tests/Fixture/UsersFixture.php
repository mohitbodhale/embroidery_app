<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Administrator',
                'email' => 'admin@stitchcraft.com',
                'password' => '$2y$10$N9qo8uLOickgx2ZMRZoMy...',
                'role' => 'admin',
                'organization_id' => 1,
                'role_id' => 10,
                'created_at' => '2025-01-01 00:00:00',
            ],
            [
                'id' => 2,
                'name' => 'Jane Digitizer',
                'email' => 'digitizer@stitchcraft.com',
                'password' => '$2y$10$N9qo8uLOickgx2ZMRZoMy...',
                'role' => 'digitizer',
                'organization_id' => 1,
                'role_id' => 30,
                'created_at' => '2025-01-01 00:00:00',
            ],
            [
                'id' => 3,
                'name' => 'Bob Qualitychecker',
                'email' => 'qc@stitchcraft.com',
                'password' => '$2y$10$N9qo8uLOickgx2ZMRZoMy...',
                'role' => 'quality_checker',
                'organization_id' => 1,
                'role_id' => 40,
                'created_at' => '2025-01-01 00:00:00',
            ],
        ];
        parent::init();
    }
}
