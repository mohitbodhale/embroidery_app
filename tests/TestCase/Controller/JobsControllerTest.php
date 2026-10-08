<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\JobsController;
use Cake\Http\ServerRequest;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\JobsController Test Case
 *
 * @link \App\Controller\JobsController
 */
class JobsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Organizations',
        'app.Roles',
        'app.Users',
        'app.JobStatuses',
        'app.Jobs',
        'app.JobCounters',
        'app.JobAttachments',
        'app.JobLogs',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $operator = TableRegistry::getTableLocator()->get('Users')->get(4);
        $this->session(['Auth' => $operator]);
        $this->enableCsrfToken();
        TableRegistry::getTableLocator()->get('Jobs')->updateAll(['operator_id' => 4], ['id' => 1]);
    }

    public function testOperatorCanViewAndEditAssignedJob(): void
    {
        $this->get('/jobs/view/1');
        $this->assertResponseOk();
        $this->assertResponseContains('Floral design on polo');
        $this->assertResponseContains('/job-attachments/download/1');
        $this->assertResponseNotContains('/uploads/attachments/1/abc123.emb');

        $this->get('/jobs/edit/1');
        $this->assertResponseOk();
        $this->assertResponseContains('Upload Files');
        $this->assertResponseNotContains('Return to Scheduler');
    }

    public function testSchedulerCanDeleteOwnJobButNotAnotherSchedulersJob(): void
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $users->updateAll(['role' => 'scheduler', 'role_id' => 20], ['id' => 2]);
        $this->session(['Auth' => $users->get(2)]);
        $jobs = TableRegistry::getTableLocator()->get('Jobs');
        $jobs->updateAll(['created_by' => 2, 'status' => 'draft'], ['id' => 1]);

        $this->post('/jobs/delete/2');
        $this->assertResponseCode(403);
        $this->assertTrue($jobs->exists(['id' => 2]));

        $this->post('/jobs/delete/1');
        $this->assertResponseCode(302);
        $this->assertFalse($jobs->exists(['id' => 1]));
    }

    public function testOperatorQueueContainsOnlyAssignedJobs(): void
    {
        $this->get('/jobs');

        $this->assertResponseOk();
        $this->assertResponseContains('JOB-2026-001');
        $this->assertResponseNotContains('JOB-2026-002');
    }

    public function testOperatorCannotViewOrEditAnotherOperatorsJob(): void
    {
        $this->get('/jobs/view/2');
        $this->assertResponseCode(403);

        $this->get('/jobs/edit/2');
        $this->assertResponseCode(403);

        TableRegistry::getTableLocator()->get('Jobs')->updateAll(['operator_id' => 4], ['id' => 2]);
        $this->get('/jobs/edit/2');
        $this->assertResponseCode(403);
    }

    public function testOperatorCannotUseSchedulerQcOrProductionActions(): void
    {
        $this->get('/jobs/add');
        $this->assertResponseCode(403);

        foreach ([
            '/jobs/delete/1',
            '/jobs/assign/1',
            '/jobs/approve/1',
            '/jobs/reject/1',
            '/jobs/start-production/1',
            '/jobs/complete/1',
        ] as $url) {
            $this->post($url);
            $this->assertResponseCode(403, $url);
        }
    }

    public function testOperatorEditIgnoresProtectedJobFields(): void
    {
        $this->post('/jobs/edit/1', [
            'job_number' => 'JOB-OP-EDIT',
            'title' => 'Updated by operator',
            'instructions' => 'Updated instructions',
            'status' => 'completed',
            'operator_id' => 3,
            'qc_id' => null,
            'organization_id' => 2,
            'created_by' => 3,
            'level_id' => 99,
        ]);

        $job = TableRegistry::getTableLocator()->get('Jobs')->get(1);
        $this->assertSame('Updated by operator', $job->title);
        $this->assertSame('Updated instructions', $job->instructions);
        $this->assertSame('in_progress', $job->status);
        $this->assertSame(4, (int)$job->operator_id);
        $this->assertSame(3, (int)$job->qc_id);
        $this->assertSame(1, (int)$job->organization_id);
        $this->assertSame(1, (int)$job->created_by);
        $this->assertNotSame(99, (int)$job->level_id);
    }

    public function testAdminEditCannotBypassWorkflowOrChangeJobOwnership(): void
    {
        $admin = TableRegistry::getTableLocator()->get('Users')->get(1);
        $this->session(['Auth' => $admin]);
        $this->post('/jobs/edit/1', [
            'title' => 'Admin edited title',
            'status' => 'completed',
            'operator_id' => 3,
            'qc_id' => null,
            'organization_id' => 2,
            'created_by' => 3,
        ]);

        $job = TableRegistry::getTableLocator()->get('Jobs')->get(1);
        $this->assertSame('Admin edited title', $job->title);
        $this->assertSame('in_progress', $job->status);
        $this->assertSame(4, (int)$job->operator_id);
        $this->assertSame(3, (int)$job->qc_id);
        $this->assertSame(1, (int)$job->organization_id);
        $this->assertSame(1, (int)$job->created_by);
    }

    public function testAssigneesMustMatchJobRoleAndOrganization(): void
    {
        $controller = new JobsController(new ServerRequest());
        $method = new \ReflectionMethod($controller, 'isValidAssignee');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($controller, 4, 'operator', 1));
        $this->assertFalse($method->invoke($controller, 4, 'operator', 2));
        $this->assertFalse($method->invoke($controller, 3, 'quality_checker', 2));
        $this->assertFalse($method->invoke($controller, 3, 'operator', 1));
        $this->assertTrue($method->invoke($controller, null, 'operator', 1));
    }

    public function testAdminAndSchedulerCreateJobsForDifferentOperators(): void
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $users->getConnection()->execute(
            'INSERT INTO users (name, email, password, role, organization_id, role_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)',
            ['Second Test Operator', 'second-operator@example.test', 'test-password-hash', 'operator', 1, null, '2025-01-01 00:00:00']
        );
        $secondOperator = $users->find()
            ->where(['email' => 'second-operator@example.test'])
            ->firstOrFail();

        $jobs = TableRegistry::getTableLocator()->get('Jobs');
        $initialJobCount = $jobs->find()->count();
        $admin = $users->get(1);
        $this->session(['Auth' => $admin]);
        $this->post('/jobs/add', [
            'title' => 'Admin created test job',
            'organization_id' => 1,
            'operator_id' => 4,
            'qc_id' => 3,
        ]);
        $this->assertResponseCode(302);

        $adminJob = $jobs->getConnection()->execute(
            'SELECT id, organization_id, operator_id, qc_id, created_by FROM jobs WHERE title = ?',
            ['Admin created test job']
        )->fetch('assoc');
        $this->assertNotFalse($adminJob);
        $this->assertSame(1, (int)$adminJob['organization_id']);
        $this->assertSame(4, (int)$adminJob['operator_id']);
        $this->assertSame(3, (int)$adminJob['qc_id']);
        $this->assertSame(1, (int)$adminJob['created_by']);

        $scheduler = $users->get(2);
        $scheduler->role = 'scheduler';
        $users->saveOrFail($scheduler);
        $this->session(['Auth' => $scheduler]);
        $this->post('/jobs/add', [
            'title' => 'Scheduler created test job',
            'organization_id' => 1,
            'operator_id' => $secondOperator->id,
            'qc_id' => 3,
        ]);
        $this->assertResponseCode(302);

        $schedulerJob = $jobs->getConnection()->execute(
            'SELECT id, organization_id, operator_id, qc_id, created_by FROM jobs WHERE title = ?',
            ['Scheduler created test job']
        )->fetch('assoc');
        $this->assertNotFalse($schedulerJob);
        $this->assertSame(1, (int)$schedulerJob['organization_id']);
        $this->assertSame((int)$secondOperator->id, (int)$schedulerJob['operator_id']);
        $this->assertSame(3, (int)$schedulerJob['qc_id']);
        $this->assertSame(2, (int)$schedulerJob['created_by']);
        $this->assertNotSame((int)$adminJob['operator_id'], (int)$schedulerJob['operator_id']);
        $this->assertSame($initialJobCount + 2, $jobs->find()->count());
    }

    public function testAssignmentRejectsWrongRoleUsers(): void
    {
        $admin = TableRegistry::getTableLocator()->get('Users')->get(1);
        $this->session(['Auth' => $admin]);
        $this->post('/jobs/assign/1', ['operator_id' => 3]);

        $job = TableRegistry::getTableLocator()->get('Jobs')->get(1);
        $this->assertSame(4, (int)$job->operator_id);
    }

    public function testOperatorCanSubmitOwnJobButCannotSubmitAnotherOperatorsJob(): void
    {
        $this->post('/jobs/submit/1');
        $job = TableRegistry::getTableLocator()->get('Jobs')->get(1);
        $this->assertSame('ready_for_qc', $job->status);

        $this->post('/jobs/submit/2');
        $this->assertResponseCode(403);
    }

    public function testOperatorCannotAddNotesToAnotherOperatorsJobOrReturnJobToScheduler(): void
    {
        $this->post('/jobs/add-log/2', ['comments' => 'Unauthorized note']);
        $this->assertResponseCode(403);

        $this->post('/jobs/return-to-scheduler/1');
        $this->assertResponseCode(403);

        $job = TableRegistry::getTableLocator()->get('Jobs')->get(1);
        $this->assertSame('in_progress', $job->status);
        $this->assertSame(4, (int)$job->operator_id);
    }

    public function testOperatorCanAddNoteToOwnJobAndCannotAccessAnotherJobsFiles(): void
    {
        $this->post('/jobs/add-log/1', ['comments' => 'Operator work note']);
        $this->assertResponseCode(302);
        $this->assertSame(1, TableRegistry::getTableLocator()->get('JobLogs')->find()->where([
            'job_id' => 1,
            'comments' => 'Operator work note',
        ])->count());

        $this->get('/job-attachments/add?job_id=1');
        $this->assertResponseOk();

        $this->get('/job-attachments/add?job_id=2');
        $this->assertResponseCode(403);

        $this->get('/job-attachments/download/1');
        $this->assertResponseCode(404);

        $attachments = TableRegistry::getTableLocator()->get('JobAttachments');
        $attachments->updateAll(['job_id' => 2], ['id' => 2]);
        $foreignAttachment = $attachments->get(2, contain: ['Jobs']);
        $operator = TableRegistry::getTableLocator()->get('Users')->get(4);
        $this->assertSame(2, (int)$foreignAttachment->job_id);
        $this->assertSame(2, (int)$foreignAttachment->job->id);
        $this->assertSame(2, (int)$foreignAttachment->job->operator_id);
        $this->assertFalse((new \App\Policy\JobAttachmentPolicy())->canDownload($operator, $foreignAttachment));
        $this->get('/job-attachments/download/2');
        $this->assertTrue(in_array($this->_response->getStatusCode(), [403, 404], true));
    }

    /**
     * Test index method
     *
     * @return void
     * @link \App\Controller\JobsController::index()
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     * @link \App\Controller\JobsController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @link \App\Controller\JobsController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @link \App\Controller\JobsController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @link \App\Controller\JobsController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
