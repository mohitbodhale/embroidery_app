<?php
declare(strict_types=1);

namespace App\Controller;

class RolesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->Roles = $this->fetchTable('Roles');
    }

    public function index()
    {
        $this->requireRole(['admin']);

        $roles = $this->Roles->find()
            ->contain(['Users'])
            ->orderBy(['Roles.sort_order' => 'ASC', 'Roles.id' => 'ASC'])
            ->all();

        $this->set(compact('roles'));
    }

    public function export()
    {
        $this->requireRole(['admin']);
        $this->request->allowMethod(['get']);
        $roles = $this->Roles->find()
            ->contain(['Users'])
            ->orderBy(['Roles.sort_order' => 'ASC', 'Roles.id' => 'ASC'])
            ->all();

        $filename = 'roles_export_' . date('Y-m-d') . '.csv';
        
        $this->response = $this->response
            ->withType('csv')
            ->withDownload($filename)
            ->withHeader('Content-Type', 'text/csv; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        $this->viewBuilder()->disableAutoLayout();
        
        $csv = fopen('php://output', 'w');
        fputcsv($csv, ['ID', 'Name', 'Label', 'Description', 'Color', 'Active', 'Sort Order', 'Users Count']);
        foreach ($roles as $role) {
            fputcsv($csv, [
                $role->id,
                $role->name,
                $role->label,
                $role->description ?? '',
                $role->color,
                $role->is_active ? 'Yes' : 'No',
                $role->sort_order,
                count($role->users ?? [])
            ]);
        }
        fclose($csv);
        
        return $this->response;
    }

    public function view($id = null)
    {
        $this->requireRole(['admin']);
        $role = $this->Roles->get($id, contain: ['Users']);
        $this->set(compact('role'));
    }

    public function add()
    {
        $this->requireRole(['admin']);
        $role = $this->Roles->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['is_active'] = !empty($data['is_active']);
            $role = $this->Roles->patchEntity($role, $data);
            if ($this->Roles->save($role)) {
                $this->Flash->success(__('Role "{0}" created.', $role->label));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Could not save role. Please fix the errors and try again.'));
        }
        $this->set(compact('role'));
    }

    public function edit($id = null)
    {
        $this->requireRole(['admin']);
        $role = $this->Roles->get($id);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            $data['is_active'] = !empty($data['is_active']);
            $role = $this->Roles->patchEntity($role, $data);
            if ($this->Roles->save($role)) {
                $this->Flash->success(__('Role "{0}" updated.', $role->label));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Could not update role.'));
        }
        $this->set(compact('role'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $this->requireRole(['admin']);
        $role = $this->Roles->get($id);
        $userCount = $this->Roles->Users->find()->where(['role_id' => $id])->count();
        if ($userCount > 0) {
            $this->Flash->error(__('Cannot delete role "{0}" - {1} user(s) are assigned to it.', $role->label, $userCount));
            return $this->redirect(['action' => 'index']);
        }
        if ($this->Roles->delete($role)) {
            $this->Flash->success(__('Role "{0}" deleted.', $role->label));
        } else {
            $this->Flash->error(__('Could not delete role.'));
        }
        return $this->redirect(['action' => 'index']);
    }
}
