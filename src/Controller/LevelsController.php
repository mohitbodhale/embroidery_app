<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Levels master: pay grades and the payment amount an operator earns per job
 * assigned that level. Administrator only.
 */
class LevelsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->Levels = $this->fetchTable('Levels');
    }

    public function index()
    {
        $this->requireRole(['admin']);

        $levels = $this->Levels->find()
            ->contain(['LevelRates'])
            ->orderBy(['Levels.sort_order' => 'ASC', 'Levels.id' => 'ASC'])
            ->all();

        $currentRates = $this->Levels->currentRates();
        $this->set(compact('levels', 'currentRates'));
    }

    public function add()
    {
        $this->requireRole(['admin']);

        $level = $this->Levels->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['is_active'] = !empty($data['is_active']);
            $level = $this->Levels->patchEntity($level, $data);
            if ($this->Levels->save($level)) {
                $this->Flash->success(__('Level "{0}" created.', $level->label));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Could not save level. Please fix the errors and try again.'));
        }
        $this->set(compact('level'));
    }

    public function edit($id = null)
    {
        $this->requireRole(['admin']);

        $level = $this->Levels->get($id, contain: ['LevelRates']);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->getData();
            $data['is_active'] = !empty($data['is_active']);
            $level = $this->Levels->patchEntity($level, $data);
            if ($this->Levels->save($level)) {
                $this->Flash->success(__('Level "{0}" updated.', $level->label));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Could not update level. Please fix the errors and try again.'));
        }
        $this->set(compact('level'));
    }

    /**
     * Add a payment period to a level.
     *
     * @param string|null $id Level id.
     * @return \Cake\Http\Response|null|void
     */
    public function addRate($id = null)
    {
        $this->request->allowMethod(['post']);
        $this->requireRole(['admin']);

        $level = $this->Levels->get($id);
        $rates = $this->Levels->LevelRates;
        $rate = $rates->newEmptyEntity();

        $data = $this->request->getData();
        $data['level_id'] = $level->id;
        $rate = $rates->patchEntity($rate, $data);

        $from = $this->toDate($rate->valid_from);
        $to = $this->toDate($rate->valid_to);
        if ($from !== null && $to !== null && $from > $to) {
            $this->Flash->error(__('The start date is after the end date.'));

            return $this->redirect(['action' => 'edit', $level->id]);
        }

        $overlap = $rates->findOverlapping($level->id, $from, $to)->first();
        if ($overlap) {
            $this->Flash->error(__(
                'This period overlaps an existing one ({0}).',
                $overlap->period_formatted
            ));

            return $this->redirect(['action' => 'edit', $level->id]);
        }

        if ($rates->save($rate)) {
            $this->Flash->success(__('Payment period added.'));
        } else {
            $this->Flash->error(__('Could not add the payment period. Please fix the errors and try again.'));
        }

        return $this->redirect(['action' => 'edit', $level->id]);
    }

    /**
     * Remove a payment period from a level.
     *
     * @param string|null $id Level id.
     * @param string|null $rateId Period id.
     * @return \Cake\Http\Response|null|void
     */
    public function deleteRate($id = null, $rateId = null)
    {
        $this->request->allowMethod(['post']);
        $this->requireRole(['admin']);

        $level = $this->Levels->get($id);
        $rates = $this->Levels->LevelRates;
        $rate = $rates->find()
            ->where(['LevelRates.level_id' => $level->id, 'LevelRates.id' => $rateId])
            ->firstOrFail();

        if ($rates->delete($rate)) {
            $this->Flash->success(__('Payment period removed.'));
        } else {
            $this->Flash->error(__('Could not remove the payment period.'));
        }

        return $this->redirect(['action' => 'edit', $level->id]);
    }

    /**
     * Normalize a posted date to a DateTimeImmutable, or null.
     *
     * @param \DateTimeInterface|string|null $value
     * @return \DateTimeImmutable|null
     */
    private function toDate(\DateTimeInterface|string|null $value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            return new \DateTimeImmutable($value);
        }

        return \DateTimeImmutable::createFromInterface($value);
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $this->requireRole(['admin']);

        $level = $this->Levels->get($id);
        $jobCount = $this->Levels->Jobs->find()->where(['level_id' => $id])->count();
        if ($jobCount > 0) {
            $this->Flash->error(__(
                'Cannot delete level "{0}" - {1} job(s) are using it. Deactivate it instead.',
                $level->label,
                $jobCount
            ));

            return $this->redirect(['action' => 'index']);
        }

        if ($this->Levels->delete($level)) {
            $this->Flash->success(__('Level "{0}" deleted.', $level->label));
        } else {
            $this->Flash->error(__('Could not delete level.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}