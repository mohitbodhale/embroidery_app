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
            $query->where(['Jobs.status IN' => ['qc_approved', 'in_production', 'completed']]);
        } elseif ($role === 'scheduler') {
            $query->where(['Jobs.created_by' => $userId]);
        }

        $statusFilter = $this->request->getQuery('status');
        if ($statusFilter) {
                if ($statusFilter === 'in_progress') {
                    if ($role === 'operator') {
                        $query->where(['Jobs.status IN' => ['in_progress', 'qc_rejected']]);
                    } elseif ($role === 'quality_checker') {
                        $query->where(['Jobs.status' => 'ready_for_qc']);
                    } elseif ($role === 'production') {
                        $query->where(['Jobs.status' => 'in_production']);
                    }
                } elseif ($statusFilter === 'sent_for_qc') {
                    if ($role === 'operator') {
                        $query->where(['Jobs.status' => 'ready_for_qc']);
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
        $job = $this->Jobs->get($id, contain: ['Operators', 'Qcs', 'Organizations', 'Levels', 'JobAttachments', 'JobLogs']);
        if (!$this->authorizeAction($job, 'view')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to view this job.'));
        }
        $canEdit = $this->authorizeAction($job, 'edit');
        $canDelete = $this->authorizeAction($job, 'delete');
        $jobPayment = $this->Jobs->paymentFor($job);

        $operators = $this->Jobs->Operators->find('list', limit: 200)
            ->where(['role' => 'operator'])->all();
        $qcs = $this->Jobs->Qcs->find('list', limit: 200)
            ->where(['role' => 'quality_checker'])->all();
        $statusMeta = $this->fetchTable('JobStatuses')->find('list', keyField: 'name', valueField: function ($e) {
            return ['color' => $e->color, 'label' => $e->label, 'is_terminal' => $e->is_terminal];
        })->all()->toArray();

        $this->set(compact('job', 'operators', 'qcs', 'statusMeta', 'jobPayment', 'canEdit', 'canDelete'));
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
        $jobCounters = $this->getTableLocator()->get('JobCounters');
        $job->job_number = $jobCounters->getSuggestedJobNumber();
        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $currentUser = $this->getCurrentUser();
            $data['created_by'] = $currentUser->id;

            // Always use the next job number from counter so the counter tracks actual job creation
            $jobCounters = $this->getTableLocator()->get('JobCounters');
            $data['job_number'] = $jobCounters->getNextJobNumber();

            $organizationId = $this->normalizedRole($currentUser) === 'admin'
                ? (int)($data['organization_id'] ?? $currentUser->organization_id)
                : (int)$currentUser->organization_id;
            if ($organizationId < 1 || !$this->Jobs->Organizations->exists(['id' => $organizationId])) {
                $this->Flash->error(__('Select a valid organization.'));
                return $this->redirect(['action' => 'add']);
            }
            $data['organization_id'] = $organizationId;

            if (!$this->isValidAssignee($data['operator_id'] ?? null, 'operator', $organizationId)
                || !$this->isValidAssignee($data['qc_id'] ?? null, 'quality_checker', $organizationId)) {
                $this->Flash->error(__('The selected operator and QC reviewer must belong to the job organization.'));
                return $this->redirect(['action' => 'add']);
            }

            $data['status'] = 'draft';
            if (!empty($data['operator_id'])) {
                $data['status'] = 'in_progress';
            }

            // The level comes from the posted level_id; the
            // payment is computed live from the rate schedule,
            // so nothing is stored on the job.
            $job = $this->Jobs->patchEntity($job, $data);
            if ($this->Jobs->save($job)) {
                $this->Flash->success(__('The job has been saved.'));

                $jobAttachments = $this->getTableLocator()->get('JobAttachments');
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
                        $entity = $jobAttachments->newEmptyEntity();
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
                        $jobAttachments->save($entity);
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
        $levels = $this->fetchTable('Levels')->selectOptions();
        $rateSchedule = $this->fetchTable('Levels')->rateSchedule();
        $this->set(compact('job', 'operators', 'qcs', 'organizations', 'levels', 'rateSchedule'));
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
        // Operators and Qcs are required by the template: the read-only
        // "assigned to" boxes render the associated user names.
        $job = $this->Jobs->get($id, contain: ['Levels', 'Operators', 'Qcs', 'JobAttachments.UploadedBy', 'JobLogs.Users']);
        $jobPayment = $this->Jobs->paymentFor($job);

        if (!$this->authorizeAction($job, 'edit')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not allowed to edit this job.'));
        }
        $canEdit = true;

        $canAddAttachment = (new \App\Policy\JobAttachmentPolicy())->canAdd($this->getCurrentUser(), $job);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            if (empty($data['status'])) {
                $data['status'] = $job->status ?? 'draft';
            }
            $currentUser = $this->getCurrentUser();
            unset($data['status'], $data['operator_id'], $data['qc_id'], $data['organization_id'], $data['created_by']);
            if ($this->normalizedRole($currentUser) === 'operator') {
                $data = array_intersect_key($data, array_flip(['job_number', 'title', 'instructions']));
            }
            if (!$this->canSetLevel($currentUser)) {
                // Operators and reviewers must not change the level.
                unset($data['level_id']);
                unset($data['level_payment']);
            }

            $job = $this->Jobs->patchEntity($job, $data);
            $jobSaved = $this->Jobs->save($job);
            if ($jobSaved) {
                $this->Flash->success(__('The job has been saved.'));
            } else {
                $this->Flash->error(__('The job could not be saved. Please, try again.'));
            }

            if ($canAddAttachment) {
                $jobAttachments = $this->getTableLocator()->get('JobAttachments');
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
                        $entity = $jobAttachments->newEmptyEntity();
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
                        $jobAttachments->save($entity);
                        $saved++;
                    }
                    if ($saved > 0) {
                        $this->Flash->success(__('{0} file(s) attached to this job.', $saved));
                    }
                }
            }

            return $this->redirect(['action' => 'index']);
        }
        $operators = $this->Jobs->Operators->find('list', limit: 200)->all();
        $qcs = $this->Jobs->Qcs->find('list', limit: 200)->all();
        $organizations = $this->Jobs->Organizations->find('list', limit: 200)->all();
        $statuses = $this->fetchTable('JobStatuses')->find('list', keyField: 'name', valueField: 'label', limit: 200)
            ->where(['is_active' => true])
            ->orderBy(['sort_order' => 'ASC', 'label' => 'ASC'])
            ->all();
        $statusMeta = $this->fetchTable('JobStatuses')->find('list', keyField: 'name', valueField: function ($e) {
            return ['color' => $e->color, 'label' => $e->label, 'is_terminal' => $e->is_terminal];
        })->all()->toArray();
        $canDelete = $this->authorizeAction($job, 'delete');
        $levels = $this->fetchTable('Levels')->selectOptions(activeOnly: false);
        $rateSchedule = $this->fetchTable('Levels')->rateSchedule();
        $this->set(compact('job', 'operators', 'qcs', 'organizations', 'statuses', 'statusMeta', 'canDelete', 'canAddAttachment', 'levels', 'rateSchedule', 'jobPayment'));
    }

    /**
     * Whether this user may choose a job's level and payment amount.
     *
     * Levels carry the pay rate, so only the roles that price work (admin and
     * scheduler) may set them. Operators and reviewers see the level read-only
     * and must not be able to post a different amount.
     *
     * @param object|null $user Authenticated user, or null when signed out.
     * @return bool
     */
    protected function canSetLevel(?object $user): bool
    {
        return $user !== null && in_array(
            $this->normalizedRole($user),
            ['admin', 'scheduler'],
            true
        );
    }

    private function isValidAssignee($userId, string $role, int $organizationId): bool
    {
        if ($userId === null || $userId === '') {
            return true;
        }

        return $this->fetchTable('Users')->exists([
            'id' => (int)$userId,
            'role' => $role,
            'organization_id' => $organizationId,
        ]);
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

        if (!$this->isValidAssignee($patch['operator_id'] ?? null, 'operator', (int)$job->organization_id)
            || !$this->isValidAssignee($patch['qc_id'] ?? null, 'quality_checker', (int)$job->organization_id)) {
            $this->Flash->error(__('The selected operator and QC reviewer must belong to the job organization.'));
            return $this->redirect(['action' => 'view', $job->id]);
        }

        if (!empty($patch['operator_id']) && in_array($job->status, ['draft', 'pending_approval'], true)) {
            $patch['status'] = 'in_progress';
        }

        $job = $this->Jobs->patchEntity($job, $patch);
        if ($this->saveWithLog($job, 'assigned', (string)json_encode($patch))) {
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

        if ($job->status !== 'ready_for_qc') {
            $this->Flash->error(__('Only ready-for-QC jobs can be approved.'));
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
        if ($job->status !== 'ready_for_qc' || $comment === '') {
            $this->Flash->error(__('A rejection comment is required for a ready-for-QC job.'));
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
        if (!in_array($job->status, ['in_progress', 'qc_rejected'], true) || !$job->qc_id) {
            $this->Flash->error(__('Assign a QC reviewer before submitting a job for review.'));
            return $this->redirect(['action' => 'view', $job->id]);
        }

    $job->status = 'ready_for_qc';
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
        $connection = $this->Jobs->getConnection();
        $jobLogs = $this->getTableLocator()->get('JobLogs');
        $connection->begin();
        try {
            if (!$this->Jobs->save($job)) {
                $connection->rollback();
                return false;
            }

            $currentUser = $this->getCurrentUser();
            $log = $jobLogs->newEmptyEntity();
            $log->job_id = $job->id;
            $log->user_id = $currentUser->id ?? null;
            $log->action = $action;
            $log->comments = $details;
            if (!$jobLogs->save($log)) {
                $connection->rollback();
                return false;
            }

            $connection->commit();
            return true;
        } catch (\Throwable $exception) {
            $connection->rollback();
            throw $exception;
        }
    }

    /** Quick manual note added by any team member on the job view page. */
    public function addLog($id = null)
    {
        $this->request->allowMethod(['post']);
        $job = $this->Jobs->get($id);
        if (!$this->authorizeAction($job, 'view')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to add a note to this job.'));
        }
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

    /** Return a job to the scheduler for reassignment. */
    public function returnToScheduler($id = null)
    {
        $this->request->allowMethod(['post']);
        $job = $this->Jobs->get($id);
        if (!$this->authorizeAction($job, 'assign')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to return this job.'));
        }
        $job->status = 'draft';
        $job->operator_id = null;
        if ($this->saveWithLog($job, 'returned_to_scheduler', 'Job returned to scheduler for reassignment.')) {
            $this->Flash->success(__('Job returned to scheduler.'));
        } else {
            $this->Flash->error(__('Failed to return job to scheduler.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
