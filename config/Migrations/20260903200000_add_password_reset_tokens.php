<?php
declare(strict_types=1);

use Migrations\BaseMigration;

final class AddPasswordResetTokens extends BaseMigration
{
    public function change(): void
    {
        if (!$this->table('password_reset_tokens')->exists()) {
            $this->table('password_reset_tokens')
                ->addColumn('user_id', 'integer', ['null' => false])
                ->addColumn('token', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('expires_at', 'datetime', ['null' => false])
                ->addColumn('used', 'boolean', ['null' => false, 'default' => false])
                ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['token'], ['unique' => true])
                ->addIndex(['user_id'])
                ->addIndex(['expires_at'])
                ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->create();
        }
    }
}
