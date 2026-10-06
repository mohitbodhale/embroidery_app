<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * One wallet per operator. balance is the running figure, total_earned and
 * total_paid are lifetime counters kept in step with wallet_transactions.
 */
final class CreateOperatorWallets extends BaseMigration
{
    public function change(): void
    {
        if ($this->table('operator_wallets')->exists()) {
            return;
        }

        if (!$this->table('users')->exists()) {
            return;
        }

        $this->table('operator_wallets')
            ->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('balance', 'decimal', [
                'precision' => 12,
                'scale' => 2,
                'null' => false,
                'default' => 0,
            ])
            ->addColumn('total_earned', 'decimal', [
                'precision' => 12,
                'scale' => 2,
                'null' => false,
                'default' => 0,
            ])
            ->addColumn('total_paid', 'decimal', [
                'precision' => 12,
                'scale' => 2,
                'null' => false,
                'default' => 0,
            ])
            ->addColumn('notes', 'text', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addColumn('modified_at', 'datetime', ['null' => true])
            ->addIndex(['user_id'], ['unique' => true, 'name' => 'idx_operator_wallets_user_id'])
            ->create();

        $this->table('operator_wallets')
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_operator_wallets_user_id',
            ])
            ->update();

        // Give every existing operator a zeroed wallet so income can be recorded
        // immediately without a setup step.
        $now = date('Y-m-d H:i:s');
        $this->execute(
            'INSERT INTO operator_wallets (user_id, balance, total_earned, total_paid, created_at, modified_at)
             SELECT u.id, 0, 0, 0, \'' . $now . '\', \'' . $now . '\'
             FROM users u
             WHERE LOWER(u.role) = \'operator\'
             ON CONFLICT (user_id) DO NOTHING'
        );
    }
}