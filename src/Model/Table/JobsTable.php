<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\Event\EventInterface;
use ArrayObject;
use Cake\Routing\Router;
use Cake\ORM\TableRegistry;

use Cake\Datasource\EntityInterface; // ADD THIS IMPORT


/**
 * Jobs Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Operators
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Qcs
 * @property \App\Model\Table\OrganizationsTable&\Cake\ORM\Association\BelongsTo $Organizations
 * @property \App\Model\Table\LevelsTable&\Cake\ORM\Association\BelongsTo $Levels
 * @property \App\Model\Table\JobAttachmentsTable&\Cake\ORM\Association\HasMany $JobAttachments
 * @property \App\Model\Table\JobLogsTable&\Cake\ORM\Association\HasMany $JobLogs
 *
 * @method \App\Model\Entity\Job newEmptyEntity()
 * @method \App\Model\Entity\Job newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Job> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Job get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Job findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Job patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Job> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Job|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Job saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Job>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Job>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Job>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Job> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Job>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Job>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Job>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Job> deleteManyOrFail(iterable $entities, array $options = [])
 */
class JobsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('jobs');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->belongsTo('Operators', [
            'foreignKey' => 'operator_id',
            'className' => 'Users',
            'propertyName' => 'operator',
        ]);
        $this->belongsTo('Qcs', [
            'foreignKey' => 'qc_id',
            'className' => 'Users',
            'propertyName' => 'qc',
        ]);
        $this->belongsTo('Organizations', [
            'foreignKey' => 'organization_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Levels', [
            'foreignKey' => 'level_id',
        ]);
        $this->hasMany('JobAttachments', [
            'foreignKey' => 'job_id',
        ]);
        $this->hasMany('JobLogs', [
            'foreignKey' => 'job_id',
        ]);
        $this->belongsTo('JobStatuses', [
            'foreignKey' => 'status_id',
        ]);

        $this->addBehavior('Timestamp', [
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('job_number')
            ->maxLength('job_number', 50)
            ->allowEmptyString('job_number');

        $validator
            ->scalar('title')
            ->maxLength('title', 200)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('instructions')
            ->allowEmptyString('instructions');

        $validator
            ->scalar('status')
            ->add('status', 'registeredStatus', [
                'rule' => function ($value) {
                    return $this->JobStatuses->exists([
                        'name' => (string)$value,
                        'is_active' => true,
                    ]);
                },
                'message' => __('Select an active job status.'),
            ])
            ->allowEmptyString('status');

        $validator
            ->integer('created_by')
            ->allowEmptyString('created_by');

        $validator
            ->integer('operator_id')
            ->allowEmptyString('operator_id');

        $validator
            ->integer('qc_id')
            ->allowEmptyString('qc_id');

        $validator
            ->date('scheduled_date')
            ->allowEmptyDate('scheduled_date');

        $validator
            ->dateTime('created_at')
            ->allowEmptyDateTime('created_at');

        $validator
            ->dateTime('updated_at')
            ->allowEmptyDateTime('updated_at');

        $validator
            ->integer('organization_id')
            ->allowEmptyString('organization_id');

        $validator
            ->integer('level_id')
            ->allowEmptyString('level_id');

        // `decimal` with no place limit accepts plain integers ("500") as well as
        // decimals; the size is bounded separately below.
        $validator
            ->add('level_payment', 'decimal', [
                'rule' => 'decimal',
                'message' => __('Payment amount must be a number.'),
            ])
            ->allowEmptyString('level_payment')
            ->greaterThanOrEqual('level_payment', 0, __('Payment amount cannot be negative.'))
            ->lessThanOrEqual(
                'level_payment',
                9999999999.99,
                __('Payment amount is too large.')
            );

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        // $rules->add($rules->isUnique(['job_number']), ['errorField' => 'job_number', 'message' => __('This job number already exists')]);
        // $rules->add($rules->existsIn(['operator_id'], 'Operators'), ['errorField' => 'operator_id']);
        // $rules->add($rules->existsIn(['qc_id'], 'Qcs'), ['errorField' => 'qc_id']);
        // $rules->add($rules->existsIn(['status_id'], 'JobStatuses'), ['errorField' => 'status_id']);

        return $rules;
    }

    /**
     * The amount this job earns, computed live.
     *
     * The payment is read from the level's payment period that
     * covers the job's scheduled date, so it always follows the
     * rate schedule the administrator maintains. A level without
     * a matching period falls back to its base payment amount,
     * and a job without a level earns nothing.
     *
     * @param \App\Model\Entity\Job $job Job being priced.
     * @return string|null The payment amount, or null when unpriced.
     */
    public function paymentFor($job): ?string
    {
        if (empty($job->level_id)) {
            return null;
        }

        $rate = TableRegistry::getTableLocator()->get('LevelRates')
            ->rateFor((int)$job->level_id, $job->scheduled_date ?? null);

        if ($rate !== null) {
            return $rate;
        }

        $level = $job->hasValue('level')
            ? $job->level
            : $this->Levels->find()->where(['Levels.id' => (int)$job->level_id])->first();

        return $level !== null ? $level->payment_amount : null;
    }

// 1. Automatically append role-aware filter to all SELECT queries
public function beforeFind(EventInterface $event, SelectQuery $query, ArrayObject $options, bool $primary): void
{
    // Skip filtering for existsIn/rule checks so cross-org referential
    // integrity validation still works when an attachment or log points
    // at a job in a different organization.
    if (!empty($options['checkRules']) || !empty($options['checkExists']) || !empty($options['skipOrgFilter'])) {
        return;
    }

    // If the query already limits by primary key, assume it is a targeted
    // lookup (e.g. get($id)) and skip role/org filtering.
    try {
        $limit = $query->clause('limit');
        if ($limit === 1) {
            return;
        }
    } catch (\Throwable $e) {
        // ignore and continue
    }

    $request = Router::getRequest();
    $session = $request ? $request->getSession() : null;
    $auth = $session ? $session->read('Auth') : null;

    // The Authentication plugin stores the user entity in session.Auth; legacy
    // code may have stored scalar keys. Support both.
    if (is_object($auth)) {
        $orgId  = $auth->organization_id ?? null;
        $role   = strtolower((string)($auth->role ?? ''));
        $userId = $auth->id ?? null;
    } elseif (is_array($auth)) {
        $orgId  = $auth['organization_id'] ?? null;
        $role   = strtolower((string)($auth['role'] ?? ''));
        $userId = $auth['id'] ?? ($auth['user_id'] ?? null);
    } else {
        $orgId = $role = $userId = null;
    }

    // Admins see jobs across all organizations.
    if ($role === 'admin') {
        return;
    }

    switch ($role) {
        case 'operator':
            if (!empty($userId)) {
                 $query->where(['Jobs.operator_id' => $userId]);
            }
            break;
        case 'quality_checker':
            if (!empty($userId)) {
                $query->where(['Jobs.qc_id' => $userId]);
            }
            break;
        case 'production':
            // Production sees jobs that are ready to produce or in production.
            $query->where(['Jobs.status IN' => ['qc_approved', 'in_production']]);
            break;
        case 'scheduler':
            if (!empty($userId)) {
                $query->where(['Jobs.created_by' => $userId]);
            }
            break;
        default:
            if (!empty($orgId)) {
                $query->where(['Jobs.organization_id' => $orgId]);
            }
    }
}

// 2. Automatically assign organization_id before saving new records
public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
{
    // Keep the status_id FK in sync with the legacy `status` enum column
    // so admin reports and joins through JobStatuses stay accurate.
    if (!empty($entity->status)) {
        $status = $this->JobStatuses->find()
            ->where(['name' => (string)$entity->status])
            ->first();
        if ($status) {
            $entity->status_id = $status->id;
        }
    }
}
}
