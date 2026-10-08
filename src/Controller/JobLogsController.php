<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * JobLogs Controller
 *
 * @property \App\Model\Table\JobLogsTable $JobLogs
 */
class JobLogsController extends AppController
{
    public function index()
    {
        $this->requireRole(['admin', 'scheduler', 'operator', 'quality_checker', 'production']);
        $user = $this->getCurrentUser();
        $role = $this->normalizedRole($user);
        $query = $this->JobLogs->find('all')
            ->contain(['Jobs', 'Users']);
        if ($role === 'scheduler') {
            $query->where(['Jobs.created_by' => $user->id]);
        } elseif ($role === 'operator') {
            $query->where(['Jobs.operator_id' => $user->id]);
        } elseif ($role === 'quality_checker') {
            $query->where(['Jobs.qc_id' => $user->id]);
        } elseif ($role === 'production') {
            $query->where(['Jobs.status IN' => ['qc_approved', 'in_production']]);
        }
        $jobLogs = $this->paginate($query);
        $this->set(compact('jobLogs'));
    }

    public function view($id = null)
    {
        $this->requireRole(['admin', 'scheduler', 'operator', 'quality_checker', 'production']);
        $jobLog = $this->JobLogs->get($id, [
            'contain' => ['Jobs', 'Users']
        ]);
        if (!$this->authorizeAction($jobLog->job, 'view')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to view this log entry.'));
        }
        $this->set(compact('jobLog'));
    }

    public function add()
    {
        $this->requireRole(['admin']);
        $jobLog = $this->JobLogs->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['user_id'] = $this->getCurrentUser()?->id;
            $jobLog = $this->JobLogs->patchEntity($jobLog, $data);
            if ($this->JobLogs->save($jobLog)) {
                $this->Flash->success(__('Log entry added.'));
                $target = $this->request->getQuery('redirect') ?? ['action' => 'index'];
                return $this->redirect($target);
            }
            $this->Flash->error(__('Could not save log entry.'));
        }
        $jobs = $this->JobLogs->Jobs->find('list', limit: 200)->all();
        $this->set(compact('jobLog', 'jobs'));
    }

    public function edit($id = null)
    {
        $this->requireRole(['admin']);
        $jobLog = $this->JobLogs->get($id, ['contain' => []]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $jobLog = $this->JobLogs->patchEntity($jobLog, $this->request->getData());
            if ($this->JobLogs->save($jobLog)) {
                $this->Flash->success(__('Log entry updated.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Could not save log entry.'));
        }
        $jobs = $this->JobLogs->Jobs->find('list', limit: 200)->all();
        $this->set(compact('jobLog', 'jobs'));
    }

    public function delete($id = null)
    {
        $this->requireRole(['admin']);
        $this->request->allowMethod(['post', 'delete']);
        $jobLog = $this->JobLogs->get($id);
        $jobId = $jobLog->job_id;
        if ($this->JobLogs->delete($jobLog)) {
            $this->Flash->success(__('Log entry deleted.'));
        } else {
            $this->Flash->error(__('Could not delete log entry.'));
        }
        return $this->redirect(['controller' => 'Jobs', 'action' => 'view', $jobId]);
    }
}
