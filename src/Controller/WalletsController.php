<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\WalletTransaction;
use Cake\Http\Exception\ForbiddenException;

/**
 * Operator wallets.
 *
 * Administrators manage wallets and record income, adjustments and payouts.
 * Operators can open their own wallet read-only via Wallets::my.
 *
 * Income is entered manually on purpose: the amount is the live
 * rate for the job's level and scheduled date, and only an
 * administrator decides when a job has actually been paid.
 */
class WalletsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->OperatorWallets = $this->fetchTable('OperatorWallets');
        $this->WalletTransactions = $this->fetchTable('WalletTransactions');
        $this->Jobs = $this->fetchTable('Jobs');
    }

    /**
     * All operator wallets with balances.
     */
    public function index()
    {
        $this->requireRole(['admin']);

        // Operators created after the wallet migration need a wallet row before
        // income can be recorded, so create it on first visit instead of hiding
        // them from the list.
        $operatorIds = $this->fetchTable('Users')
            ->find()
            ->select(['id'])
            ->where(['Users.role' => 'operator'])
            ->all()
            ->extract('id')
            ->toList();

        foreach ($operatorIds as $operatorId) {
            $this->OperatorWallets->getOrCreate((int)$operatorId);
        }

        $wallets = $this->OperatorWallets->find()
            ->contain(['Users'])
            ->orderByAsc('Users.name')
            ->all();

        $this->set(compact('wallets'));
    }

    /**
     * The signed-in operator's own wallet, read-only.
     */
    public function my()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $role = $this->normalizedRole($user);
        if ($role === 'admin') {
            return $this->redirect(['action' => 'index']);
        }
        if ($role !== 'operator') {
            throw new ForbiddenException(__('Only operators have a wallet.'));
        }

        $wallet = $this->OperatorWallets->getOrCreate((int)$user->id);
        $transactions = $this->paginate($this->ledgerQuery($wallet->id));
        $analytics = $this->walletAnalytics((int)$wallet->id);
        $summary = $this->walletSummary($wallet);

        $this->set(compact('wallet', 'transactions', 'analytics', 'summary'));
        $this->render('view');
    }

    /**
     * One wallet with its full ledger and the income entry form.
     */
    public function view($id = null)
    {
        $this->requireRole(['admin']);

        $wallet = $this->OperatorWallets->get($id, contain: ['Users']);
        $transactions = $this->paginate($this->ledgerQuery($wallet->id));
        $analytics = $this->walletAnalytics((int)$wallet->id);
        $summary = $this->walletSummary($wallet);

        $this->set(compact('wallet', 'transactions', 'analytics', 'summary'));
        $this->set('entry', $this->WalletTransactions->newEmptyEntity());
        $this->set('payableJobs', $this->payableJobs($wallet));
    }

    /**
     * Export one operator's ledger, or all ledgers for administrators.
     * Operators cannot select another wallet, even by changing the URL.
     *
     * @param string|null $id Wallet id for an administrator, omitted for all.
     * @return \Cake\Http\Response
     */
    public function export($id = null)
    {
        $this->request->allowMethod(['get']);
        $user = $this->getCurrentUser();
        if (!$user) {
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $role = $this->normalizedRole($user);
        $query = $this->WalletTransactions->find()
            ->contain(['Users', 'Jobs.Levels'])
            ->orderByAsc('WalletTransactions.wallet_id')
            ->orderByAsc('WalletTransactions.id');
        $filename = 'wallet_ledger_' . date('Y-m-d') . '.csv';

        if ($role === 'admin') {
            if ($id !== null) {
                $wallet = $this->OperatorWallets->get($id);
                $query->where(['WalletTransactions.wallet_id' => $wallet->id]);
                $filename = 'wallet_' . $wallet->id . '_ledger_' . date('Y-m-d') . '.csv';
            }
        } elseif ($role === 'operator') {
            if ($id !== null) {
                throw new ForbiddenException(__('Operators can only export their own wallet.'));
            }
            $wallet = $this->OperatorWallets->getOrCreate((int)$user->id);
            $query->where(['WalletTransactions.wallet_id' => $wallet->id]);
            $filename = 'my_wallet_ledger_' . date('Y-m-d') . '.csv';
        } else {
            throw new ForbiddenException(__('Only administrators and operators can export wallets.'));
        }

        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new \RuntimeException('Unable to create the wallet export.');
        }
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, [
            'Wallet ID', 'Operator', 'Transaction ID', 'Date', 'Type', 'Job Number',
            'Job Title', 'Level', 'Amount', 'Balance After', 'Reference', 'Notes',
        ]);
        foreach ($query->all() as $transaction) {
            $job = $transaction->job;
            $level = $job?->level;
            fputcsv($stream, [
                $transaction->wallet_id,
                $this->safeCsvText($transaction->user?->name),
                $transaction->id,
                $transaction->created_at?->format('Y-m-d H:i:s') ?? '',
                $transaction->type,
                $this->safeCsvText($job?->job_number),
                $this->safeCsvText($job?->title),
                $this->safeCsvText($level?->label),
                $transaction->amount,
                $transaction->balance_after,
                $this->safeCsvText($transaction->reference),
                $this->safeCsvText($transaction->notes),
            ]);
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $this->response
            ->withType('csv')
            ->withDownload($filename)
            ->withHeader('Content-Type', 'text/csv; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withStringBody($csv === false ? '' : $csv);
    }

    /** Record the selected job's current level payment into the operator wallet. */
    public function addEntry($id = null)
    {
        $this->request->allowMethod(['post']);
        $this->requireRole(['admin']);

        $wallet = $this->OperatorWallets->get($id, contain: ['Users']);
        $entry = $this->WalletTransactions->newEmptyEntity();

        if ($this->request->is('post')) {
            // FormHelper nests fields under `data`; accept flat payloads too.
            $payload = $this->request->getData('data');
            if (!is_array($payload) || $payload === []) {
                $payload = $this->request->getData();
            }

            $jobId = !empty($payload['job_id']) ? (int)$payload['job_id'] : null;
            $notes = trim((string)($payload['notes'] ?? ''));
            if ($jobId === null) {
                $this->Flash->error(__('Select an unpaid job to add its payment to this wallet.'));

                return $this->redirect(['action' => 'view', $wallet->id]);
            }

            $job = $this->Jobs->find('all', skipOrgFilter: true)
                ->where(['Jobs.id' => $jobId])
                ->first();
            if (!$job || (int)$job->operator_id !== (int)$wallet->user_id) {
                $this->Flash->error(__('That job is not assigned to this operator.'));

                return $this->redirect(['action' => 'view', $wallet->id]);
            }
            if (empty($job->level_id)) {
                $this->Flash->error(__('The selected job has no payment level.'));

                return $this->redirect(['action' => 'view', $wallet->id]);
            }

            $amount = $this->Jobs->paymentFor($job);
            if ($amount === null || (float)$amount <= 0) {
                $this->Flash->error(__('The selected job has no payable level rate.'));

                return $this->redirect(['action' => 'view', $wallet->id]);
            }
            $amount = round((float)$amount, 2);

            $error = $this->validateEntry($wallet, WalletTransaction::TYPE_EARNING, $amount, $jobId);
            if ($error !== null) {
                $this->Flash->error($error);

                return $this->redirect(['action' => 'view', $wallet->id]);
            }

            try {
                $transaction = $this->OperatorWallets->recordMovement(
                    (int)$wallet->user_id,
                    WalletTransaction::TYPE_EARNING,
                    $amount,
                    [
                        'job_id' => $jobId,
                        'notes' => $notes !== '' ? $notes : null,
                        'created_by' => $this->getCurrentUser()?->id,
                    ]
                );
            } catch (\Throwable $e) {
                $this->Flash->error($e->getMessage());

                return $this->redirect(['action' => 'view', $wallet->id]);
            }

            if (!$transaction) {
                $this->Flash->error(__('Enter an amount before saving the entry.'));

                return $this->redirect(['action' => 'view', $wallet->id]);
            }

            $this->logWalletMovement($transaction, $wallet, WalletTransaction::TYPE_EARNING);
            $this->Flash->success(__(
                'Wallet updated. New balance: {0}',
                number_format((float)$this->OperatorWallets->get($wallet->id)->balance, 2)
            ));

            return $this->redirect(['action' => 'view', $wallet->id]);
        }

        return $this->redirect(['action' => 'view', $wallet->id]);
    }

    /**
     * Ledger rows for a wallet, newest first.
     *
     * @return \Cake\ORM\Query\SelectQuery
     */
    private function ledgerQuery(int $walletId)
    {
        return $this->WalletTransactions->find()
            ->where(['WalletTransactions.wallet_id' => $walletId])
            ->contain(['Users', 'Jobs', 'CreatedBy'])
            ->orderByDesc('WalletTransactions.created_at')
            ->orderByDesc('WalletTransactions.id');
    }

    /**
     * Build chart series from the complete ledger, independently of pagination.
     *
     * @return array<string, mixed>
     */
    private function walletAnalytics(int $walletId): array
    {
        $rows = $this->WalletTransactions->find()
            ->where(['WalletTransactions.wallet_id' => $walletId])
            ->contain(['Jobs.Levels'])
            ->orderByAsc('WalletTransactions.created_at')
            ->all();

        $months = [];
        $jobs = [];
        $levels = [];
        foreach ($rows as $transaction) {
            $month = $transaction->created_at?->format('Y-m') ?? 'Unknown date';
            $months[$month] ??= ['earning' => 0.0, 'payout' => 0.0, 'adjustment' => 0.0];
            $amount = (float)$transaction->amount;

            if ($transaction->type === WalletTransaction::TYPE_EARNING) {
                $months[$month]['earning'] += $amount;
                $jobKey = $transaction->job_id !== null
                    ? (string)$transaction->job_id
                    : 'manual';
                $jobLabel = $transaction->job
                    ? (string)$transaction->job->job_number
                    : 'Manual / no job';
                $jobs[$jobKey] ??= ['label' => $jobLabel, 'amount' => 0.0];
                $jobs[$jobKey]['amount'] += $amount;

                $levelLabel = $transaction->job?->level?->label ?? 'No level / manual';
                $levels[$levelLabel] ??= 0.0;
                $levels[$levelLabel] += $amount;
            } elseif ($transaction->type === WalletTransaction::TYPE_PAYOUT) {
                $months[$month]['payout'] += abs($amount);
            } else {
                $months[$month]['adjustment'] += $amount;
            }
        }

        ksort($months);
        uasort($jobs, static fn(array $left, array $right): int => $right['amount'] <=> $left['amount']);
        arsort($levels);
        $jobs = array_slice($jobs, 0, 12, true);

        return [
            'months' => array_keys($months),
            'movement' => [
                ['name' => 'Income', 'data' => array_values(array_map(static fn(array $row): float => round($row['earning'], 2), $months))],
                ['name' => 'Payouts', 'data' => array_values(array_map(static fn(array $row): float => round($row['payout'], 2), $months))],
                ['name' => 'Adjustments', 'data' => array_values(array_map(static fn(array $row): float => round($row['adjustment'], 2), $months))],
            ],
            'jobLabels' => array_values(array_map(static fn(array $row): string => $row['label'], $jobs)),
            'jobAmounts' => array_values(array_map(static fn(array $row): float => round($row['amount'], 2), $jobs)),
            'levelLabels' => array_keys($levels),
            'levelAmounts' => array_values(array_map(static fn(float $amount): float => round($amount, 2), $levels)),
        ];
    }

    /**
     * Totals shown to the operator: assigned job value, completed job earnings,
     * and payments actually recorded by an administrator.
     *
     * @return array{current_balance: float, total_earnings: float, paid_to_you: float}
     */
    private function walletSummary($wallet): array
    {
        $currentBalance = 0.0;
        $totalEarnings = 0.0;
        $jobs = $this->Jobs->find('all', skipOrgFilter: true)
            ->where(['Jobs.operator_id' => $wallet->user_id])
            ->all();

        foreach ($jobs as $job) {
            $payment = $this->Jobs->paymentFor($job);
            if ($payment === null) {
                continue;
            }

            $amount = (float)$payment;
            $currentBalance += $amount;
            if (strtolower((string)$job->status) === 'completed') {
                $totalEarnings += $amount;
            }
        }

        return [
            'current_balance' => round($currentBalance, 2),
            'total_earnings' => round($totalEarnings, 2),
            'paid_to_you' => round((float)$wallet->total_paid, 2),
        ];
    }

    /** Prevent spreadsheet formula execution for user-entered CSV text. */
    private function safeCsvText(?string $value): string
    {
        $value = (string)$value;

        return preg_match('/^[\s]*[=+\-@]/u', $value) === 1 ? "'" . $value : $value;
    }

    /**
     * Jobs this operator can still be paid for: assigned to them, carrying a
     * level, and not already credited through the wallet.
     *
    * @return array<int, array{label: string, amount: string}>
     */
    private function payableJobs($wallet): array
    {
        $paid = $this->WalletTransactions->paidJobIds((int)$wallet->id);

        $jobs = $this->Jobs->find('all', skipOrgFilter: true)
            ->contain(['Levels'])
            ->where([
                'Jobs.operator_id' => $wallet->user_id,
                'Jobs.level_id IS NOT' => null,
            ])
            ->orderByDesc('Jobs.id')
            ->all();

        $options = [];
        foreach ($jobs as $job) {
            if (isset($paid[$job->id])) {
                continue;
            }
            $amount = $this->Jobs->paymentFor($job);
            if ($amount === null || (float)$amount <= 0) {
                continue;
            }
            $options[$job->id] = [
                'label' => sprintf(
                    '%s%s — %s [%s]',
                    (string)$job->job_number,
                    $job->hasValue('level') ? ' (' . $job->level->label . ')' : '',
                    (string)$job->title,
                    number_format((float)$amount, 2)
                ),
                'amount' => number_format((float)$amount, 2, '.', ''),
            ];
        }

        return $options;
    }

    /**
     * Reject entries that would corrupt the ledger.
     */
    private function validateEntry($wallet, string $type, float $amount, ?int $jobId): ?string
    {
        if (!in_array($type, [
            WalletTransaction::TYPE_EARNING,
            WalletTransaction::TYPE_PAYOUT,
            WalletTransaction::TYPE_ADJUSTMENT,
        ], true)) {
            return __('Invalid transaction type.');
        }

        if ($type === WalletTransaction::TYPE_ADJUSTMENT) {
            return $amount == 0.0 ? __('Enter an adjustment amount.') : null;
        }

        if ($amount <= 0) {
            return __('Enter an amount greater than zero.');
        }

        if ($type === WalletTransaction::TYPE_PAYOUT) {
            if ($amount - 0.005 > (float)$wallet->balance) {
                return __('Payout is larger than the current balance of {0}.', number_format((float)$wallet->balance, 2));
            }

            return null;
        }

        if ($jobId === null) {
            return null;
        }

        $job = $this->Jobs->find('all', skipOrgFilter: true)
            ->where(['Jobs.id' => $jobId])
            ->first();
        if (!$job) {
            return __('That job could not be found.');
        }
        if ((int)$job->operator_id !== (int)$wallet->user_id) {
            return __('That job is not assigned to this operator.');
        }

        if ($this->WalletTransactions->exists([
            'wallet_id' => $wallet->id,
            'job_id' => $jobId,
            'type' => WalletTransaction::TYPE_EARNING,
        ])) {
            return __('This job has already been paid into this wallet.');
        }

        return null;
    }

    /**
     * Mirror job-linked wallet movements into the job activity log.
     */
    private function logWalletMovement(WalletTransaction $transaction, $wallet, string $type): void
    {
        if (!$transaction->job_id) {
            return;
        }

        $actions = [
            WalletTransaction::TYPE_EARNING => 'wallet_income',
            WalletTransaction::TYPE_PAYOUT => 'wallet_payout',
            WalletTransaction::TYPE_ADJUSTMENT => 'wallet_adjustment',
        ];

        $jobLogs = $this->fetchTable('JobLogs');
        $log = $jobLogs->newEmptyEntity();
        $log->job_id = $transaction->job_id;
        $log->user_id = $this->getCurrentUser()?->id;
        $log->action = $actions[$type] ?? 'wallet_entry';
        $log->comments = sprintf(
            '%s of %s for %s',
            ucfirst($type),
            number_format(abs((float)$transaction->amount), 2),
            (string)($wallet->user->name ?? ('operator #' . $wallet->user_id))
        );
        $jobLogs->save($log);
    }
}