<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Levels master: pay grades whose payment amount drives operator earnings.
 */
class LevelsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('levels');
        $this->setDisplayField('label');
        $this->setPrimaryKey('id');

        $this->hasMany('Jobs', [
            'foreignKey' => 'level_id',
        ]);

        // Payment periods: the amount in force depends on the job's
        // scheduled date, so rates are kept as dated intervals.
        $this->hasMany('LevelRates', [
            'foreignKey' => 'level_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
            'sort' => ['LevelRates.valid_from' => 'ASC'],
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
            ->scalar('name')
            ->maxLength('name', 64)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('label')
            ->maxLength('label', 128)
            ->requirePresence('label', 'create')
            ->notEmptyString('label');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        // `decimal` with no place limit accepts plain integers ("500") as well as
        // decimals; the size is bounded separately below.
        $validator
            ->add('payment_amount', 'decimal', [
                'rule' => 'decimal',
                'message' => __('Payment amount must be a number.'),
            ])
            ->allowEmptyString('payment_amount')
            ->greaterThanOrEqual('payment_amount', 0, __('Payment amount cannot be negative.'))
            ->lessThanOrEqual(
                'payment_amount',
                9999999999.99,
                __('Payment amount is too large.')
            );

        $validator
            ->scalar('color')
            ->maxLength('color', 16)
            ->allowEmptyString('color');

        $validator
            ->integer('sort_order')
            ->allowEmptyString('sort_order');

        $validator
            ->boolean('is_active')
            ->allowEmptyString('is_active');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['name']), [
            'errorField' => 'name',
            'message' => __('This level name is already in use.'),
        ]);

        return $rules;
    }

    /**
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param array<string, mixed> $options
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findActive(SelectQuery $query, array $options = []): SelectQuery
    {
        return $query->where(['Levels.is_active' => true]);
    }

    /**
     * Level options for select boxes: "Level 3 (1,250.00)".
     * The figure is the rate in force today.
     *
     * @return array<int|string, string>
     */
    public function selectOptions(bool $activeOnly = true): array
    {
        $query = $this->find()->orderByAsc('sort_order')->orderByAsc('label');
        if ($activeOnly) {
            $query = $query->where(['Levels.is_active' => true]);
        }

        $current = $this->currentRates();
        $options = [];
        foreach ($query->all() as $level) {
            $amount = $current[$level->id] ?? (float)$level->payment_amount;
            $options[$level->id] = sprintf(
                '%s (%s)',
                $level->label,
                number_format($amount, 2)
            );
        }

        return $options;
    }

    /**
     * Level id => rate in force today, read from the payment
     * periods. A level without a matching period is absent, so
     * callers fall back to its base payment amount.
     *
     * @return array<int|string, float>
     */
    public function currentRates(): array
    {
        $today = (new \DateTimeImmutable('today'))->format('Y-m-d');
        $best = [];
        foreach ($this->LevelRates->find()->all() as $rate) {
            $from = $rate->valid_from?->format('Y-m-d') ?? '0000-00-00';
            $to = $rate->valid_to?->format('Y-m-d') ?? '9999-12-31';
            if ($from > $today || $to < $today) {
                continue;
            }
            // The period with the latest start wins, matching
            // LevelRatesTable::rateFor().
            if (!isset($best[$rate->level_id]) || $from >= $best[$rate->level_id][0]) {
                $best[$rate->level_id] = [$from, (float)$rate->payment_amount];
            }
        }

        $out = [];
        foreach ($best as $levelId => [$from, $amount]) {
            $out[$levelId] = $amount;
        }

        return $out;
    }

    /**
     * Payment schedule for the job forms: every level with its base
     * amount and all its payment periods, so a form can compute the
     * rate covering any scheduled date without a round trip.
     *
     * @param bool $activeOnly Skip inactive levels.
     * @return array<int|string, array{base: float, periods: array<int, array{from: string|null, to: string|null, amount: float}>}
     */
    public function rateSchedule(bool $activeOnly = true): array
    {
        $query = $this->find()->orderByAsc('sort_order')->orderByAsc('label');
        if ($activeOnly) {
            $query = $query->where(['Levels.is_active' => true]);
        }

        $periods = [];
        foreach ($this->LevelRates->find()->all() as $rate) {
            $periods[$rate->level_id][] = [
                'from' => $rate->valid_from?->format('Y-m-d'),
                'to' => $rate->valid_to?->format('Y-m-d'),
                'amount' => (float)$rate->payment_amount,
            ];
        }

        $schedule = [];
        foreach ($query->all() as $level) {
            $schedule[$level->id] = [
                'base' => (float)$level->payment_amount,
                'periods' => $periods[$level->id] ?? [],
            ];
        }

        return $schedule;
    }
}