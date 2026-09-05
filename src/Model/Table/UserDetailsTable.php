<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class UserDetailsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('user_details');
        $this->setPrimaryKey('id');
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('user_id')
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->maxLength('phone', 20)
            ->allowEmptyString('phone');

        $validator
            ->allowEmptyString('bio');

        $validator
            ->maxLength('avatar', 255)
            ->allowEmptyString('avatar');

        $validator
            ->maxLength('website', 255)
            ->allowEmptyString('website')
            ->url('website');

        $validator
            ->maxLength('location', 100)
            ->allowEmptyString('location');

        return $validator;
    }
}
