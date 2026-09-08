<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Jobs Controller
 *
 * @property \App\Model\Table\JobsTable $Jobs
 */
class JobsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index() 
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            // Not logged in - redirect to login page
            $this->Flash->error(__('Please login to access jobs.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $role = $this->normalizedRole($user);
        $userId = $user->id ?? null;
        if (!in_array($role, ['admin', 'scheduler', 'operator', 'quality_checker', 'production'], true)) {
            return $this->redirect(['controller' => 'Users', 'action' => 'awaitingApproval']);
        }

        $query = $this->Jobs->find('all', contain: ['Operators', 'Qcs', 'Organizations']);

        if ($role === 'operator') {
            $query->where(['Jobs.operator_id' => $userId]);
        } elseif ($role === 'quality_checker') {
            $query->where(['Jobs.qc_id' => $userId]);
        } elseif ($role === 'production') {
            $query->where(['Jobs.status IN' => ['qc_approved', 'in_production']]);
        } elseif ($role === 'scheduler') {
            $query->where(['Jobs.created_by' => $userId]);
        }

        $statusFilter = $this->request->getQuery('status');
        if ($statusFilter) {
            if ($statusFilter === 'in_progress') {
                if ($role === 'operator') {
                    $query->where(['Jobs.status IN' => ['in_digitizing', 'qc_rejected']]);
                } elseif ($role === 'quality_checker') {
                    $query->where(['Jobs.status' => 'digitized']);
                } elseif ($role === 'production') {
                    $query->where(['Jobs.status' => 'in_production']);
                }
            } elseif ($statusFilter === 'sent_for_qc') {
                if ($role === 'operator') {
                    $query->where(['Jobs.status' => 'digitized']);
                }
            } elseif ($statusFilter === 'done') {
                $query->where(['Jobs.status IN' => ['qc_approved', 'in_production', 'completed']]);
            }
        }

        $jobs = $this->paginate($query);
        $statusMeta = $this->fetchTable('JobStatuses')->find('list', keyField: 'name', valueField: function ($e) {
            return ['color' => $e->color, 'label' => $e->label, 'is_terminal' => $e->is_terminal];
        })->all()->toArray();
        $this->set(compact('jobs', 'role', 'statusMeta', 'statusFilter'));
    }

    /**
     * View method
     *
     * @param string|null $id Job id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $job = $this->Jobs->get($id, contain: ['Operators', 'Qcs', 'Organizations', 'JobAttachments', 'JobLogs']);

        // Authorization: ensure current user may view
        if (!$this->authorizeAction($job, 'view')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to view this job.'));
        }

        $operators = $this->Jobs->Operators->find('list', limit: 200)
            ->where(['role' => 'operator'])->all();
        $qcs = $this->Jobs->Qcs->find('list', limit: 200)
            ->where(['role' => 'quality_checker'])->all();
        $statusMeta = $this->fetchTable('JobStatuses')->find('list', keyField: 'name', valueField: function ($e) {
            return ['color' => $e->color, 'label' => $e->label, 'is_terminal' => $e->is_terminal];
        })->all()->toArray();

        $this->set(compact('job', 'operators', 'qcs', 'statusMeta'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        // Authorize via policy
        if (!$this->authorizeAction('Job', 'create')) {
            // Will redirect to login or show forbidden via requireRole fallback
            $redirect = $this->requireRole(['scheduler']);
            if ($redirect instanceof \Cake\Http\Response) {
                return $redirect;
            }
        }

        $job = $this->Jobs->newEmptyEntity();
        $this->JobCounters = $this->getTableLocator()->get('JobCounters');
        $job->job_number = $this->JobCounters->getSuggestedJobNumber();
        if ($this->request->is('post')) {
            $data = $this->request->getData();

            // Attach creator information from current user if available
            $currentUser = $this->getCurrentUser();
            if ($currentUser && empty($data['created_by'])) {
                $data['created_by'] = $currentUser->id ?? null;
            }

            // Always use the next job number from counter so the counter tracks actual job creation
            $this->JobCounters = $this->getTableLocator()->get('JobCounters');
            $data['job_number'] = $this->JobCounters->getNextJobNumber();

            // Set default organization if not provided
            if (empty($data['organization_id'])) {
                $currentUser = $this->getCurrentUser();
                $data['organization_id'] = $currentUser->organization_id ?? 1;
            }

            if (!empty($data['operator_id']) && ($data['status'] ?? 'draft') === 'draft') {
                $data['status'] = 'in_digitizing';
            }

            $job = $this->Jobs->patchEntity($job, $data);
            if ($this->Jobs->save($job)) {
                $this->Flash->success(__('The job has been saved.'));

                $this->JobAttachments = $this->getTableLocator()->get('JobAttachments');
                $files = $this->normalizeFiles($_FILES['files'] ?? null);
                if (!empty($files)) {
                    $uploadedBy = $currentUser->id ?? null;
                    $fileType = $data['attachment_file_type'] ?? '';
                    $comments = $data['attachment_comments'] ?? '';
                    $saved = 0;
                    foreach ($files as $file) {
                        $meta = $this->saveUploadedFile($file, $job->id);
                        if ($meta === false) {
                            continue;
                        }
                        $entity = $this->JobAttachments->newEmptyEntity();
                        $entity->job_id = (int)$job->id;
                        $entity->uploaded_by = $uploadedBy;
                        $entity->file_name = $meta['name'];
                        $entity->file_path = $meta['path'];
                        $entity->file_type = $meta['type'];
                        $entity->file_size = $meta['size'];
                        $entity->mime_type = $meta['mime'];
                        if (!empty($fileType)) {
                            $entity->file_type = $fileType;
                        }
                        if (!empty($comments)) {
                            $entity->comments = $comments;
                        }
                        $this->JobAttachments->save($entity);
                        $saved++;
                    }
                    if ($saved > 0) {
                        $this->Flash->success(__('{0} file(s) attached to this job.', $saved));
                    }
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The job could not be saved. Please, try again.'));
        }
        $operators = $this->Jobs->Operators->find('list', limit: 200)
            ->where(['role' => 'operator'])->all();
        $qcs = $this->Jobs->Qcs->find('list', limit: 200)
            ->where(['role' => 'quality_checker'])->all();
        $organizations = $this->Jobs->Organizations->find('list', limit: 200)->all();
        $this->set(compact('job', 'operators', 'qcs', 'organizations'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Job id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $job = $this->Jobs->get($id, contain: ['JobAttachments.UploadedBy']);

        // Authorization via policy
        if (!$this->authorizeAction($job, 'edit')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not allowed to edit this job.'));
        }

        $policy = new \App\Policy\JobAttachmentPolicy();
        $canAddAttachment = $policy->canAdd($this->getCurrentUser(), $job);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            if (empty($data['status'])) {
                $data['status'] = $job->status ?? 'draft';
            }
            $job = $this->Jobs->patchEntity($job, $data);
            if ($this->Jobs->save($job)) {
                $this->Flash->success(__('The job has been saved.'));

                if ($canAddAttachment) {
                    $this->JobAttachments = $this->getTableLocator()->get('JobAttachments');
                    $files = $this->normalizeFiles($_FILES['files'] ?? null);
                    if (!empty($files)) {
                        $uploadedBy = $this->getCurrentUser()?->id;
                        $fileType = $this->request->getData('attachment_file_type') ?? '';
                        $comments = $this->request->getData('attachment_comments') ?? '';
                        $saved = 0;
                        foreach ($files as $file) {
                            $meta = $this->saveUploadedFile($file, $job->id);
                            if ($meta === false) {
                                continue;
                            }
                            $entity = $this->JobAttachments->newEmptyEntity();
                            $entity->job_id = (int)$job->id;
                            $entity->uploaded_by = $uploadedBy;
                            $entity->file_name = $meta['name'];
                            $entity->file_path = $meta['path'];
                            $entity->file_type = $meta['type'];
                            $entity->file_size = $meta['size'];
                            $entity->mime_type = $meta['mime'];
                            if (!empty($fileType)) {
                                $entity->file_type = $fileType;
                            }
                            if (!empty($comments)) {
                                $entity->comments = $comments;
                            }
                            $this->JobAttachments->save($entity);
                            $saved++;
                        }
                        if ($saved > 0) {
                            $this->Flash->success(__('{0} file(s) attached to this job.', $saved));
                        }
                    }
                }

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The job could not be saved. Please, try again.'));
        }
        $operators = $this->Jobs->Operators->find('list', limit: 200)->all();
        $qcs = $this->Jobs->Qcs->find('list', limit: 200)->all();
        $organizations = $this->Jobs->Organizations->find('list', limit: 200)->all();
        // Statuses from the master table so admin changes (color/label/active)
        // flow through to this dropdown automatically.
        $statuses = $this->fetchTable('JobStatuses')->find('list', limit: 200)
            ->where(['is_active' => true])
            ->orderBy(['sort_order' => 'ASC', 'label' => 'ASC'])
            ->all();
        $statusMeta = $this->fetchTable('JobStatuses')->find('list', keyField: 'name', valueField: function ($e) {
            return ['color' => $e->color, 'label' => $e->label, 'is_terminal' => $e->is_terminal];
        })->all()->toArray();
        $canDelete = $this->authorizeAction($job, 'delete');
        $this->set(compact('job', 'operators', 'qcs', 'organizations', 'statuses', 'statusMeta', 'canDelete', 'canAddAttachment'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Job id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $job = $this->Jobs->get($id);
        if (!$this->authorizeAction($job, 'delete')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to delete this job.'));
        }

        $this->request->allowMethod(['post', 'delete']);
        if ($this->Jobs->delete($job)) {
            $this->Flash->success(__('The job has been deleted.'));
        } else {
            $this->Flash->error(__('The job could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Assign an operator or QC to a job. POST only.
     */
    public function assign($id = null)
    {
        $this->request->allowMethod(['post']);
        $job = $this->Jobs->get($id);
        if (!$this->authorizeAction($job, 'assign')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to assign this job.'));
        }

        $data = $this->request->getData();
        $patch = [];
        if (isset($data['operator_id'])) {
            $patch['operator_id'] = $data['operator_id'];
        }
        if (isset($data['qc_id'])) {
            $patch['qc_id'] = $data['qc_id'];
        }

        if (!empty($patch['operator_id']) && in_array($job->status, ['draft', 'pending_approval'], true)) {
            $patch['status'] = 'in_digitizing';
        }

    $job = $this->Jobs->patchEntity($job, $patch);
    if ($this->Jobs->save($job)) {
        $this->JobLogs = $this->getTableLocator()->get('JobLogs');
        $log = $this->JobLogs->newEmptyEntity();
        $currentUser = $this->getCurrentUser();
        $log->job_id = $job->id;
        $log->user_id = $currentUser->id ?? null;
        $log->action = 'assigned';
        $log->comments = json_encode($patch);
        $this->JobLogs->save($log);

        $this->Flash->success(__('Assignment updated.'));
    } else {
        $this->Flash->error(__('Failed to assign job.'));
    }

        return $this->redirect(['action' => 'view', $job->id]);
    }

    /**
     * Approve a job (QC action)
     */
    public function approve($id = null)
    {
        $this->request->allowMethod(['post']);
        $job = $this->Jobs->get($id);
        if (!$this->authorizeAction($job, 'approve')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to approve this job.'));
        }

        if ($job->status !== 'digitized') {
            $this->Flash->error(__('Only digitized jobs can be approved.'));
            return $this->redirect(['action' => 'view', $job->id]);
        }

        $job->status = 'qc_approved';
        if ($this->saveWithLog($job, 'approved', 'Approved by QC')) {
            $this->Flash->success(__('Job approved.'));
        } else {
            $this->Flash->error(__('Failed to approve job.'));
        }

        return $this->redirect(['action' => 'view', $job->id]);
    }

    /**
     * Reject a job (QC action) - requires comment
     */
    public function reject($id = null)
    {
        $this->request->allowMethod(['post']);
        $job = $this->Jobs->get($id);
        if (!$this->authorizeAction($job, 'approve')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to reject this job.'));
        }

        $comment = trim((string)$this->request->getData('comment'));
        if ($job->status !== 'digitized' || $comment === '') {
            $this->Flash->error(__('A rejection comment is required for a digitized job.'));
            return $this->redirect(['action' => 'view', $job->id]);
        }
        $job->status = 'qc_rejected';
        if ($this->saveWithLog($job, 'rejected', $comment)) {
            $this->Flash->success(__('Job rejected and operator notified.'));
        } else {
            $this->Flash->error(__('Failed to reject job.'));
        }

        return $this->redirect(['action' => 'view', $job->id]);
    }

    /** Submit an assigned digitizing job to its assigned QC reviewer. */
    public function submit($id = null)
    {
        $this->request->allowMethod(['post']);
        $job = $this->Jobs->get($id);
        if (!$this->authorizeAction($job, 'submit')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to submit this job.'));
        }
        if (!in_array($job->status, ['in_digitizing', 'qc_rejected'], true) || !$job->qc_id) {
            $this->Flash->error(__('Assign a QC reviewer before submitting a job for review.'));
            return $this->redirect(['action' => 'view', $job->id]);
        }

    $job->status = 'digitized';
    if ($this->saveWithLog($job, 'submitted_for_qc', 'EMB file submitted for QC review.')) {
        $this->Flash->success(__('Job submitted for QC review.'));
    } else {
        $this->Flash->error(__('Failed to submit job for QC review.'));
    }

    return $this->redirect(['action' => 'view', $job->id]);
    }

    /** Production begins machine processing of an approved job. */
    public function startProduction($id = null)
    {
        return $this->moveToProductionStatus($id, 'qc_approved', 'in_production', 'production_started', 'Production processing started.');
    }

    /** Production records completion after machine processing. */
    public function complete($id = null)
    {
        return $this->moveToProductionStatus($id, 'in_production', 'completed', 'production_completed', 'Production processing completed.');
    }

    private function moveToProductionStatus($id, string $from, string $to, string $action, string $details)
    {
        $this->request->allowMethod(['post']);
        $job = $this->Jobs->get($id);
        if (!$this->authorizeAction($job, 'produce')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to process this job.'));
        }
        if ($job->status !== $from) {
            $this->Flash->error(__('This job is not ready for that production step.'));
            return $this->redirect(['action' => 'view', $job->id]);
        }

        $job->status = $to;
        if ($this->saveWithLog($job, $action, $details)) {
            $this->Flash->success(__('Job status updated.'));
        } else {
            $this->Flash->error(__('Failed to update job status.'));
        }

        return $this->redirect(['action' => 'view', $job->id]);
    }

    private function saveWithLog($job, string $action, string $details): bool
    {
        if (!$this->Jobs->save($job)) {
            return false;
        }
        $this->JobLogs = $this->getTableLocator()->get('JobLogs');
        $log = $this->JobLogs->newEmptyEntity();
        $currentUser = $this->getCurrentUser();
        $log->job_id = $job->id;
        $log->user_id = $currentUser->id ?? null;
        $log->action = $action;
        $log->comments = $details;

        return (bool)$this->JobLogs->save($log);
    }

    /** Quick manual note added by any team member on the job view page. */
    public function addLog($id = null)
    {
        $this->request->allowMethod(['post']);
        $job = $this->Jobs->get($id);
    $comment = trim((string)$this->request->getData('comments'));
    if ($comment === '') {
        $this->Flash->error(__('Comment cannot be empty.'));
        return $this->redirect(['action' => 'view', $job->id]);
    }
    if ($this->saveWithLog($job, 'note', $comment)) {
        $this->Flash->success(__('Note added.'));
    } else {
        $this->Flash->error(__('Failed to add note.'));
    }
    return $this->redirect(['action' => 'view', $job->id]);
    }
}
