<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\JobAttachmentsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\JobAttachmentsTable Test Case
 */
class JobAttachmentsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\JobAttachmentsTable
     */
    protected $JobAttachments;

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
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('JobAttachments') ? [] : ['className' => JobAttachmentsTable::class];
        $this->JobAttachments = $this->getTableLocator()->get('JobAttachments', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->JobAttachments);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\JobAttachmentsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\JobAttachmentsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
