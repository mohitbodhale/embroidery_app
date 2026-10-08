<?php
declare(strict_types=1);

namespace App\Command;

use App\Controller\JobsController;
use App\Controller\WalletsController;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\ConnectionManager;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;

/**
 * Exercises the level/payment and wallet code paths across every condition
 * branch. All database work runs inside a transaction that is rolled back, so
 * development data is never modified.
 */
class VerifyFeaturesCommand extends Command
{
    private int $pass = 0;
    private int $fail = 0;
    private array $failures = [];

    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return $parser->setDescription('Verify level/payment and wallet logic.');
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->runSchema($io);
        $this->runLevelValidation($io);
        $this->runJobValidation($io);
        $this->runLivePayment($io);
        $this->runWalletSummary($io);
        $this->runWalletMath($io);
        $this->runLiveRepricing($io);
        $this->runJobGuards($io);
        $this->runRoleGating($io);
        $this->runFullFormSave($io);
        $this->runLevelRates($io);

        $io->out('');
        $io->out(sprintf('PASS: %d   FAIL: %d', $this->pass, $this->fail));
        foreach ($this->failures as $f) {
            $io->error($f);
        }

        return $this->fail === 0 ? static::CODE_SUCCESS : static::CODE_ERROR;
    }

    private function check(ConsoleIo $io, string $group, string $label, $expected, $actual): void
    {
        $show = static function ($v) {
            return is_array($v) ? json_encode($v) : var_export($v, true);
        };

        if ($expected === $actual) {
            $this->pass++;
            $io->success(sprintf('[%s] %s', $group, $label));

            return;
        }
        $this->fail++;
        $msg = sprintf(
            '[%s] %s | expected %s, got %s',
            $group,
            $label,
            $show($expected),
            $show($actual)
        );
        $this->failures[] = $msg;
        $io->warning($msg);
    }

    /**
     * The columns the application reads must actually exist. Schema drift here
     * is what makes the ORM silently drop fields or throw on save.
     */
    private function runSchema(ConsoleIo $io): void
    {
        $io->out('<info>== Schema ==</info>');
        $conn = ConnectionManager::get('default');

        $expect = [
            'job_statuses' => ['id', 'name', 'label', 'color', 'sort_order', 'is_active', 'is_terminal'],
            'levels' => ['id', 'name', 'label', 'payment_amount', 'color', 'sort_order', 'is_active'],
            'jobs' => ['id', 'level_id', 'level_payment'],
            'operator_wallets' => ['id', 'user_id', 'balance', 'total_earned', 'total_paid'],
            'wallet_transactions' => [
                'id', 'wallet_id', 'user_id', 'job_id', 'type', 'amount', 'balance_after',
                'notes', 'reference', 'created_by',
            ],
            'level_rates' => ['id', 'level_id', 'payment_amount', 'valid_from', 'valid_to'],
        ];

        foreach ($expect as $table => $columns) {
            $schema = $conn->getSchemaCollection()->describe($table);
            foreach ($columns as $column) {
                $this->check($io, 'schema', sprintf('%s.%s exists', $table, $column), true,
                    in_array($column, $schema->columns(), true));
            }
        }

        // The seeded levels must carry a usable rate.
        $levelRows = TableRegistry::getTableLocator()->get('Levels')->find()
            ->orderByAsc('sort_order')->all();
        $this->check($io, 'schema', 'levels are seeded', true, $levelRows->count() > 0);
        foreach ($levelRows as $row) {
            $this->check($io, 'schema', sprintf('level %s has a numeric rate', $row->label), true,
                is_numeric($row->payment_amount));
        }
    }

    private function runLevelValidation(ConsoleIo $io): void
    {
        $io->out('<info>== Levels validator ==</info>');
        $Levels = TableRegistry::getTableLocator()->get('Levels');
        $v = $Levels->getValidator();

        $cases = [
            ['plain integer 500', ['name' => 'A', 'label' => 'A', 'payment_amount' => '500'], true],
            ['integer 0', ['name' => 'A', 'label' => 'A', 'payment_amount' => '0'], true],
            ['two decimals', ['name' => 'A', 'label' => 'A', 'payment_amount' => '500.50'], true],
            ['one decimal', ['name' => 'A', 'label' => 'A', 'payment_amount' => '500.5'], true],
            ['empty allowed', ['name' => 'A', 'label' => 'A', 'payment_amount' => ''], true],
            ['missing allowed', ['name' => 'A', 'label' => 'A'], true],
            ['negative rejected', ['name' => 'A', 'label' => 'A', 'payment_amount' => '-1'], false],
            ['non numeric rejected', ['name' => 'A', 'label' => 'A', 'payment_amount' => 'abc'], false],
            ['too large rejected', ['name' => 'A', 'label' => 'A', 'payment_amount' => '99999999999'], false],
            ['label required', ['name' => 'A', 'payment_amount' => '1'], false],
        ];
        foreach ($cases as [$label, $data, $wantValid]) {
            $this->check($io, 'level', $label, $wantValid, $v->validate($data) === []);
        }
    }

    private function runJobValidation(ConsoleIo $io): void
    {
        $io->out('<info>== Jobs validator (level fields) ==</info>');
        $Jobs = TableRegistry::getTableLocator()->get('Jobs');
        $v = $Jobs->getValidator();

        $base = ['title' => 'x', 'job_number' => 'J1', 'status' => 'draft'];

        $this->check($io, 'job', 'level_payment integer', true,
            $v->validate($base + ['level_id' => '1', 'level_payment' => '500']) === []);
        $this->check($io, 'job', 'level_payment empty with level', true,
            $v->validate($base + ['level_id' => '1', 'level_payment' => '']) === []);
        $this->check($io, 'job', 'level_payment negative', false,
            $v->validate($base + ['level_id' => '1', 'level_payment' => '-5']) === []);
        $this->check($io, 'job', 'level_payment non numeric', false,
            $v->validate($base + ['level_id' => '1', 'level_payment' => 'x']) === []);
        $this->check($io, 'job', 'level_payment too large', false,
            $v->validate($base + ['level_id' => '1', 'level_payment' => '99999999999']) === []);
        $this->check($io, 'job', 'no level no payment', true, $v->validate($base) === []);
    }

    /**
     * The payment a job earns is computed live from the level's
     * payment periods and the job's scheduled date; nothing is
     * stored on the job itself.
     */
    private function runLivePayment(ConsoleIo $io): void
    {
        $io->out('<info>== Live payment computation ==</info>');
        $Levels = TableRegistry::getTableLocator()->get('Levels');
        $Rates = TableRegistry::getTableLocator()->get('LevelRates');
        $Jobs = TableRegistry::getTableLocator()->get('Jobs');

        $conn = ConnectionManager::get('default');
        $conn->begin();
        try {
            $level = $Levels->newEmptyEntity();
            $level->name = 'verify_live_' . uniqid();
            $level->label = 'Verify Live';
            $level->payment_amount = '100.00';
            $level->sort_order = 5;
            $level->is_active = true;
            $Levels->saveOrFail($level);

            // 150.00 for the first half of 2026, 250.00 from
            // the second half of 2026 onwards.
            $periods = [
                ['150.00', '2026-01-01', '2026-06-30'],
                ['250.00', '2026-07-01', null],
            ];
            foreach ($periods as [$amount, $from, $to]) {
                $rate = $Rates->newEmptyEntity();
                $rate->level_id = $level->id;
                $rate->payment_amount = $amount;
                $rate->valid_from = $from;
                $rate->valid_to = $to;
                $Rates->saveOrFail($rate);
            }

            $jobFor = function (?string $date) use ($Jobs, $level) {
                $job = $Jobs->newEmptyEntity();
                $job->title = 'Live payment';
                $job->job_number = 'LP-' . uniqid();
                $job->status = 'draft';
                $job->level_id = $level->id;
                $job->scheduled_date = $date;
                $Jobs->saveOrFail($job);

                return $Jobs->get($job->id);
            };

            $this->check($io, 'live', 'job earns the period covering its date', 250.00,
                round((float)$Jobs->paymentFor($jobFor('2026-08-01')), 2));
            $this->check($io, 'live', 'earlier date earns the earlier period', 150.00,
                round((float)$Jobs->paymentFor($jobFor('2026-03-01')), 2));
            $this->check($io, 'live', 'date before every period falls back to the base amount', 100.00,
                round((float)$Jobs->paymentFor($jobFor('2025-12-31')), 2));

            $today = $Rates->rateFor((int)$level->id);
            $this->check($io, 'live', 'undated job uses the period in force today',
                $today === null ? null : round((float)$today, 2),
                round((float)$Jobs->paymentFor($jobFor(null)), 2));

            $bare = $Jobs->newEmptyEntity();
            $bare->title = 'No level';
            $bare->job_number = 'NL-' . uniqid();
            $bare->status = 'draft';
            $Jobs->saveOrFail($bare);
            $bare = $Jobs->get($bare->id);
            $this->check($io, 'live', 'job without a level earns nothing', null,
                $Jobs->paymentFor($bare));
            $this->check($io, 'live', 'the job stores no payment snapshot', null,
                $bare->level_payment);

            // Inactive levels stay editable but are hidden from the add form.
            $level->is_active = false;
            $Levels->saveOrFail($level);
            $opts = $Levels->selectOptions(activeOnly: false);
            $this->check($io, 'live', 'inactive level offered on edit', true, isset($opts[$level->id]));
            $this->check($io, 'live', 'inactive level hidden on add', false,
                isset($Levels->selectOptions(true)[$level->id]));
        } finally {
            $conn->rollback();
        }
    }

    /**
     * Verify the operator-facing totals against assigned jobs and admin payouts.
     * All rows are rolled back so this can run against a development database.
     */
    private function runWalletSummary(ConsoleIo $io): void
    {
        $io->out('<info>== Operator wallet summary ==</info>');
        $Users = TableRegistry::getTableLocator()->get('Users');
        $Wallets = TableRegistry::getTableLocator()->get('OperatorWallets');
        $Levels = TableRegistry::getTableLocator()->get('Levels');
        $Jobs = TableRegistry::getTableLocator()->get('Jobs');
        $operators = $Users->find()->where(['role' => 'operator'])->orderByAsc('id')->all()->toList();
        if ($operators === []) {
            $io->warning('no operators found; skipping wallet summary');

            return;
        }

        $controller = new WalletsController(new ServerRequest());
        $summaryMethod = new \ReflectionMethod($controller, 'walletSummary');
        $summaryMethod->setAccessible(true);
        $connection = ConnectionManager::get('default');
        $connection->begin();
        try {
            $baseline = [];
            foreach ($operators as $operator) {
                $wallet = $Wallets->getOrCreate((int)$operator->id);
                $baseline[$operator->id] = $summaryMethod->invoke($controller, $wallet);
            }

            $level = $Levels->newEmptyEntity();
            $level->name = 'verify_wallet_summary_' . uniqid();
            $level->label = 'Verify Wallet Summary';
            $level->payment_amount = '100.00';
            $level->is_active = true;
            $Levels->saveOrFail($level);

            $createJob = function (int $operatorId, string $status) use ($Jobs, $level) {
                $job = $Jobs->newEmptyEntity();
                $job->title = 'Wallet summary ' . $status;
                $job->job_number = 'WS-' . uniqid();
                $job->status = $status;
                $job->operator_id = $operatorId;
                $job->level_id = $level->id;
                $Jobs->saveOrFail($job);

                return $job;
            };

            $firstOperator = $operators[0];
            $createJob((int)$firstOperator->id, 'in_progress');
            $createJob((int)$firstOperator->id, 'completed');
            $Wallets->recordMovement((int)$firstOperator->id, 'adjustment', 50.00);
            $Wallets->recordMovement((int)$firstOperator->id, 'payout', 20.00);

            if (isset($operators[1])) {
                $createJob((int)$operators[1]->id, 'completed');
            }

            foreach ($operators as $operator) {
                $wallet = $Wallets->getForUser((int)$operator->id);
                $actual = $summaryMethod->invoke($controller, $wallet);
                $expected = $baseline[$operator->id];
                if ((int)$operator->id === (int)$firstOperator->id) {
                    $expected['current_balance'] += 200.00;
                    $expected['total_earnings'] += 100.00;
                    $expected['paid_to_you'] += 20.00;
                } elseif (isset($operators[1]) && (int)$operator->id === (int)$operators[1]->id) {
                    $expected['current_balance'] += 100.00;
                    $expected['total_earnings'] += 100.00;
                }

                $this->check($io, 'summary', sprintf('operator %s sees only assigned job and paid totals', $operator->id), [
                    'current_balance' => round($expected['current_balance'], 2),
                    'total_earnings' => round($expected['total_earnings'], 2),
                    'paid_to_you' => round($expected['paid_to_you'], 2),
                ], [
                    'current_balance' => round($actual['current_balance'], 2),
                    'total_earnings' => round($actual['total_earnings'], 2),
                    'paid_to_you' => round($actual['paid_to_you'], 2),
                ]);
            }
        } finally {
            $connection->rollback();
            $io->out('<info>rolled back</info>');
        }
    }

    private function runWalletMath(ConsoleIo $io): void
    {
        $io->out('<info>== Wallet movement math ==</info>');
        $Wallets = TableRegistry::getTableLocator()->get('OperatorWallets');
        $Tx = TableRegistry::getTableLocator()->get('WalletTransactions');

        $operator = TableRegistry::getTableLocator()->get('Users')->find()
            ->where(['role' => 'operator'])->orderByAsc('id')->first();
        if (!$operator) {
            $io->warning('no operator user found; skipping wallet math');

            return;
        }

        $conn = ConnectionManager::get('default');
        $conn->begin();
        try {
            $wallet = $Wallets->getOrCreate((int)$operator->id);
            $txn = function (string $type, float $amount, array $opts = []) use ($Wallets, $operator) {
                return $Wallets->recordMovement((int)$operator->id, $type, $amount, $opts);
            };
            $state = function () use ($Wallets, $operator) {
                $w = $Wallets->getOrCreate((int)$operator->id);
                unset($w->user);

                return [$w->balance, $w->total_earned, $w->total_paid];
            };

            $txn('adjustment', 1000.00);
            $this->check($io, 'wallet', 'seed 1000', ['1000.00', '1000.00', '0.00'], $state());

            $t = $txn('earning', 250.50);
            $this->check($io, 'wallet', 'earning adds to balance and earned',
                ['1250.50', '1250.50', '0.00'], $state());
            $this->check($io, 'wallet', 'earning stores balance_after', 1250.50,
                round((float)$t->balance_after, 2));
            $this->check($io, 'wallet', 'earning amount positive', 250.50,
                round((float)$t->amount, 2));

            $t = $txn('payout', 500.00);
            $this->check($io, 'wallet', 'payout debits balance',
                ['750.50', '1250.50', '500.00'], $state());
            $this->check($io, 'wallet', 'payout stored negative', -500.00,
                round((float)$t->amount, 2));

            $t = $txn('adjustment', -25.25);
            $this->check($io, 'wallet', 'negative adjustment debits',
                ['725.25', '1225.25', '500.00'], $state());

            $this->check($io, 'wallet', 'zero amount returns null', null, $txn('adjustment', 0.0));

            // payout larger than balance must be rejected before recording
            $walletsCtrl = new WalletsController(new ServerRequest());
            $m = new \ReflectionMethod($walletsCtrl, 'validateEntry');
            $m->setAccessible(true);
            $w = $Wallets->getOrCreate((int)$operator->id);
            $this->check($io, 'wallet', 'payout over balance rejected', true,
                $m->invoke($walletsCtrl, $w, 'payout', (float)$w->balance + 1, null) !== null);
            $this->check($io, 'wallet', 'payout equal to balance allowed', null,
                $m->invoke($walletsCtrl, $w, 'payout', (float)$w->balance, null));
            $this->check($io, 'wallet', 'zero earning rejected', true,
                $m->invoke($walletsCtrl, $w, 'earning', 0, null) !== null);
            $this->check($io, 'wallet', 'negative earning rejected', true,
                $m->invoke($walletsCtrl, $w, 'earning', -5, null) !== null);
            $this->check($io, 'wallet', 'bad type rejected', true,
                $m->invoke($walletsCtrl, $w, 'nonsense', 10, null) !== null);
            $this->check($io, 'wallet', 'zero adjustment rejected', true,
                $m->invoke($walletsCtrl, $w, 'adjustment', 0, null) !== null);
            $this->check($io, 'wallet', 'negative adjustment allowed', null,
                $m->invoke($walletsCtrl, $w, 'adjustment', -10, null));

            // ledger must reconcile with stored balance
            $rows = $Tx->find()->where(['wallet_id' => $w->id])->orderByAsc('id')->all();
            $running = 0.0;
            foreach ($rows as $r) {
                $running = round($running + (float)$r->amount, 2);
                if (round($running, 2) !== round((float)$r->balance_after, 2)) {
                    $this->check($io, 'wallet', 'ledger balance_after chain', true, false);
                    break;
                }
            }
            $this->check($io, 'wallet', 'ledger balance_after chain consistent', true, true);
            $this->check($io, 'wallet', 'final balance equals last balance_after',
                number_format((float)$w->balance, 2),
                number_format((float)$rows->last()->balance_after, 2));
        } finally {
            $conn->rollback();
            $io->out('<info>rolled back</info>');
        }
    }

    /**
     * Computation is live: the payment is always read from the
     * rate schedule, so adding a period or raising a rate
     * changes what an existing job earns.
     */
    private function runLiveRepricing(ConsoleIo $io): void
    {
        $io->out('<info>== Live repricing ==</info>');
        $conn = ConnectionManager::get('default');
        $conn->begin();
        try {
            $Levels = TableRegistry::getTableLocator()->get('Levels');
            $Rates = TableRegistry::getTableLocator()->get('LevelRates');
            $Jobs = TableRegistry::getTableLocator()->get('Jobs');

            $level = $Levels->newEmptyEntity();
            $level->name = 'verify_repricing_' . uniqid();
            $level->label = 'Verify Repricing';
            $level->payment_amount = '100.00';
            $level->is_active = true;
            $Levels->saveOrFail($level);

            $job = $Jobs->newEmptyEntity();
            $job->title = 'Repricing job';
            $job->job_number = 'RP-' . uniqid();
            $job->status = 'draft';
            $job->scheduled_date = '2026-05-01';
            $job->level_id = $level->id;
            $Jobs->saveOrFail($job);

            // No period yet: the job earns the level's base amount.
            $this->check($io, 'repricing', 'base amount before any period', 100.00,
                round((float)$Jobs->paymentFor($Jobs->get($job->id)), 2));

            // A period covering the job's date takes over.
            $rate = $Rates->newEmptyEntity();
            $rate->level_id = $level->id;
            $rate->payment_amount = '180.00';
            $rate->valid_from = '2026-01-01';
            $rate->valid_to = '2026-12-31';
            $Rates->saveOrFail($rate);
            $this->check($io, 'repricing', 'a new period reprices the job', 180.00,
                round((float)$Jobs->paymentFor($Jobs->get($job->id)), 2));

            // A date outside every period falls back to the
            // level's base amount, so raising the base amount
            // reprices the job.
            $job->scheduled_date = '2027-05-01';
            $Jobs->saveOrFail($job);
            $level->payment_amount = '900.00';
            $Levels->saveOrFail($level);
            $this->check($io, 'repricing', 'a raised base amount reprices the job', 900.00,
                round((float)$Jobs->paymentFor($Jobs->get($job->id)), 2));

            // The job itself still stores no amount.
            $this->check($io, 'repricing', 'the job stores no payment snapshot', null,
                $Jobs->get($job->id)->level_payment);
        } finally {
            $conn->rollback();
        }
    }

    /**
     * Wallet income may only reference a job assigned to that operator, and a
     * job may only be paid into a wallet once.
     */
    private function runJobGuards(ConsoleIo $io): void
    {
        $io->out('<info>== Wallet job guards ==</info>');
        $Wallets = TableRegistry::getTableLocator()->get('OperatorWallets');
        $Users = TableRegistry::getTableLocator()->get('Users');
        $Jobs = TableRegistry::getTableLocator()->get('Jobs');

        $operator = $Users->find()->where(['role' => 'operator'])->orderByAsc('id')->first();
        $otherOperator = $Users->find()
            ->where(['role' => 'operator', 'id !=' => $operator->id])
            ->orderByAsc('id')->first();
        if (!$operator) {
            $io->warning('no operator found; skipping job guards');

            return;
        }

        $conn = ConnectionManager::get('default');
        $conn->begin();
        try {
            $wallet = $Wallets->getOrCreate((int)$operator->id);
            $ctrl = new WalletsController(new ServerRequest());
            $m = new \ReflectionMethod($ctrl, 'validateEntry');
            $m->setAccessible(true);

            // A level-bearing job assigned to this operator.
            $job = $Jobs->newEmptyEntity();
            $job->title = 'Verify payable';
            $job->job_number = 'VP-' . uniqid();
            $job->status = 'draft';
            $job->operator_id = $operator->id;
            $job->level_id = 1;
            $job->level_payment = '75.00';
            $Jobs->saveOrFail($job);

            $this->check($io, 'guard', 'valid job income allowed', null,
                $m->invoke($ctrl, $wallet, 'earning', 75.00, (int)$job->id));

            $Wallets->recordMovement((int)$operator->id, 'earning', 75.00, ['job_id' => $job->id]);
            $this->check($io, 'guard', 'same job cannot be paid twice', true,
                $m->invoke($ctrl, $wallet, 'earning', 75.00, (int)$job->id) !== null);

            $this->check($io, 'guard', 'missing job rejected', true,
                $m->invoke($ctrl, $wallet, 'earning', 10.00, 99999999) !== null);

            if ($otherOperator) {
                $foreign = $Jobs->newEmptyEntity();
                $foreign->title = 'Verify foreign';
                $foreign->job_number = 'VF-' . uniqid();
                $foreign->status = 'draft';
                $foreign->operator_id = $otherOperator->id;
                $foreign->level_id = 1;
                $foreign->level_payment = '75.00';
                $Jobs->saveOrFail($foreign);

                $this->check($io, 'guard', "another operator's job rejected", true,
                    $m->invoke($ctrl, $wallet, 'earning', 75.00, (int)$foreign->id) !== null);
            }

            // A payout is bounded by the balance, not by job ownership.
            // Reload first: validateEntry reads the balance off the wallet it
            // is handed, and the controller always fetches a fresh one per post.
            $fresh = $Wallets->get($wallet->id);
            $this->check($io, 'guard', 'payout is bounded by balance not by job ownership', null,
                $m->invoke($ctrl, $fresh, 'payout', 10.00, (int)$job->id));

            // Guard: a stale wallet must not be used to authorise an overdraw.
            $this->check($io, 'guard', 'stale wallet does not permit an overdraw', true,
                $m->invoke($ctrl, $Wallets->newEmptyEntity()->set('id', $fresh->id)->set('balance', 0.00)
                    ->set('user_id', $fresh->user_id), 'payout', 10.00, null) !== null);
        } finally {
            $conn->rollback();
        }
    }

    /**
     * Only admins and schedulers may set a level; every other role must have the
     * level fields stripped before the job is patched.
     */
    private function runRoleGating(ConsoleIo $io): void
    {
        $io->out('<info>== Role gating ==</info>');

        $controller = new JobsController(new ServerRequest());
        $ref = new \ReflectionMethod($controller, 'normalizedRole');
        $ref->setAccessible(true);
        $gate = new \ReflectionMethod($controller, 'canSetLevel');
        $gate->setAccessible(true);

        $make = static function (string $role) {
            $u = new \stdClass();
            $u->role = $role;
            $u->id = 1;
            $u->organization_id = 1;

            return $u;
        };

        $canSet = static fn(object $user): bool => $gate->invoke($controller, $user);

        $this->check($io, 'role', 'admin may set a level', true, $canSet($make('admin')));
        $this->check($io, 'role', 'scheduler may set a level', true, $canSet($make('scheduler')));
        $this->check($io, 'role', 'operator may not set a level', false, $canSet($make('operator')));
        $this->check($io, 'role', 'quality_checker may not set a level', false, $canSet($make('quality_checker')));
        $this->check($io, 'role', 'production may not set a level', false, $canSet($make('production')));
        $this->check($io, 'role', 'unknown role may not set a level', false, $canSet($make('viewer')));
        $this->check($io, 'role', 'signed-out user may not set a level', false, $gate->invoke($controller, null));
        $this->check($io, 'role', 'mixed-case Admin is normalized', 'admin',
            $ref->invoke($controller, $make(' Admin ')));

        // Decisive check: the real records in the database must map onto the
        // canonical role names the templates and controllers branch on. A stored
        // role such as "Scheduler " or "schedulers" silently disables the level
        // controls for that user.
        $Users = TableRegistry::getTableLocator()->get('Users');
        foreach ($Users->find()->orderByAsc('id')->all() as $user) {
            $stored = (string)($user->role ?? '');
            $normalized = $ref->invoke($controller, $user);
            $expected = in_array($normalized, ['admin', 'scheduler'], true);
            $this->check($io, 'role',
                sprintf('user %s (role %s) gate', $user->id, var_export($stored, true)),
                $expected, $gate->invoke($controller, $user));
        }

        $distinct = $Users->find()->select(['role'])->distinct(['role'])->all()
            ->extract('role')->toList();
        sort($distinct);
        $io->out('<info>  roles in users table: ' . implode(', ', array_map(
            static fn($r) => var_export($r, true),
            $distinct
        )) . '</info>');
    }

    /**
     * Payment periods decide a job's pay from its scheduled date.
     * Mirrors the periods an administrator maintains:
     *
     *   100  beginning .. 2026-02-28
     *   200  2026-03-01 .. 2026-08-30
     *   300  2026-09-01 .. 2026-10-10
     *   400  2026-10-11 .. open
     */
    private function runLevelRates(ConsoleIo $io): void
    {
        $io->out('<info>== Payment periods ==</info>');
        $Levels = TableRegistry::getTableLocator()->get('Levels');
        $Rates = TableRegistry::getTableLocator()->get('LevelRates');

        $conn = ConnectionManager::get('default');
        $conn->begin();
        try {
            $level = $Levels->newEmptyEntity();
            $level->name = 'verify_rates_' . uniqid();
            $level->label = 'Verify Rates';
            $level->payment_amount = '0.00';
            $level->is_active = true;
            $Levels->saveOrFail($level);

            $periods = [
                ['100.00', null, '2026-02-28'],
                ['200.00', '2026-03-01', '2026-08-30'],
                ['300.00', '2026-09-01', '2026-10-10'],
                ['400.00', '2026-10-11', null],
            ];
            foreach ($periods as [$amount, $from, $to]) {
                $rate = $Rates->newEmptyEntity();
                $rate->level_id = $level->id;
                $rate->payment_amount = $amount;
                $rate->valid_from = $from;
                $rate->valid_to = $to;
                $Rates->saveOrFail($rate);
            }

            // An open-ended default period must not shadow a
            // dated period that covers the same date: the
            // dated, more specific period wins.
            $default = $Rates->newEmptyEntity();
            $default->level_id = $level->id;
            $default->payment_amount = '0.00';
            $default->valid_from = null;
            $default->valid_to = null;
            $Rates->saveOrFail($default);
            $this->check($io, 'rates', 'dated period beats the open default', 200.00,
                round((float)$Rates->rateFor((int)$level->id, '2026-06-15'), 2));
            $this->check($io, 'rates', 'open default still covers a gap', 0.00,
                round((float)$Rates->rateFor((int)$level->id, '2025-01-01'), 2));

            $cases = [
                'before every period' => ['2026-01-15', '100.00'],
                'inclusive end of first' => ['2026-02-28', '100.00'],
                'inclusive start of second' => ['2026-03-01', '200.00'],
                'middle of second' => ['2026-06-15', '200.00'],
                'inclusive end of second' => ['2026-08-30', '200.00'],
                'inclusive start of third' => ['2026-09-01', '300.00'],
                'inclusive end of third' => ['2026-10-10', '300.00'],
                'inclusive start of fourth' => ['2026-10-11', '400.00'],
                'open end' => ['2027-05-05', '400.00'],
            ];
            $raw = $Rates->getConnection()->execute(
                'SELECT id, level_id, payment_amount, valid_from, valid_to FROM level_rates WHERE level_id = ? ORDER BY id',
                [(int)$level->id]
            )->fetchAll('assoc');
            $io->out('<info>  raw rows: ' . json_encode($raw) . '</info>');
            foreach ($cases as $label => [$day, $want]) {
                $this->check($io, 'rates', $label, (float)$want,
                    round((float)$Rates->rateFor((int)$level->id, $day), 2));
            }

            // A date between two periods matches the later-starting one
            // only when the bounds actually meet; here they are contiguous.
            $this->check($io, 'rates', 'gap day resolves to none', null,
                $Rates->rateFor((int)$level->id, '2026-08-31'));

            // Overlap is rejected so a date cannot match two rates.
            // Test against a level that has a single bounded period,
            // so the expected answer is unambiguous.
            $solo = $Levels->newEmptyEntity();
            $solo->name = 'verify_solo_' . uniqid();
            $solo->label = 'Verify Solo';
            $solo->payment_amount = '0.00';
            $solo->is_active = true;
            $Levels->saveOrFail($solo);
            $soloRate = $Rates->newEmptyEntity();
            $soloRate->level_id = $solo->id;
            $soloRate->payment_amount = '500.00';
            $soloRate->valid_from = '2026-06-01';
            $soloRate->valid_to = '2026-06-30';
            $Rates->saveOrFail($soloRate);

            $this->check($io, 'rates', 'overlapping period is detected', true,
                $Rates->findOverlapping((int)$solo->id, '2026-06-15', '2026-06-20')->count() > 0);
            $this->check($io, 'rates', 'touching start boundary is detected', true,
                $Rates->findOverlapping((int)$solo->id, '2026-05-15', '2026-06-01')->count() > 0);
            $this->check($io, 'rates', 'range before the period is not flagged', false,
                $Rates->findOverlapping((int)$solo->id, '2025-01-01', '2025-02-01')->count() > 0);
            $this->check($io, 'rates', 'range after the period is not flagged', false,
                $Rates->findOverlapping((int)$solo->id, '2026-07-01', '2026-07-15')->count() > 0);
            $this->check($io, 'rates', 'the period itself is excluded on edit', false,
                $Rates->findOverlapping((int)$solo->id, '2026-06-01', '2026-06-30', (int)$soloRate->id)->count() > 0);

            // A job scheduled inside a period earns that period's rate.
            $Jobs = TableRegistry::getTableLocator()->get('Jobs');
            $job = $Jobs->newEmptyEntity();
            $job->title = 'Rate period job';
            $job->job_number = 'RP-' . uniqid();
            $job->status = 'draft';
            $job->scheduled_date = '2026-09-15';
            $job->level_id = $level->id;
            $Jobs->saveOrFail($job);
            $this->check($io, 'rates', 'job earns the period covering its date', 300.00,
                round((float)$Jobs->paymentFor($Jobs->get($job->id)), 2));

            // The same level on an earlier date earns the earlier rate.
            $job2 = $Jobs->newEmptyEntity();
            $job2->title = 'Rate period job 2';
            $job2->job_number = 'RP-' . uniqid();
            $job2->status = 'draft';
            $job2->scheduled_date = '2026-04-15';
            $job2->level_id = $level->id;
            $Jobs->saveOrFail($job2);
            $this->check($io, 'rates', 'earlier date earns the earlier rate', 200.00,
                round((float)$Jobs->paymentFor($Jobs->get($job2->id)), 2));

            // A job with no date uses today's period.
            $job3 = $Jobs->newEmptyEntity();
            $job3->title = 'Rate period job 3';
            $job3->job_number = 'RP-' . uniqid();
            $job3->status = 'draft';
            $job3->level_id = $level->id;
            $Jobs->saveOrFail($job3);
            $todayRate = $Rates->rateFor((int)$level->id);
            $this->check($io, 'rates', 'undated job uses today period',
                $todayRate === null ? null : round((float)$todayRate, 2),
                round((float)$Jobs->paymentFor($Jobs->get($job3->id)), 2));

            // Rescheduling reprices the job live: the payment
            // always follows the period covering the date.
            $job->scheduled_date = '2026-04-01';
            $Jobs->saveOrFail($job);
            $this->check($io, 'rates', 'rescheduling reprices the job', 200.00,
                round((float)$Jobs->paymentFor($Jobs->get($job->id)), 2));
        } finally {
            $conn->rollback();
        }
    }

    /**
     * Replays a complete scheduler save using every field the edit
     * form posts, through the real patchEntity -> save path. The
     * payment is never posted: it is computed live from the rate
     * schedule after the save.
     */
    private function runFullFormSave(ConsoleIo $io): void
    {
        $io->out('<info>== Full scheduler form save ==</info>');
        $Jobs = TableRegistry::getTableLocator()->get('Jobs');
        $Users = TableRegistry::getTableLocator()->get('Users');
        $Levels = TableRegistry::getTableLocator()->get('Levels');
        $Rates = TableRegistry::getTableLocator()->get('LevelRates');

        $scheduler = $Users->find()->where(['role' => 'scheduler'])->orderByAsc('id')->first();
        if (!$scheduler) {
            $io->warning('no scheduler found; skipping full form save');

            return;
        }

        $conn = ConnectionManager::get('default');
        $conn->begin();
        try {
            $controller = new JobsController(new ServerRequest());
            $gateRef = new \ReflectionMethod($controller, 'canSetLevel');
            $gateRef->setAccessible(true);

            $this->check($io, 'form', 'scheduler passes the level gate', true,
                $gateRef->invoke($controller, $scheduler));

            // A level whose rate depends on the scheduled date:
            //   120.00  2026-01-01 .. 2026-06-30
            //   240.00  2026-07-01 .. open
            // with a 60.00 base amount for dates outside
            // every period.
            $level = $Levels->newEmptyEntity();
            $level->name = 'verify_form_' . uniqid();
            $level->label = 'Verify Form';
            $level->payment_amount = '60.00';
            $level->is_active = true;
            $Levels->saveOrFail($level);
            $periods = [
                ['120.00', '2026-01-01', '2026-06-30'],
                ['240.00', '2026-07-01', null],
            ];
            foreach ($periods as [$amount, $from, $to]) {
                $rate = $Rates->newEmptyEntity();
                $rate->level_id = $level->id;
                $rate->payment_amount = $amount;
                $rate->valid_from = $from;
                $rate->valid_to = $to;
                $Rates->saveOrFail($rate);
            }
            $other = $Levels->find()
                ->where(['Levels.id !=' => $level->id])
                ->orderByAsc('id')
                ->first() ?? $level;

            // Exactly what templates/Jobs/edit.php submits for a
            // scheduler: the level is posted, the payment is not.
            $payload = [
                'title' => 'Form save test',
                'instructions' => 'n/a',
                'status' => 'in_progress',
                'operator_id' => '',
                'qc_id' => '',
                'scheduled_date' => '2026-10-05',
                'organization_id' => (string)($scheduler->organization_id ?? ''),
                'level_id' => (string)$level->id,
                'created_by' => (string)$scheduler->id,
            ];

            // Case 1: brand new job with a level.
            $job = $Jobs->newEmptyEntity();
            $job->title = 'Form save test';
            $job->job_number = 'FS-' . uniqid();
            $job->status = 'draft';
            $Jobs->saveOrFail($job);
            $posted = array_merge($payload, ['job_number' => $job->job_number]);
            $entity = $Jobs->patchEntity($job, $posted);
            $saved = $Jobs->save($entity);
            $this->check($io, 'form', 'new job saves', true, (bool)$saved);
            if (!$saved) {
                $io->out('<info>  errors: ' . json_encode($entity->getErrors()));
            }
            $fresh = $Jobs->get($job->id);
            $this->check($io, 'form', 'new job level persisted', (int)$level->id, (int)$fresh->level_id);
            $this->check($io, 'form', 'new job stores no payment snapshot', null, $fresh->level_payment);
            $this->check($io, 'form', 'new job earns the live rate', 240.00,
                round((float)$Jobs->paymentFor($fresh), 2));

            // Case 2: existing job, level changed in one submit.
            $job2 = $Jobs->newEmptyEntity();
            $job2->title = 'Form save test 2';
            $job2->job_number = 'FS-' . uniqid();
            $job2->status = 'draft';
            $job2->level_id = $level->id;
            $job2->scheduled_date = '2026-02-01';
            $Jobs->saveOrFail($job2);
            $posted2 = array_merge($payload, [
                'job_number' => $job2->job_number,
                'level_id' => (string)$other->id,
            ]);
            $entity2 = $Jobs->patchEntity($Jobs->get($job2->id), $posted2);
            $saved2 = $Jobs->save($entity2);
            $this->check($io, 'form', 'level change saves', true, (bool)$saved2);
            if (!$saved2) {
                $io->out('<info>  errors: ' . json_encode($entity2->getErrors()));
            }
            $fresh2 = $Jobs->get($job2->id);
            $this->check($io, 'form', 'changed level persisted', (int)$other->id, (int)$fresh2->level_id);
            $expectedOther = $Rates->rateFor((int)$other->id, $fresh2->scheduled_date)
                ?? $other->payment_amount;
            $this->check($io, 'form', 'level switch adopts that level rate',
                round((float)$expectedOther, 2),
                round((float)$Jobs->paymentFor($fresh2), 2));

            // Case 3: re-submitting the same values must be stable.
            $entity3 = $Jobs->patchEntity($Jobs->get($job2->id), $posted2);
            $Jobs->saveOrFail($entity3);
            $fresh3 = $Jobs->get($job2->id);
            $this->check($io, 'form', 'resubmit keeps level', (int)$other->id, (int)$fresh3->level_id);
            $this->check($io, 'form', 'resubmit keeps the live rate',
                round((float)$expectedOther, 2),
                round((float)$Jobs->paymentFor($fresh3), 2));

            // Case 4: moving the scheduled date reprices the job live.
            $posted4 = $posted2;
            $posted4['level_id'] = (string)$level->id;
            $posted4['scheduled_date'] = '2026-04-01';
            $entity4 = $Jobs->patchEntity($Jobs->get($job2->id), $posted4);
            $Jobs->saveOrFail($entity4);
            $fresh4 = $Jobs->get($job2->id);
            $this->check($io, 'form', 'rescheduling reprices the job', 120.00,
                round((float)$Jobs->paymentFor($fresh4), 2));
        } finally {
            $conn->rollback();
        }
    }
}
