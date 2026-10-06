<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * LevelRate Entity
 *
 * One payment period of a level. The amount an operator earns for a
 * job assigned this level is taken from the period that covers the
 * job's scheduled date, so rates can be revised over time without
 * touching jobs that were already priced.
 *
 * @property int $id
 * @property int $level_id
 * @property string $payment_amount
 * @property \Cake\I18n\DateTime|null $valid_from
 * @property \Cake\I18n\DateTime|null $valid_to
 * @property \Cake\I18n\DateTime|null $created_at
 * @property \Cake\I18n\DateTime|null $modified_at
 *
 * @property \App\Model\Entity\Level $level
 */
class LevelRate extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'level_id' => true,
        'payment_amount' => true,
        'valid_from' => true,
        'valid_to' => true,
        'created_at' => true,
        'modified_at' => true,
        'level' => true,
    ];

    /**
     * Human readable payment figure, e.g. "1,250.00".
     */
    protected function _getPaymentFormatted(): string
    {
        return number_format((float)$this->payment_amount, 2);
    }

    /**
     * Inclusive display range, e.g. "01 Mar 2026 → 30 Aug 2026".
     * An open end is shown as "open".
     */
    protected function _getPeriodFormatted(): string
    {
        $from = $this->valid_from?->format('d M Y') ?? 'beginning';
        $to = $this->valid_to?->format('d M Y') ?? 'open';

        return $from . ' → ' . $to;
    }

    /**
     * Whether today falls inside this period.
     */
    protected function _getCoversToday(): bool
    {
        $today = new \DateTimeImmutable('today');
        $from = $this->valid_from ? \DateTimeImmutable::createFromInterface($this->valid_from)->setTime(0, 0) : null;
        $to = $this->valid_to ? \DateTimeImmutable::createFromInterface($this->valid_to)->setTime(0, 0) : null;

        if ($from !== null && $today < $from) {
            return false;
        }
        if ($to !== null && $today > $to) {
            return false;
        }

        return true;
    }
}
