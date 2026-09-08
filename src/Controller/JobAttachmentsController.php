<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * JobAttachments Controller
 *
 * @property \App\Model\Table\JobAttachmentsTable $JobAttachments
 */
class JobAttachmentsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Flash');
    }

    public function index()
    {
        $user = $this->getCurrentUser();
        if (!$this->authorizeAction('JobAttachment', 'index')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to view attachments.'));
        }

        $role = $user ? strtolower((string)$user->role) : '';
        $query = $this->JobAttachments->find()
            ->contain(['Jobs', 'UploadedBy']);

        $jobId = $this->request->getQuery('job_id');
        if ($jobId) {
            $query->where(['JobAttachments.job_id' => (int)$jobId]);
        }

        if ($role === 'operator' && $user) {
            $query->where(['Jobs.operator_id' => $user->id]);
        } elseif ($role === 'quality_checker' && $user) {
            $query->where(['Jobs.qc_id' => $user->id]);
        } elseif ($role === 'production' && $user) {
            $query->where(['Jobs.status IN' => ['qc_approved', 'in_production']]);
        }

        $jobAttachments = $this->paginate($query);
        $this->set(compact('jobAttachments'));
    }

    public function view($id = null)
    {
        $jobAttachment = $this->JobAttachments->get($id, contain: ['Jobs', 'UploadedBy']);
        if (!$this->authorizeAction($jobAttachment, 'view')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to view this attachment.'));
        }
        $this->set(compact('jobAttachment'));
    }

    public function add()
    {
        $jobId = $this->request->getQuery('job_id');
        $job = null;
        if ($jobId) {
            $job = $this->JobAttachments->Jobs->get($jobId);
        }
        $policy = new \App\Policy\JobAttachmentPolicy();
        if (!$policy->canAdd($this->getCurrentUser(), $job)) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to add attachments.'));
        }

        $jobAttachment = $this->JobAttachments->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $uploadedBy = $this->getCurrentUser()?->id;
            $fileType = $data['file_type'] ?? '';
            $comments = $data['comments'] ?? '';

            $files = $this->normalizeFiles($_FILES['files'] ?? null);
            if (empty($files)) {
                $this->Flash->error(__('Please select at least one file to upload.'));
            } else {
                $saved = 0;
                $failed = 0;
                foreach ($files as $file) {
                    $meta = $this->saveUploadedFile($file, $jobId);
                    if ($meta === false) {
                        $failed++;
                        continue;
                    }
                    $entity = $this->JobAttachments->newEmptyEntity();
                    $entity->job_id = (int)$jobId;
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
                    if ($this->JobAttachments->save($entity)) {
                        $saved++;
                    } else {
                        $failed++;
                    }
                }
                if ($saved > 0) {
                    $this->Flash->success(__('{0} file(s) uploaded.', $saved));
                    $target = $this->request->getQuery('redirect');
                    if ($target) {
                        return $this->redirect($target);
                    }
                    return $this->redirect(['controller' => 'Jobs', 'action' => 'view', $jobId]);
                }
                $this->Flash->error(__('Could not save any files.'));
            }
        }
        $jobs = $this->JobAttachments->Jobs->find('list', limit: 200);
        if ($jobId) {
            $jobs = $jobs->where(['Jobs.id' => $jobId]);
        }
        $this->set(compact('jobAttachment', 'jobs'));
    }

    public function edit($id = null)
    {
        $jobAttachment = $this->JobAttachments->get($id, contain: ['Jobs']);
        if (!$this->authorizeAction($jobAttachment, 'edit')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to edit this attachment.'));
        }
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $jobId = $data['job_id'] ?? $jobAttachment->job_id;
            $files = $this->normalizeFiles($_FILES['files'] ?? null);
            if (!empty($files)) {
                $file = $files[0];
                $meta = $this->saveUploadedFile($file, $jobId);
                if ($meta !== false) {
                    $data['file_name'] = $meta['name'];
                    $data['file_path'] = $meta['path'];
                    $data['file_type'] = $meta['type'];
                    $data['file_size'] = $meta['size'];
                    $data['mime_type'] = $meta['mime'];
                }
            }
            $jobAttachment = $this->JobAttachments->patchEntity($jobAttachment, $data);
            if ($this->JobAttachments->save($jobAttachment)) {
                $this->Flash->success(__('Attachment updated.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Could not save attachment.'));
        }
        $jobs = $this->JobAttachments->Jobs->find('list', limit: 200)->all();
        $this->set(compact('jobAttachment', 'jobs'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $jobAttachment = $this->JobAttachments->get($id, contain: ['Jobs']);
        if (!$this->authorizeAction($jobAttachment, 'delete')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to delete this attachment.'));
        }
        $jobId = $jobAttachment->job_id;
        if ($this->JobAttachments->delete($jobAttachment)) {
            $this->Flash->success(__('Attachment deleted.'));
        } else {
            $this->Flash->error(__('Could not delete attachment.'));
        }
        return $this->redirect(['controller' => 'Jobs', 'action' => 'view', $jobId]);
    }

    /**
     * Download an attachment file.
     */
    public function download($id = null)
    {
        $jobAttachment = $this->JobAttachments->get($id, contain: ['Jobs']);
        if (!$this->authorizeAction($jobAttachment, 'download')) {
            throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to download this file.'));
        }

        $filePath = WWW_ROOT . ltrim($jobAttachment->file_path, '/');
        if (!is_file($filePath)) {
            throw new \Cake\Http\Exception\NotFoundException(__('File not found on disk.'));
        }

        $this->response = $this->response->withFile($filePath, [
            'name' => $jobAttachment->file_name,
            'download' => true,
            'Content-Type' => $jobAttachment->mime_type ?: 'application/octet-stream',
        ]);

        return $this->response;
    }
}
