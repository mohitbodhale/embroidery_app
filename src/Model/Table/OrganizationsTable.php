<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class OrganizationsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('organizations');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->hasMany('Users', [
            'foreignKey' => 'organization_id',
        ]);
        $this->hasMany('Jobs', [
            'foreignKey' => 'organization_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        return $validator;
    }
}