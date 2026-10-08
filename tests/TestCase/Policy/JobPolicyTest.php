<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use PHPUnit\Framework\TestCase;
use App\Policy\JobPolicy;
use App\Policy\JobAttachmentPolicy;

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
        $scheduler = (object)['id' => 2, 'role' => 'scheduler'];
        $operator = (object)['id' => 3, 'role' => 'operator'];
        $job = (object)['id' => 100, 'operator_id' => 3, 'status' => 'in_progress'];
        $otherJob = (object)['id' => 101, 'operator_id' => 4, 'status' => 'in_progress'];
        $schedulerDraft = (object)['id' => 102, 'created_by' => 2, 'status' => 'draft'];
        $otherSchedulerDraft = (object)['id' => 103, 'created_by' => 4, 'status' => 'draft'];
        $schedulerActiveJob = (object)['id' => 104, 'created_by' => 2, 'status' => 'in_progress'];

        $this->assertTrue($policy->canEdit($admin, $job));
        $this->assertTrue($policy->canEdit($operator, $job));
        $this->assertFalse($policy->canEdit($operator, $otherJob));
        $this->assertTrue($policy->canEdit($scheduler, $schedulerDraft));
        $this->assertFalse($policy->canEdit($scheduler, $otherSchedulerDraft));
        $this->assertFalse($policy->canEdit($scheduler, $schedulerActiveJob));
    }

    public function testProductionCanViewApprovedInProductionAndCompletedJobs()
    {
        $policy = new JobPolicy();
        $production = (object)['id' => 5, 'role' => 'production'];

        $this->assertTrue($policy->canView($production, (object)['status' => 'qc_approved']));
        $this->assertTrue($policy->canView($production, (object)['status' => 'in_production']));
        $this->assertTrue($policy->canView($production, (object)['status' => 'completed']));
        $this->assertFalse($policy->canView($production, (object)['status' => 'draft']));
    }

    public function testCanApprove()
    {
        $policy = new JobPolicy();
        $qc = (object)['id' => 4, 'role' => 'quality_checker'];
        $this->assertTrue($policy->canApprove($qc, null));
        $admin = (object)['id' => 1, 'role' => 'admin'];
        $this->assertTrue($policy->canApprove($admin, null));
    }

    public function testCanAssign()
    {
        $policy = new JobPolicy();
        $admin = (object)['id' => 1, 'role' => 'admin'];
        $scheduler = (object)['id' => 2, 'role' => 'scheduler'];
        $operator = (object)['id' => 3, 'role' => 'operator'];
        $ownedDraft = (object)['created_by' => 2, 'status' => 'draft'];

        $this->assertTrue($policy->canAssign($admin, null));
        $this->assertTrue($policy->canAssign($scheduler, $ownedDraft));
        $this->assertFalse($policy->canAssign($scheduler, null));
        $this->assertFalse($policy->canAssign($operator, null));
    }

    public function testAdminCanPerformAllWorkflowActions(): void
    {
        $policy = new JobPolicy();
        $admin = (object)['id' => 1, 'role' => 'admin'];
        $job = (object)['id' => 10, 'operator_id' => 3, 'qc_id' => 4, 'status' => 'in_progress'];

        $this->assertTrue($policy->canSubmit($admin, $job));
        $this->assertTrue($policy->canApprove($admin, $job));
        $this->assertTrue($policy->canProduce($admin, $job));
    }

    public function testOperatorAttachmentChangesAreLimitedToActiveWork(): void
    {
        $policy = new JobAttachmentPolicy();
        $operator = (object)['id' => 3, 'role' => 'operator'];
        $activeJob = (object)['operator_id' => 3, 'status' => 'in_progress'];
        $reworkJob = (object)['operator_id' => 3, 'status' => 'qc_rejected'];
        $submittedJob = (object)['operator_id' => 3, 'status' => 'ready_for_qc'];

        $this->assertTrue($policy->canAdd($operator, $activeJob));
        $this->assertTrue($policy->canAdd($operator, $reworkJob));
        $this->assertFalse($policy->canAdd($operator, $submittedJob));
        $this->assertFalse($policy->canAdd($operator, (object)['operator_id' => 4, 'status' => 'in_progress']));
    }

    public function testAttachmentPolicyScopesSchedulersAndReviewersToTheirJobs(): void
    {
        $policy = new JobAttachmentPolicy();
        $scheduler = (object)['id' => 2, 'role' => 'scheduler'];
        $qc = (object)['id' => 4, 'role' => 'quality_checker'];
        $ownJob = (object)['created_by' => 2, 'qc_id' => 4, 'status' => 'ready_for_qc'];
        $otherJob = (object)['created_by' => 3, 'qc_id' => 4, 'status' => 'ready_for_qc'];
        $ownAttachment = (object)['uploaded_by' => 4, 'job' => $ownJob];

        $this->assertTrue($policy->canView($scheduler, (object)['job' => $ownJob]));
        $this->assertFalse($policy->canView($scheduler, (object)['job' => $otherJob]));
        $this->assertTrue($policy->canAdd($qc, $ownJob));
        $this->assertFalse($policy->canAdd($qc, (object)['qc_id' => 4, 'status' => 'in_progress']));
        $this->assertTrue($policy->canEdit($qc, $ownAttachment));
        $this->assertFalse($policy->canEdit($qc, (object)[
            'uploaded_by' => 4,
            'job' => (object)['qc_id' => 4, 'status' => 'qc_approved'],
        ]));
    }

    public function testAttachmentDeletionIsUploaderOnly(): void
    {
        $policy = new JobAttachmentPolicy();
        $admin = (object)['id' => 1, 'role' => 'admin'];
        $uploader = (object)['id' => 3, 'role' => 'operator'];
        $attachment = (object)['uploaded_by' => 3];

        $this->assertTrue($policy->canDelete($uploader, $attachment));
        $this->assertFalse($policy->canDelete($admin, $attachment));
    }
}
