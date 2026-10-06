<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Payment periods of a level.
 *
 * A level keeps any number of these rows. The amount a job earns is
 * read from the period that covers the job's scheduled date, which
 * lets the administrator revise a rate on a given date without
 * rewriting jobs that were already priced.
 *
 * `valid_from` and `valid_to` are inclusive. A null bound is open,
 * so a period may run from the beginning of time or forever.
 */
class LevelRatesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('level_rates');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Levels', [
            'foreignKey' => 'level_id',
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
            ->integer('level_id')
            ->requirePresence('level_id', 'create')
            ->notEmptyString('level_id');

        // `decimal` with no place limit accepts plain integers ("200")
        // as well as decimals; the size is bounded separately below.
        $validator
            ->add('payment_amount', 'decimal', [
                'rule' => 'decimal',
                'message' => __('Payment amount must be a number.'),
            ])
            ->requirePresence('payment_amount', 'create')
            ->notEmptyString('payment_amount')
            ->greaterThanOrEqual('payment_amount', 0, __('Payment amount cannot be negative.'))
            ->lessThanOrEqual(
                'payment_amount',
                9999999999.99,
                __('Payment amount is too large.')
            );

        $validator
            ->date('valid_from')
            ->allowEmptyDate('valid_from');

        $validator
            ->date('valid_to')
            ->allowEmptyDate('valid_to');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['level_id'], 'Levels'), [
            'errorField' => 'level_id',
        ]);

        return $rules;
    }

    /**
     * The amount in force for a level on a given date.
     *
     * A period matches when the date falls between its `valid_from`
     * and `valid_to` (both inclusive); a null bound means open.
     * When several periods match, the one with the latest start
     * wins, so a narrow correction can override a wide default.
     *
     * @param int $levelId Level to price.
     * @param \DateTimeInterface|string|null $date Job date; today when omitted.
     * @return string|null The payment amount, or null when nothing matches.
     */
    public function rateFor(int $levelId, \DateTimeInterface|string|null $date = null): ?string
    {
        // The whole of the given day is in scope: a period may start
        // any time on its start day and end any time on its end day.
        $dayStart = $this->toDay($date) . ' 00:00:00';
        $dayEnd = $this->toDay($date) . ' 23:59:59';

        $matches = $this->find()
            ->where(['LevelRates.level_id' => $levelId])
            ->where(function (QueryExpression $exp) use ($dayStart, $dayEnd) {
                return $exp->and([
                    $exp->or([
                        ['LevelRates.valid_from IS' => null],
                        ['LevelRates.valid_from <=' => $dayEnd],
                    ]),
                    $exp->or([
                        ['LevelRates.valid_to IS' => null],
                        ['LevelRates.valid_to >=' => $dayStart],
                    ]),
                ]);
            })
            ->orderByAsc('LevelRates.id')
            ->all();

        // Several periods can cover the same date (an open-ended
        // default plus a dated correction). The most specific one
        // wins: a period with a start date beats an open-ended
        // one, and the latest start beats an earlier start.
        $best = null;
        $bestFrom = null;
        foreach ($matches as $rate) {
            $from = $rate->valid_from
                ? $rate->valid_from->format('Y-m-d')
                : '0000-00-00';
            if ($best === null || $from >= $bestFrom) {
                $best = $rate;
                $bestFrom = $from;
            }
        }

        return $best?->payment_amount;
    }

    /**
     * Periods of a level that overlap a given range, optionally
     * excluding one row. Two periods of the same level may not
     * overlap, otherwise a date would match more than one rate.
     *
     * @param int $levelId Level being edited.
     * @param \DateTimeInterface|string|null $from Inclusive start, null for open.
     * @param \DateTimeInterface|string|null $to Inclusive end, null for open.
     * @param int|null $excludeId Skip this row (the one being edited).
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findOverlapping(
        int $levelId,
        \DateTimeInterface|string|null $from,
        \DateTimeInterface|string|null $to,
        ?int $excludeId = null
    ): SelectQuery {
        // Unbounded placeholders so the comparison stays well formed
        // even when one side of the range is left open.
        $rangeStart = $this->toDay($from ?? '1970-01-01') . ' 00:00:00';
        $rangeEnd = $this->toDay($to ?? '2199-12-31') . ' 23:59:59';

        $query = $this->find()->where(['LevelRates.level_id' => $levelId]);
        if ($excludeId !== null) {
            $query = $query->where(['LevelRates.id !=' => $excludeId]);
        }

        // Ranges [a1,a2] and [b1,b2] overlap when a1 <= b2 and b1 <= a2,
        // with a null bound treated as unbounded.
        return $query->where(function (QueryExpression $exp) use ($rangeStart, $rangeEnd) {
            return $exp->and([
                $exp->or([
                    ['LevelRates.valid_to IS' => null],
                    ['LevelRates.valid_to >=' => $rangeStart],
                ]),
                $exp->or([
                    ['LevelRates.valid_from IS' => null],
                    ['LevelRates.valid_from <=' => $rangeEnd],
                ]),
            ]);
        });
    }

    /**
     * Normalize a date or datetime to a "Y-m-d" calendar day.
     *
     * @param \DateTimeInterface|string|null $date
     * @return string
     */
    private function toDay(\DateTimeInterface|string|null $date): string
    {
        if ($date === null) {
            $date = new \DateTimeImmutable('today');
        }
        if (is_string($date)) {
            $date = new \DateTimeImmutable($date);
        }

        return $date->format('Y-m-d');
    }
}
