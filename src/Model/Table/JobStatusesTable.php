<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class JobStatusesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('job_statuses');
        $this->setDisplayField('label');
        $this->setPrimaryKey('id');

        $this->hasMany('Jobs', [
            'foreignKey' => 'status_id',
        ]);

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => ['created_at' => 'new'],
            ],
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 64)
            ->requirePresence('name', 'create')
            ->notEmptyString('name')
            ->add('name', 'unique', [
                'rule' => 'validateUnique',
                'provider' => 'table',
                'message' => 'Status name must be unique.',
            ]);

        $validator
            ->scalar('label')
            ->maxLength('label', 128)
            ->requirePresence('label', 'create')
            ->notEmptyString('label');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->scalar('color')
            ->maxLength('color', 16)
            ->allowEmptyString('color');

        $validator
            ->integer('sort_order')
            ->allowEmptyString('sort_order');

        $validator
            ->boolean('is_active')
            ->allowEmptyString('is_active');

        $validator
            ->boolean('is_terminal')
            ->allowEmptyString('is_terminal');

        return $validator;
    }

    public function findActive(\Cake\ORM\Query\SelectQuery $query, array $options = []): \Cake\ORM\Query\SelectQuery
    {
        return $query->where(['JobStatuses.is_active' => true]);
    }
}
