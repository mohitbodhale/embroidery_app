<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\JobAttachmentsController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\JobAttachmentsController Test Case
 *
 * @link \App\Controller\JobAttachmentsController
 */
class JobAttachmentsControllerTest extends TestCase
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
        'app.JobAttachments',
        'app.JobLogs',
    ];

    /**
     * Test index method
     *
     * @return void
     * @link \App\Controller\JobAttachmentsController::index()
     */
    public function testIndex(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test view method
     *
     * @return void
     * @link \App\Controller\JobAttachmentsController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @link \App\Controller\JobAttachmentsController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @link \App\Controller\JobAttachmentsController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @link \App\Controller\JobAttachmentsController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
