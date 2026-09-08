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
        $operator = (object)['id' => 3, 'role' => 'operator'];

        $this->assertTrue($policy->canCreate($admin));
        $this->assertTrue($policy->canCreate($scheduler));
        $this->assertFalse($policy->canCreate($operator));
    }

    public function testCanEditRules()
    {
        $policy = new JobPolicy();
        $admin = (object)['id' => 1, 'role' => 'admin'];
        $operator = (object)['id' => 3, 'role' => 'operator'];
        $job = (object)['id' => 100, 'operator_id' => 3];
        $otherJob = (object)['id' => 101, 'operator_id' => 4];

        $this->assertTrue($policy->canEdit($admin, $job));
        $this->assertTrue($policy->canEdit($operator, $job));
        $this->assertFalse($policy->canEdit($operator, $otherJob));
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
        $operator = (object)['id' => 3, 'role' => 'operator'];

        $this->assertTrue($policy->canAssign($admin, null));
        $this->assertTrue($policy->canAssign($scheduler, null));
        $this->assertFalse($policy->canAssign($operator, null));
    }
}
