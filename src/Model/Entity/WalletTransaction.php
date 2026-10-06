<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * WalletTransaction Entity
 *
 * One immutable ledger row. `amount` is signed: positive credits the wallet,
 * negative debits it.
 *
 * @property int $id
 * @property int $wallet_id
 * @property int $user_id
 * @property int|null $job_id
 * @property string $type
 * @property string $amount
 * @property string $balance_after
 * @property string|null $notes
 * @property string|null $reference
 * @property int|null $created_by
 * @property \Cake\I18n\DateTime|null $created_at
 *
 * @property \App\Model\Entity\OperatorWallet $operator_wallet
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Job|null $job
 * @property \App\Model\Entity\User|null $creator
 */
class WalletTransaction extends Entity
{
    public const TYPE_EARNING = 'earning';
    public const TYPE_PAYOUT = 'payout';
    public const TYPE_ADJUSTMENT = 'adjustment';

    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'wallet_id' => true,
        'user_id' => true,
        'job_id' => true,
        'type' => true,
        'amount' => true,
        'balance_after' => true,
        'notes' => true,
        'reference' => true,
        'created_by' => true,
        'created_at' => true,
        'operator_wallet' => true,
        'user' => true,
        'job' => true,
        'creator' => true,
    ];

    protected function _getAmountFormatted(): string
    {
        return number_format((float)$this->amount, 2);
    }

    protected function _getBalanceAfterFormatted(): string
    {
        return number_format((float)$this->balance_after, 2);
    }

    /**
     * True when the row increases the wallet balance.
     */
    protected function _getIsCredit(): bool
    {
        return (float)$this->amount >= 0;
    }
}