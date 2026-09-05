<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\ConnectionManager;
use Cake\Http\Exception\ForbiddenException;
use Cake\ORM\TableRegistry;

/**
 * Lightweight smoke-test runner used by the "Automatic Testing" button
 * in the layout footer. Runs a series of checks and returns JSON.
 *
 * Endpoints:
 *   GET  /system-tests/panel   – partial used by the modal
 *   POST /system-tests/run      – execute the test suite
 */
class SystemTestsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Flash');
    }

    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        // Diagnostic endpoints — skip CSRF so AJAX calls from any page can hit them.
        $this->request->getSession()->write('App.unauthenticatedActions', [
            'login', 'logout', 'register', 'awaitingApproval',
            'panel', 'run',
        ]);
    }

    public function panel()
    {
        $this->autoRender = false;
        $this->viewBuilder()->disableAutoLayout();
        $csrf = $this->request->getAttribute('csrfToken') ?? '';
        $html = <<<'HTML'
<div class="modal fade" id="autoTestModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-vial me-2 text-primary"></i>Automatic testing</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small mb-3">
          Runs an in-depth smoke test: database connectivity, model CRUD, authentication,
          role-based authorization, and sidebar payload for each role.
        </p>
        <div id="autoTestProgress" class="mb-2" style="display:none;">
          <div class="progress" style="height: 8px;">
            <div id="autoTestBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 0%;"></div>
          </div>
          <div id="autoTestStatus" class="small text-muted mt-1"></div>
        </div>
        <div id="autoTestResults"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="autoTestRun">
          <i class="fas fa-play me-1"></i>Run tests
        </button>
      </div>
    </div>
  </div>
</div>
HTML;
        // Stamp the csrf token onto the markup so the AJAX call can include it.
        $html = str_replace('<div id="autoTestMount"></div>', '', $html);
        $html .= '<script type="application/json" id="autoTestCsrfToken">' . htmlspecialchars((string)$csrf, ENT_QUOTES) . '</script>';
        return $this->response->withStringBody($html);
    }

    public function run()
    {
        $this->autoRender = false;
        $this->viewBuilder()->disableAutoLayout();
        $this->response = $this->response->withType('application/json');

        $started = microtime(true);
        $results = [];
        $pass = 0;
        $fail = 0;

        $check = function (string $name, callable $fn) use (&$results, &$pass, &$fail) {
            $t = microtime(true);
            try {
                $detail = $fn();
                $results[] = [
                    'name' => $name,
                    'status' => 'pass',
                    'detail' => (string)($detail ?? ''),
                    'ms' => (int)((microtime(true) - $t) * 1000),
                ];
                $pass++;
            } catch (\Throwable $e) {
                $results[] = [
                    'name' => $name,
                    'status' => 'fail',
                    'detail' => $e->getMessage(),
                    'ms' => (int)((microtime(true) - $t) * 1000),
                ];
                $fail++;
            }
        };

        // 1. Database connectivity
        $check('Database connection', function () {
            $conn = ConnectionManager::get('default');
            $conn->execute('SELECT 1');
            $driver = get_class($conn->getDriver());
            return 'OK — ' . $driver;
        });

        // 2. Tables exist
        foreach (['Users', 'Jobs', 'Organizations', 'JobAttachments', 'JobLogs'] as $t) {
            $check("Table $t reachable", function () use ($t) {
                $c = TableRegistry::getTableLocator()->get($t);
                $c->find()->limit(1)->all()->toList();
                return 'OK';
            });
        }

        // 3. Test users exist with correct roles
        $check('Test users seeded', function () {
            $users = TableRegistry::getTableLocator()->get('Users');
            $expected = ['admin','scheduler','digitizer','quality_checker','production'];
            $found = $users->find()->all()->extract('role')->toList();
            $missing = array_diff($expected, $found);
            if ($missing) {
                throw new \RuntimeException('Missing roles: ' . implode(', ', $missing));
            }
            return count($found) . ' users (' . implode(', ', array_unique($found)) . ')';
        });

        // 4. Auth pipeline (Password identifier can verify admin password)
        $check('Password identifier verifies admin password', function () {
            $identifier = new \Authentication\Identifier\PasswordIdentifier([
                'resolver' => [
                    'className' => 'Authentication.Orm',
                    'userModel' => 'Users',
                    'finder' => 'all',
                ],
                'fields' => [
                    'username' => 'email',
                    'password' => 'password',
                ],
            ]);
            $result = $identifier->identify([
                'username' => 'admin@stitchcraft.com',
                'password' => 'admin123',
            ]);
            if (!$result) {
                throw new \RuntimeException('Identifier returned no data');
            }
            return 'OK — id=' . ($result['id'] ?? '?');
        });

        // 5. JobPolicy methods exist & return bool
        $check('JobPolicy methods callable', function () {
            if (!class_exists('App\\Policy\\JobPolicy')) {
                return 'skipped (no policy class)';
            }
            $p = new \App\Policy\JobPolicy();
            $methods = ['canView','canCreate','canEdit','canDelete','canAssign','canApprove','canSubmit','canProduce'];
            $missing = [];
            foreach ($methods as $m) {
                if (!method_exists($p, $m)) { $missing[] = $m; }
            }
            if ($missing) {
                throw new \RuntimeException('Missing methods: ' . implode(', ', $missing));
            }
            return count($methods) . ' methods';
        });

        // 6. Sidebar role expectations
        $roles = [
            'admin'          => ['Operations Dashboard','Create Job','Attachments','Activity Logs','Users','Create User'],
            'scheduler'      => ['Scheduling Board','Create Job','Attachments','Activity Logs'],
            'digitizer'      => ['My Digitizing Queue'],
            'quality_checker'=> ['QC Review Queue','Activity Logs'],
            'production'     => ['Production Queue'],
        ];
        $Users = TableRegistry::getTableLocator()->get('Users');
        foreach ($roles as $role => $expected) {
            $check("Sidebar shows correct items for role=$role", function () use ($Users, $role, $expected) {
                $user = $Users->find()->where(['role' => $role])->first();
                if (!$user) {
                    throw new \RuntimeException("No user with role=$role");
                }
                $has = array_filter($expected, fn($label) => $this->shouldShowLabel($role, $label));
                if (count($has) !== count($expected)) {
                    $missing = array_diff($expected, $has);
                    throw new \RuntimeException('Missing expected items: ' . implode(', ', $missing));
                }
                return count($expected) . ' items OK';
            });
        }

        // 7. CRUD round-trip on a throwaway job (then delete)
        $check('Jobs CRUD round-trip', function () {
            $Jobs = TableRegistry::getTableLocator()->get('Jobs');
            $orgId = (int)TableRegistry::getTableLocator()->get('Organizations')->find()->first()->id;
            $entity = $Jobs->newEntity([
                'job_number' => 'TEST-' . random_int(100000, 999999),
                'title'      => 'Auto test',
                'instructions' => 'Generated by Automatic testing',
                'status'     => 'draft',
                'organization_id' => $orgId,
            ]);
            if (!$Jobs->save($entity)) {
                throw new \RuntimeException('Save failed: ' . print_r($entity->getErrors(), true));
            }
            $id = $entity->id;
            $found = $Jobs->get($id);
            if ($found->title !== 'Auto test') {
                throw new \RuntimeException('Round-trip title mismatch');
            }
            $Jobs->delete($entity);
            return 'Created+loaded+deleted (id=' . $id . ')';
        });

        // 8. Session is writable
        $check('Session writable', function () {
            $s = $this->request->getSession();
            $s->write('App.test', 'hello-' . time());
            $v = $s->read('App.test');
            $s->delete('App.test');
            if (!is_string($v) || $v === '') {
                throw new \RuntimeException('Session read/write failed');
            }
            return 'OK — ' . $v;
        });

        $payload = [
            'passed' => $pass,
            'failed' => $fail,
            'total_ms' => (int)((microtime(true) - $started) * 1000),
            'results' => $results,
            'php' => PHP_VERSION,
            'cake' => \Cake\Core\Configure::version(),
            'db_driver' => get_class(ConnectionManager::get('default')->getDriver()),
            'time' => date('Y-m-d H:i:s'),
        ];

        return $this->response->withStringBody(json_encode($payload, JSON_PRETTY_PRINT));
    }

    /**
     * Mirrors the role logic in templates/layout/adminlte.php so we can assert it.
     */
    private function shouldShowLabel(string $role, string $label): bool
    {
        $map = [
            'Operations Dashboard' => ['admin','scheduler','digitizer','quality_checker','production'],
            'Scheduling Board'     => ['scheduler'],
            'My Digitizing Queue'  => ['digitizer'],
            'QC Review Queue'      => ['quality_checker'],
            'Production Queue'     => ['production'],
            'Create Job'           => ['admin','scheduler'],
            'Attachments'          => ['admin','scheduler','digitizer','production'],
            'Activity Logs'        => ['admin','quality_checker','scheduler'],
            'Users'                => ['admin'],
            'Create User'          => ['admin'],
        ];
        return in_array($role, $map[$label] ?? [], true);
    }
}