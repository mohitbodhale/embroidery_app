<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\WalletTransaction;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Append-only ledger of every operator wallet movement.
 */
class WalletTransactionsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('wallet_transactions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('OperatorWallets', [
            'foreignKey' => 'wallet_id',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        $this->belongsTo('Jobs', [
            'foreignKey' => 'job_id',
        ]);
        $this->belongsTo('CreatedBy', [
            'foreignKey' => 'created_by',
            'className' => 'Users',
            'propertyName' => 'creator',
        ]);

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => ['created_at' => 'new'],
            ],
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('wallet_id')
            ->requirePresence('wallet_id', 'create')
            ->notEmptyString('wallet_id');

        $validator
            ->integer('user_id')
            ->requirePresence('user_id', 'create')
            ->notEmptyString('user_id');

        $validator
            ->integer('job_id')
            ->allowEmptyString('job_id');

        $validator
            ->scalar('type')
            ->maxLength('type', 32)
            ->requirePresence('type', 'create')
            ->notEmptyString('type')
            ->inList('type', [
                WalletTransaction::TYPE_EARNING,
                WalletTransaction::TYPE_PAYOUT,
                WalletTransaction::TYPE_ADJUSTMENT,
            ], __('Invalid transaction type.'));

        $validator
            ->decimal('amount', 2)
            ->requirePresence('amount', 'create')
            ->notEmptyString('amount', __('Enter an amount.'));

        $validator
            ->decimal('balance_after', 2)
            ->requirePresence('balance_after', 'create');

        $validator
            ->scalar('notes')
            ->maxLength('notes', 255)
            ->allowEmptyString('notes');

        $validator
            ->scalar('reference')
            ->maxLength('reference', 100)
            ->allowEmptyString('reference');

        $validator
            ->integer('created_by')
            ->allowEmptyString('created_by');

        $validator
            ->dateTime('created_at')
            ->allowEmptyDateTime('created_at');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['wallet_id'], 'OperatorWallets'), ['errorField' => 'wallet_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param array<string, mixed> $options
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findRecent(SelectQuery $query, array $options = []): SelectQuery
    {
        return $query->orderByDesc('WalletTransactions.created_at')
            ->orderByDesc('WalletTransactions.id');
    }

    /**
     * Jobs that already have an income row for this wallet, so the same job is
     * never paid out twice by accident.
     *
     * @return array<int, true>
     */
    public function paidJobIds(int $walletId): array
    {
        $rows = $this->find()
            ->select(['job_id'])
            ->where([
                'wallet_id' => $walletId,
                'type' => WalletTransaction::TYPE_EARNING,
                'job_id IS NOT' => null,
            ])
            ->disableHydration()
            ->all()
            ->toList();

        $ids = [];
        foreach ($rows as $row) {
            $ids[(int)$row['job_id']] = true;
        }

        return $ids;
    }
}