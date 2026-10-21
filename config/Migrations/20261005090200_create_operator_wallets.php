<?php
declare(strict_types=1);

use Migrations\BaseMigration;
use Cake\Database\Driver\Mysql;
use Cake\Database\Driver\Postgres;

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
        // immediately without a setup step. Use DB-agnostic upsert.
        $this->seedOperatorWallets($this->getAdapter()->getConnection()->getDriver());
    }

    /**
     * Insert zero-balance wallets for all operator users.
     * Uses INSERT IGNORE (MySQL) or ON CONFLICT DO NOTHING (PostgreSQL).
     */
    private function seedOperatorWallets($driver): void
    {
        $now = date('Y-m-d H:i:s');

        if ($driver instanceof Postgres) {
            $this->execute(
                'INSERT INTO operator_wallets (user_id, balance, total_earned, total_paid, created_at, modified_at)
                 SELECT u.id, 0, 0, 0, ?, ?
                 FROM users u
                 WHERE LOWER(u.role) = \'operator\'
                 ON CONFLICT (user_id) DO NOTHING',
                [$now, $now]
            );
        } elseif ($driver instanceof Mysql) {
            $this->execute(
                'INSERT IGNORE INTO operator_wallets (user_id, balance, total_earned, total_paid, created_at, modified_at)
                 SELECT u.id, 0, 0, 0, ?, ?
                 FROM users u
                 WHERE LOWER(u.role) = \'operator\'',
                [$now, $now]
            );
        } else {
            // Fallback: check and insert individually (SQLite, etc.)
            $operators = $this->fetchAll("SELECT id FROM users WHERE LOWER(role) = 'operator'");
            foreach ($operators as $op) {
                $exists = $this->fetchOne('SELECT 1 FROM operator_wallets WHERE user_id = ?', [$op['id']]);
                if (!$exists) {
                    $this->execute(
                        'INSERT INTO operator_wallets (user_id, balance, total_earned, total_paid, created_at, modified_at) VALUES (?, 0, 0, 0, ?, ?)',
                        [$op['id'], $now, $now]
                    );
                }
            }
        }
    }
}