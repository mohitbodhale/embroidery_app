<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Append-only ledger for operator wallet movements.
 *
 * amount is signed: positive credits the wallet (income/adjustment up),
 * negative debits it (a payment made to the operator). balance_after stores the
 * resulting balance so any historical row can be verified on its own.
 */
final class CreateWalletTransactions extends BaseMigration
{
    public function change(): void
    {
        if ($this->table('wallet_transactions')->exists()) {
            return;
        }

        $this->table('wallet_transactions')
            ->addColumn('wallet_id', 'integer', ['null' => false])
            ->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('job_id', 'integer', ['null' => true])
            ->addColumn('type', 'string', ['limit' => 32, 'null' => false, 'default' => 'earning'])
            ->addColumn('amount', 'decimal', [
                'precision' => 12,
                'scale' => 2,
                'null' => false,
                'default' => 0,
            ])
            ->addColumn('balance_after', 'decimal', [
                'precision' => 12,
                'scale' => 2,
                'null' => false,
                'default' => 0,
            ])
            ->addColumn('notes', 'text', ['null' => true])
            ->addColumn('reference', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('created_by', 'integer', ['null' => true])
            ->addColumn('created_at', 'datetime', ['null' => true])
            ->addIndex(['wallet_id'], ['name' => 'idx_wallet_transactions_wallet_id'])
            ->addIndex(['job_id'], ['name' => 'idx_wallet_transactions_job_id'])
            ->addIndex(['type'], ['name' => 'idx_wallet_transactions_type'])
            ->addIndex(['user_id'], ['name' => 'idx_wallet_transactions_user_id'])
            ->addIndex(['created_at'], ['name' => 'idx_wallet_transactions_created_at'])
            ->create();

        $this->table('wallet_transactions')
            ->addForeignKey('wallet_id', 'operator_wallets', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_wallet_transactions_wallet_id',
            ])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_wallet_transactions_user_id',
            ])
            ->addForeignKey('job_id', 'jobs', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_wallet_transactions_job_id',
            ])
            ->addForeignKey('created_by', 'users', 'id', [
                'delete' => 'SET_NULL',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_wallet_transactions_created_by',
            ])
            ->update();
    }
}