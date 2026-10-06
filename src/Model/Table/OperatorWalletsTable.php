<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\OperatorWallet;
use App\Model\Entity\WalletTransaction;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Exception\RecordNotFoundException;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * One wallet per operator, moved only through recordMovement() so the stored
 * balance and the ledger can never drift apart.
 */
class OperatorWalletsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('operator_wallets');
        $this->setDisplayField('user_id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('WalletTransactions', [
            'foreignKey' => 'wallet_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created_at' => 'new',
                    'modified_at' => 'always',
                ],
            ],
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('user_id')
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->decimal('balance', 2)
            ->requirePresence('balance', 'create');

        $validator
            ->decimal('total_earned', 2)
            ->requirePresence('total_earned', 'create');

        $validator
            ->decimal('total_paid', 2)
            ->requirePresence('total_paid', 'create');

        $validator
            ->scalar('notes')
            ->maxLength('notes', 255)
            ->allowEmptyString('notes');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->isUnique(['user_id']), [
            'errorField' => 'user_id',
            'message' => __('This operator already has a wallet.'),
        ]);

        return $rules;
    }

    /**
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param array<string, mixed> $options
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findWithOperators(SelectQuery $query, array $options = []): SelectQuery
    {
        return $query->contain(['Users'])->orderByAsc('Users.name');
    }

    /**
     * Fetch an operator's wallet, creating a zeroed one on first use.
     *
     * @return \App\Model\Entity\OperatorWallet
     */
    public function getOrCreate(int $userId): OperatorWallet
    {
        /** @var \App\Model\Entity\OperatorWallet|null $wallet */
        $wallet = $this->find()
            ->where(['OperatorWallets.user_id' => $userId])
            ->contain(['Users'])
            ->first();

        if ($wallet) {
            return $wallet;
        }

        $wallet = $this->newEmptyEntity();
        $wallet->user_id = $userId;
        $wallet->balance = 0;
        $wallet->total_earned = 0;
        $wallet->total_paid = 0;

        return $this->saveOrFail($wallet, ['atomic' => false]);
    }

    /**
     * Fetch an existing wallet without creating one.
     *
     * @return \App\Model\Entity\OperatorWallet
     * @throws \Cake\ORM\Exception\RecordNotFoundException When the operator has no wallet yet.
     */
    public function getForUser(int $userId): OperatorWallet
    {
        /** @var \App\Model\Entity\OperatorWallet $wallet */
        $wallet = $this->find()
            ->where(['OperatorWallets.user_id' => $userId])
            ->contain(['Users'])
            ->firstOrFail();

        return $wallet;
    }

    /**
     * Apply a signed amount to an operator's wallet and append the ledger row.
     *
     * Both writes share one transaction and the row is locked first, so
     * concurrent entries cannot lose an update.
     *
     * @param int $userId Operator whose wallet moves.
     * @param string $type earning|payout|adjustment
     * @param float $amount Signed amount: positive credits, negative debits.
     * @param array<string, mixed> $options job_id, notes, reference, created_by
     * @return \App\Model\Entity\WalletTransaction|null The saved ledger row, or null when nothing was saved.
     */
    public function recordMovement(
        int $userId,
        string $type,
        float $amount,
        array $options = []
    ): ?WalletTransaction {
        $amount = round($amount, 2);
        if ($amount == 0.0) {
            return null;
        }

        if ($type === WalletTransaction::TYPE_PAYOUT && $amount > 0) {
            // Payouts are entered as a positive figure and stored as a debit.
            $amount = -$amount;
        }

        $connection = $this->getConnection();

        /** @var \App\Model\Entity\WalletTransaction|null $transaction */
        $transaction = null;

        $connection->transactional(function () use ($userId, $type, $amount, $options, &$transaction) {
            $wallet = $this->lockWallet($userId);

            $balance = round((float)$wallet->balance + $amount, 2);
            $wallet->balance = $balance;
            if ($type === WalletTransaction::TYPE_PAYOUT) {
                $wallet->total_paid = round((float)$wallet->total_paid + abs($amount), 2);
            } else {
                $wallet->total_earned = round((float)$wallet->total_earned + $amount, 2);
            }

            if (!$this->save($wallet)) {
                throw new \RuntimeException(
                    __('The wallet balance could not be updated.')
                );
            }

            $transaction = $this->WalletTransactions->newEmptyEntity();
            $transaction->wallet_id = $wallet->id;
            $transaction->user_id = $userId;
            $transaction->job_id = $options['job_id'] ?? null;
            $transaction->type = $type;
            $transaction->amount = $amount;
            $transaction->balance_after = $balance;
            $transaction->notes = $options['notes'] ?? null;
            $transaction->reference = $options['reference'] ?? null;
            $transaction->created_by = $options['created_by'] ?? null;

            if (!$this->WalletTransactions->save($transaction)) {
                throw new \RuntimeException(
                    __('The wallet entry could not be saved: {0}', implode(
                        ', ',
                        $this->WalletTransactions->getErrors()
                    ))
                );
            }
        });

        return $transaction;
    }

    /**
     * Read the wallet row for update, creating it when an operator is new.
     *
     * @return \App\Model\Entity\OperatorWallet
     */
    private function lockWallet(int $userId): OperatorWallet
    {
        /** @var \App\Model\Entity\OperatorWallet|null $wallet */
        $wallet = $this->find()
            ->where(['OperatorWallets.user_id' => $userId])
            ->epilog('FOR UPDATE')
            ->first();

        if ($wallet) {
            return $wallet;
        }

        return $this->getOrCreate($userId);
    }
}