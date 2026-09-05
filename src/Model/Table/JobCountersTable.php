<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class JobCountersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('job_counters');
        $this->setPrimaryKey('id');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('last_number')
            ->requirePresence('last_number', 'create')
            ->notEmptyString('last_number');

        return $validator;
    }

    public function getNextJobNumber(): string
    {
        $counter = $this->find()->first();
        if (!$counter) {
            $counter = $this->newEmptyEntity();
            $counter->last_number = 1;
            $this->save($counter);
        } else {
            $counter->last_number += 1;
            $this->save($counter);
        }

        $year = $this->getFinancialYear();
        $counterStr = str_pad((string)$counter->last_number, 3, '0', STR_PAD_LEFT);

        return 'JOB_' . $year . '_' . $counterStr;
    }

    public function getSuggestedJobNumber(): string
    {
        $counter = $this->find()->first();
        if (!$counter) {
            return 'JOB_' . $this->getFinancialYear() . '_001';
        }

        $nextNumber = $counter->last_number + 1;
        $year = $this->getFinancialYear();
        $counterStr = str_pad((string)$nextNumber, 3, '0', STR_PAD_LEFT);

        return 'JOB_' . $year . '_' . $counterStr;
    }

    public function getFinancialYear(): int
    {
        $month = (int)date('n');
        $year = (int)date('Y');

        if ($month >= 4) {
            return $year;
        }

        return $year - 1;
    }
}
