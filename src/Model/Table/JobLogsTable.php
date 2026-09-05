<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * JobLogs Model
 *
 * @method \App\Model\Entity\JobLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\JobLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\JobLog|false save(\Cake\ORM\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\JobLog saveOrFail(\Cake\ORM\EntityInterface $entity, $options = [])
 */
class JobLogsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('job_logs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Jobs', [
            'foreignKey' => 'job_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->integer('job_id')
            ->requirePresence('job_id', 'create')
            ->notEmptyString('job_id');

        $validator
            ->integer('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->scalar('action')
            ->maxLength('action', 255)
            ->requirePresence('action', 'create')
            ->notEmptyString('action');

        $validator
            ->scalar('comments')
            ->allowEmptyString('comments');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['job_id'], 'Jobs'), ['errorField' => 'job_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
