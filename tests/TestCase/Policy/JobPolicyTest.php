<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use PHPUnit\Framework\TestCase;
use App\Policy\JobPolicy;

class JobPolicyTest extends TestCase
{
    public function testCanCreate()
    {
        $policy = new JobPolicy();
        $admin = (object)['id' => 1, 'role' => 'admin'];
        $scheduler = (object)['id' => 2, 'role' => 'scheduler'];
        $digitizer = (object)['id' => 3, 'role' => 'digitizer'];

        $this->assertTrue($policy->canCreate($admin));
        $this->assertTrue($policy->canCreate($scheduler));
        $this->assertFalse($policy->canCreate($digitizer));
    }

    public function testCanEditRules()
    {
        $policy = new JobPolicy();
        $admin = (object)['id' => 1, 'role' => 'admin'];
        $digitizer = (object)['id' => 3, 'role' => 'digitizer'];
        $job = (object)['id' => 100, 'digitizer_id' => 3];
        $otherJob = (object)['id' => 101, 'digitizer_id' => 4];

        $this->assertTrue($policy->canEdit($admin, $job));
        $this->assertTrue($policy->canEdit($digitizer, $job));
        $this->assertFalse($policy->canEdit($digitizer, $otherJob));
    }

    public function testCanApprove()
    {
        $policy = new JobPolicy();
        $qc = (object)['id' => 4, 'role' => 'quality_checker'];
        $this->assertTrue($policy->canApprove($qc, null));
        $admin = (object)['id' => 1, 'role' => 'admin'];
        $this->assertFalse($policy->canApprove($admin, null));
    }

    public function testCanAssign()
    {
        $policy = new JobPolicy();
        $admin = (object)['id' => 1, 'role' => 'admin'];
        $scheduler = (object)['id' => 2, 'role' => 'scheduler'];
        $digitizer = (object)['id' => 3, 'role' => 'digitizer'];

        $this->assertTrue($policy->canAssign($admin, null));
        $this->assertTrue($policy->canAssign($scheduler, null));
        $this->assertFalse($policy->canAssign($digitizer, null));
    }
}
