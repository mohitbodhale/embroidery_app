<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('Organizations', [
            'foreignKey' => 'organization_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('JobLogs', [
            'foreignKey' => 'user_id',
        ]);
        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
            'propertyName' => 'role_entry',
        ]);
        $this->hasOne('UserDetails', [
            'foreignKey' => 'user_id',
            'dependent' => true,
        ]);
        $this->hasMany('PasswordResetTokens', [
            'foreignKey' => 'user_id',
            'dependent' => true,
        ]);
    }

    public function beforeSave(\Cake\Event\EventInterface $event, \Cake\Datasource\EntityInterface $entity, \ArrayObject $options): void
    {
        // Keep the role_id FK in sync with the legacy `role` enum column so
        // every place that reads the user (sidebar, policies, dashboards)
        // can also join through the master table.
        if (!empty($entity->role)) {
            $role = $this->Roles->find()
                ->where(['name' => (string)$entity->role])
                ->first();
            if ($role) {
                $entity->role_id = $role->id;
            }
        }
    }

    public function beforeDelete(\Cake\Event\EventInterface $event, \Cake\Datasource\EntityInterface $entity, \ArrayObject $options): void
    {
        $userId = $entity->id;
        if (!$userId) {
            return;
        }

        $connection = $this->getConnection();
        $connection->transactional(function () use ($connection, $userId) {
            $connection->execute('UPDATE jobs SET created_by = NULL WHERE created_by = :id', ['id' => $userId])->execute();
            $connection->execute('UPDATE jobs SET operator_id = NULL WHERE operator_id = :id', ['id' => $userId])->execute();
            $connection->execute('UPDATE jobs SET qc_id = NULL WHERE qc_id = :id', ['id' => $userId])->execute();
            $connection->execute('UPDATE jobs SET assigned_to = NULL WHERE assigned_to = :id', ['id' => $userId])->execute();
            $connection->execute('UPDATE jobs SET assigned_by = NULL WHERE assigned_by = :id', ['id' => $userId])->execute();

            $connection->execute('UPDATE job_attachments SET uploaded_by = NULL WHERE uploaded_by = :id', ['id' => $userId])->execute();
            $connection->execute('UPDATE job_logs SET user_id = NULL WHERE user_id = :id', ['id' => $userId])->execute();

            $connection->execute('UPDATE job_assignments SET assigned_to = NULL WHERE assigned_to = :id', ['id' => $userId])->execute();
            $connection->execute('UPDATE job_assignments SET assigned_by = NULL WHERE assigned_by = :id', ['id' => $userId])->execute();

            $connection->execute('UPDATE job_qc_reviews SET qc_user_id = NULL WHERE qc_user_id = :id', ['id' => $userId])->execute();
            $connection->execute('UPDATE job_qc_reviews SET assigned_to_user_id = NULL WHERE assigned_to_user_id = :id', ['id' => $userId])->execute();
        });
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 100)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->email('email')
            ->requirePresence('email', true)
            ->notEmptyString('email', null, 'create');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->notEmptyString('password', null, 'create');

        $validator
            ->scalar('role')
            ->requirePresence('role', 'create')
            ->notEmptyString('role')
            ->add('role', 'validRole', [
                'rule' => function ($value) {
                    if (empty($value)) {
                        return false;
                    }
                    return $this->Roles->exists(['name' => (string)$value]);
                },
                'message' => 'The provided value must be a valid role.',
            ]);

        $validator
            ->integer('organization_id')
            ->notEmptyString('organization_id');

        $validator
            ->boolean('must_change_password')
            ->allowEmptyString('must_change_password');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['email', 'organization_id']), [
            'errorField' => 'email', 
            'message' => __('This combination of email and organization_id already exists')
        ]);
        $rules->add($rules->existsIn(['organization_id'], 'Organizations'), ['errorField' => 'organization_id']);

        return $rules;
    }
}
