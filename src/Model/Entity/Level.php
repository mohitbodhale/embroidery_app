<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Level Entity
 *
 * A pay grade. `payment_amount` is what an operator earns for one job assigned
 * this level; Jobs keep their own snapshot so historical earnings stay stable
 * when a level's rate is later changed.
 *
 * @property int $id
 * @property string $name
 * @property string $label
 * @property string|null $description
 * @property string $payment_amount
 * @property string|null $color
 * @property int|null $sort_order
 * @property bool $is_active
 * @property \Cake\I18n\DateTime|null $created_at
 * @property \Cake\I18n\DateTime|null $modified_at
 *
 * @property \App\Model\Entity\Job[] $jobs
 */
class Level extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'label' => true,
        'description' => true,
        'payment_amount' => true,
        'color' => true,
        'sort_order' => true,
        'is_active' => true,
        'created_at' => true,
        'modified_at' => true,
        'jobs' => true,
    ];

    /**
     * Human readable payment figure, e.g. "1,250.00".
     */
    protected function _getPaymentFormatted(): string
    {
        return number_format((float)$this->payment_amount, 2);
    }
}