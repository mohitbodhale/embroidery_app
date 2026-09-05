<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * JobAttachments Model
 *
 * @property \App\Model\Table\JobsTable&\Cake\ORM\Association\BelongsTo $Jobs
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $UploadedBy
 */
class JobAttachmentsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('job_attachments');
        $this->setDisplayField('file_name');
        $this->setPrimaryKey('id');

        $this->belongsTo('Jobs', [
            'foreignKey' => 'job_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('UploadedBy', [
            'className' => 'Users',
            'foreignKey' => 'uploaded_by',
            'propertyName' => 'uploaded_by_user',
        ]);

        $this->addBehavior('Timestamp', [
            'createdAt' => 'created_at',
            'updatedAt' => false,
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('job_id')
            ->requirePresence('job_id', 'create')
            ->notEmptyString('job_id');

        $validator
            ->integer('uploaded_by')
            ->allowEmptyString('uploaded_by');

        $validator
            ->scalar('file_type')
            ->maxLength('file_type', 255)
            ->allowEmptyString('file_type');

        $validator
            ->scalar('file_path')
            ->maxLength('file_path', 1024)
            ->requirePresence('file_path', 'create')
            ->notEmptyString('file_path');

        $validator
            ->scalar('file_name')
            ->maxLength('file_name', 512)
            ->requirePresence('file_name', 'create')
            ->notEmptyString('file_name');

        $validator
            ->integer('file_size')
            ->allowEmptyString('file_size');

        $validator
            ->scalar('mime_type')
            ->maxLength('mime_type', 255)
            ->allowEmptyString('mime_type');

        $validator
            ->scalar('comments')
            ->allowEmptyString('comments');

        $validator
            ->dateTime('created_at')
            ->allowEmptyDateTime('created_at');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['job_id'], 'Jobs'), ['errorField' => 'job_id']);
        $rules->add($rules->existsIn(['uploaded_by'], 'UploadedBy'), ['errorField' => 'uploaded_by']);

        return $rules;
    }

    public function beforeSave(\Cake\Event\EventInterface $event, \Cake\Datasource\EntityInterface $entity, \ArrayObject $options): void
    {
        if ($entity->isNew() && empty($entity->uploaded_by)) {
            $request = \Cake\Routing\Router::getRequest();
            if ($request) {
                $identity = $request->getAttribute('identity');
                $entity->uploaded_by = $identity ? $identity->getIdentifier() : null;
            }
        }
    }
}
