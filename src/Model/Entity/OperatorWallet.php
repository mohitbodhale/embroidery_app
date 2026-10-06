<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * OperatorWallet Entity
 *
 * @property int $id
 * @property int $user_id
 * @property string $balance
 * @property string $total_earned
 * @property string $total_paid
 * @property string|null $notes
 * @property \Cake\I18n\DateTime|null $created_at
 * @property \Cake\I18n\DateTime|null $modified_at
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\WalletTransaction[] $wallet_transactions
 */
class OperatorWallet extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'balance' => true,
        'total_earned' => true,
        'total_paid' => true,
        'notes' => true,
        'created_at' => true,
        'modified_at' => true,
        'user' => true,
        'wallet_transactions' => true,
    ];

    protected function _getBalanceFormatted(): string
    {
        return number_format((float)$this->balance, 2);
    }

    protected function _getTotalEarnedFormatted(): string
    {
        return number_format((float)$this->total_earned, 2);
    }

    protected function _getTotalPaidFormatted(): string
    {
        return number_format((float)$this->total_paid, 2);
    }
}