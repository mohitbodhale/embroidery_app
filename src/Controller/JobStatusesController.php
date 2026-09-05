<?php
declare(strict_types=1);

namespace App\Controller;

class JobStatusesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->JobStatuses = $this->fetchTable('JobStatuses');
    }

    public function index()
    {
        $this->requireRole(['admin']);

        $jobStatuses = $this->JobStatuses->find()
            ->contain(['Jobs'])
            ->orderBy(['JobStatuses.sort_order' => 'ASC', 'JobStatuses.id' => 'ASC'])
            ->all();

        $this->set(compact('jobStatuses'));
    }

    public function export()
    {
        $this->requireRole(['admin']);
        $this->request->allowMethod(['get']);
        $jobStatuses = $this->JobStatuses->find()
            ->contain(['Jobs'])
            ->orderBy(['JobStatuses.sort_order' => 'ASC', 'JobStatuses.id' => 'ASC'])
            ->all();

        $filename = 'job_statuses_export_' . date('Y-m-d') . '.csv';
        
        $this->response = $this->response
            ->withType('csv')
            ->withDownload($filename)
            ->withHeader('Content-Type', 'text/csv; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        $this->viewBuilder()->disableAutoLayout();
        
        $csv = fopen('php://output', 'w');
        fputcsv($csv, ['ID', 'Name', 'Label', 'Description', 'Color', 'Active', 'Terminal', 'Sort Order', 'Jobs Count']);
        foreach ($jobStatuses as $jobStatus) {
            fputcsv($csv, [
                $jobStatus->id,
                $jobStatus->name,
                $jobStatus->label,
                $jobStatus->description ?? '',
                $jobStatus->color,
                $jobStatus->is_active ? 'Yes' : 'No',
                $jobStatus->is_terminal ? 'Yes' : 'No',
                $jobStatus->sort_order,
                count($jobStatus->jobs ?? [])
            ]);
        }
        fclose($csv);
        
        return $this->response;
    }

    public function view($id = null)
    {
        $this->requireRole(['admin']);
        $jobStatus = $this->JobStatuses->get($id, contain: ['Jobs']);
        $this->set(compact('jobStatus'));
    }

    public function add()
    {
        $this->requireRole(['admin']);
        $jobStatus = $this->JobStatuses->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['is_active'] = !empty($data['is_active']);
            $data['is_terminal'] = !empty($data['is_terminal']);
            $jobStatus = $this->JobStatuses->patchEntity($jobStatus, $data);
            if ($this->JobStatuses->save($jobStatus)) {
                $this->Flash->success(__('Status "{0}" created.', $jobStatus->label));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Could not save status. Please fix the errors and try again.'));
        }
        $this->set(compact('jobStatus'));
    }

    public function edit($id = null)
    {
        $this->requireRole(['admin']);
        $jobStatus = $this->JobStatuses->get($id);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            $data['is_active'] = !empty($data['is_active']);
            $data['is_terminal'] = !empty($data['is_terminal']);
            $jobStatus = $this->JobStatuses->patchEntity($jobStatus, $data);
            if ($this->JobStatuses->save($jobStatus)) {
                $this->Flash->success(__('Status "{0}" updated.', $jobStatus->label));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Could not update status.'));
        }
        $this->set(compact('jobStatus'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $this->requireRole(['admin']);
        $jobStatus = $this->JobStatuses->get($id);
        $jobCount = $this->JobStatuses->Jobs->find()->where(['status_id' => $id])->count();
        if ($jobCount > 0) {
            $this->Flash->error(__('Cannot delete status "{0}" - {1} job(s) are using it.', $jobStatus->label, $jobCount));
            return $this->redirect(['action' => 'index']);
        }
        if ($this->JobStatuses->delete($jobStatus)) {
            $this->Flash->success(__('Status "{0}" deleted.', $jobStatus->label));
        } else {
            $this->Flash->error(__('Could not delete status.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
