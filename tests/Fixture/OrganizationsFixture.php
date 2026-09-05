<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class OrganizationsFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Default Org',
                'domain_or_slug' => 'default',
                'status' => 'active',
                'created_at' => '2025-01-01 00:00:00',
            ],
        ];
        parent::init();
    }
}
