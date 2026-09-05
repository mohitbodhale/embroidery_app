<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

class PasswordResetTokensTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('password_reset_tokens');
        $this->setPrimaryKey('id');
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
    }
}
