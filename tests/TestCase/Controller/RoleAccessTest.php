<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class RoleAccessTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Organizations',
        'app.Roles',
        'app.Users',
        'app.JobStatuses',
        'app.Jobs',
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

    public function testNonAdminCannotManageWorkTypes(): void
    {
        $this->get('/work-types/add');
        $this->assertResponseCode(403);
    }

    public function testNonAdminCannotEditAuditLogs(): void
    {
        $this->get('/job-logs/edit/1');
        $this->assertResponseCode(403);
    }

    public function testUsersCannotReadLogsForJobsTheyCannotView(): void
    {
        $this->get('/job-logs/view/2');
        $this->assertContains($this->_response->getStatusCode(), [403, 404]);
    }

    public function testNonAdminCannotOpenSystemTestPanel(): void
    {
        $this->get('/system-tests/panel');
        $this->assertResponseCode(403);
    }

    public function testAdminCanChangeAnotherUsersRole(): void
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $this->session(['Auth' => $users->get(1)]);
        $this->post('/users/assign-role/2', [
            'email' => 'digitizer@stitchcraft.com',
            'role' => 'scheduler',
        ]);

        $this->assertResponseCode(302);
        $user = $users->get(2);
        $this->assertSame('scheduler', $user->role);
        $this->assertSame(20, (int)$user->role_id);
    }

    public function testAdminCanChangeRoleWhileEditingUserDetails(): void
    {
        $users = TableRegistry::getTableLocator()->get('Users');
        $this->session(['Auth' => $users->get(1)]);
        $this->post('/users/edit/2', [
            'name' => 'Jane Digitizer',
            'email' => 'digitizer@stitchcraft.com',
            'role' => 'scheduler',
            'organization_id' => 1,
            'password' => '',
            'password_confirm' => '',
        ]);

        $this->assertResponseCode(302);
        $user = $users->get(2);
        $this->assertSame('scheduler', $user->role);
        $this->assertSame(20, (int)$user->role_id);
    }
}
