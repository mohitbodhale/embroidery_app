<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\View\Exception\MissingTemplateException;

/**
 * Static content controller
 *
 * This controller will render views from templates/Pages/
 *
 * @link https://book.cakephp.org/4/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{
    /**
     * Displays a view
     *
     * @param string ...$path Path segments.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\View\Exception\MissingTemplateException When the view file could not
     *   be found and in debug mode.
     * @throws \Cake\Http\Exception\NotFoundException When the view file could not
     *   be found and not in debug mode.
     * @throws \Cake\View\Exception\MissingTemplateException In debug mode.
     */
    public function display(string ...$path): ?Response
    {
        if (!$path) {
            return $this->redirect('/');
        }
        if (in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }
        $this->set(compact('page', 'subpage'));

        try {
            return $this->render(implode('/', $path));
        } catch (MissingTemplateException $exception) {
            if (Configure::read('debug')) {
                throw $exception;
            }
            throw new NotFoundException();
        }
    }

    /**
     * Role-aware landing page shown at "/".
     * Shows a different dashboard per role (admin / scheduler / digitizer / QC / production).
     */
    public function dashboard()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }
        $role = strtolower((string)($user->role ?? ''));
        if ($role === 'admin') {
            return $this->redirect(['action' => 'adminDashboard']);
        }
        $this->viewBuilder()->setTemplate('dashboard');

        $role = strtolower((string)($user->role ?? ''));
        $Jobs = $this->fetchTable('Jobs');

        $counts = [
            'total'      => $Jobs->find()->count(),
            'draft'      => $Jobs->find()->where(['Jobs.status' => 'draft'])->count(),
            'digitizing' => $Jobs->find()->where(['Jobs.status' => 'in_digitizing'])->count(),
            'qc'         => $Jobs->find()->where(['Jobs.status IN' => ['digitized','qc_rejected']])->count(),
            'production' => $Jobs->find()->where(['Jobs.status IN' => ['qc_approved','in_production']])->count(),
            'completed'  => $Jobs->find()->where(['Jobs.status' => 'completed'])->count(),
        ];

        $myCount = 0;
        if ($role === 'digitizer') {
            $myCount = $Jobs->find()->where(['Jobs.digitizer_id' => $user->id, 'Jobs.status' => 'in_digitizing'])->count();
        } elseif ($role === 'quality_checker') {
            $myCount = $Jobs->find()->where(['Jobs.status IN' => ['digitized','qc_rejected']])->count();
        } elseif ($role === 'production') {
            $myCount = $Jobs->find()->where(['Jobs.status IN' => ['qc_approved','in_production']])->count();
        } elseif ($role === 'scheduler') {
            $myCount = $Jobs->find()->where(['Jobs.scheduled_date IS NOT NULL', 'Jobs.status NOT IN' => ['completed']])->count();
        }

        $recent = $Jobs->find()
            ->contain(['Organizations'])
            ->orderByDesc('Jobs.created_at')
            ->limit(8)
            ->all();

        $this->set(compact('role', 'counts', 'myCount', 'recent', 'user'));
    }

    /**
     * Admin-only operations dashboard.
     * Reports across organizations: user workload, job distribution, status mix,
     * recent activity. No job CRUD — admin only manages people, roles, and masters.
     */
    public function adminDashboard()
    {
        $this->requireRole(['admin']);
        $this->viewBuilder()->setTemplate('admin_dashboard');

        $Users = $this->fetchTable('Users');
        $Jobs = $this->fetchTable('Jobs');
        $JobLogs = $this->fetchTable('JobLogs');
        $JobAttachments = $this->fetchTable('JobAttachments');

        $orgs = $this->fetchTable('Organizations')->find('list')->all()->toList();

        // --- KPIs ---
        $kpi = [
            'users_total'   => $Users->find()->count(),
            'users_active'  => $Users->find()->where(['Users.role !=' => 'pending'])->count(),
            'jobs_total'    => $Jobs->find()->count(),
            'jobs_open'     => $Jobs->find()->where(['Jobs.status NOT IN' => ['completed']])->count(),
            'jobs_done'     => $Jobs->find()->where(['Jobs.status' => 'completed'])->count(),
            'logs_total'    => $JobLogs->find()->count(),
            'attachments'   => $JobAttachments->find()->count(),
        ];

        // --- Users by role (driven by master table) ---
        $usersByRole = $this->fetchTable('Roles')->find()
            ->leftJoinWith('Users')
            ->select([
                'role_id'   => 'Roles.id',
                'name'      => 'Roles.name',
                'label'     => 'Roles.label',
                'color'     => 'Roles.color',
                'user_count'=> $Users->find()->func()->count('Users.id'),
            ])
            ->groupBy(['Roles.id', 'Roles.name', 'Roles.label', 'Roles.color', 'Roles.sort_order'])
            ->orderBy(['Roles.sort_order' => 'ASC'])
            ->all()
            ->toList();

        // --- Jobs by status (driven by master table) ---
        $jobsByStatus = $this->fetchTable('JobStatuses')->find()
            ->leftJoinWith('Jobs')
            ->select([
                'status_id'  => 'JobStatuses.id',
                'name'       => 'JobStatuses.name',
                'label'      => 'JobStatuses.label',
                'color'      => 'JobStatuses.color',
                'is_terminal'=> 'JobStatuses.is_terminal',
                'job_count'  => $Jobs->find()->func()->count('Jobs.id'),
            ])
            ->groupBy(['JobStatuses.id', 'JobStatuses.name', 'JobStatuses.label', 'JobStatuses.color', 'JobStatuses.is_terminal', 'JobStatuses.sort_order'])
            ->orderBy(['JobStatuses.sort_order' => 'ASC'])
            ->all()
            ->toList();

        // --- Per-user workload (jobs created, jobs assigned as digitizer/qc) ---
        $conn = \Cake\Datasource\ConnectionManager::get('default');
        $workload = $conn->execute("
            SELECT u.id, u.name, u.email, r.name AS role_name, r.label AS role_label, r.color AS role_color,
                   ud.avatar,
                   (SELECT COUNT(*) FROM jobs WHERE created_by = u.id) AS jobs_created,
                   (SELECT COUNT(*) FROM jobs WHERE digitizer_id = u.id) AS jobs_digitizing,
                   (SELECT COUNT(*) FROM jobs WHERE qc_id = u.id) AS jobs_qc,
                   (SELECT COUNT(*) FROM job_logs WHERE user_id = u.id) AS log_entries,
                   ((SELECT COUNT(*) FROM jobs WHERE created_by = u.id) +
                    (SELECT COUNT(*) FROM jobs WHERE digitizer_id = u.id) +
                    (SELECT COUNT(*) FROM jobs WHERE qc_id = u.id)) AS total_workload
            FROM users u
            LEFT JOIN roles r ON r.id = u.role_id
            LEFT JOIN user_details ud ON ud.user_id = u.id
            ORDER BY total_workload DESC, u.name
        ")->fetchAll('assoc');

        // --- Recent activity (last 10 logs) ---
        $recentLogs = $JobLogs->find()
            ->contain(['Jobs', 'Users'])
            ->orderByDesc('JobLogs.created_at')
            ->limit(10)
            ->all();

        $this->set(compact('kpi', 'usersByRole', 'jobsByStatus', 'workload', 'recentLogs', 'orgs'));
    }
}
