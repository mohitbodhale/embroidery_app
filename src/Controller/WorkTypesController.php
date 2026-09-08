<?php
declare(strict_types=1);

namespace App\Controller;

class WorkTypesController extends AppController
{
    public function index()
    {
        $this->set('workTypes', $this->paginate($this->WorkTypes));
    }

    public function add()
    {
        $workType = $this->WorkTypes->newEmptyEntity();
        if ($this->request->is('post')) {
            $workType = $this->WorkTypes->patchEntity($workType, $this->request->getData());
            if ($this->WorkTypes->save($workType)) {
                $this->Flash->success(__('The work type has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The work type could not be saved. Please, try again.'));
        }
        $this->set(compact('workType'));
    }

    public function edit($id = null)
    {
        $workType = $this->WorkTypes->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $workType = $this->WorkTypes->patchEntity($workType, $this->request->getData());
            if ($this->WorkTypes->save($workType)) {
                $this->Flash->success(__('The work type has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The work type could not be saved. Please, try again.'));
        }
        $this->set(compact('workType'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $workType = $this->WorkTypes->get($id);
        if ($this->WorkTypes->delete($workType)) {
            $this->Flash->success(__('The work type has been deleted.'));
        } else {
            $this->Flash->error(__('The work type could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
