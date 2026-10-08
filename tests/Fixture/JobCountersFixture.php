<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class JobCountersFixture extends TestFixture
{
    public function init(): void
    {
        $this->records = [
            ['id' => 1, 'last_number' => 0],
        ];
        parent::init();
    }
}