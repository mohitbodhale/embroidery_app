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

        $this->set(compact('wallet', 'transactions'));
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

        $this->set(compact('wallet', 'transactions'));
        $this->set('entry', $this->WalletTransactions->newEmptyEntity());
        $this->set('payableJobs', $this->payableJobs($wallet));
    }

    /**
     * Record income, a payout or an adjustment against a wallet.
     */
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

            $type = (string)($payload['type'] ?? WalletTransaction::TYPE_EARNING);
            $amount = round((float)($payload['amount'] ?? 0), 2);
            $jobId = !empty($payload['job_id']) ? (int)$payload['job_id'] : null;
            $notes = trim((string)($payload['notes'] ?? ''));
            $reference = trim((string)($payload['reference'] ?? ''));

            $error = $this->validateEntry($wallet, $type, $amount, $jobId);
            if ($error !== null) {
                $this->Flash->error($error);

                return $this->redirect(['action' => 'view', $wallet->id]);
            }

            if ($type === WalletTransaction::TYPE_ADJUSTMENT && $amount < 0) {
                $amount = -abs($amount);
            }

            try {
                $transaction = $this->OperatorWallets->recordMovement(
                    (int)$wallet->user_id,
                    $type,
                    $amount,
                    [
                        'job_id' => $jobId,
                        'notes' => $notes !== '' ? $notes : null,
                        'reference' => $reference !== '' ? $reference : null,
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

            $this->logWalletMovement($transaction, $wallet, $type);
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
     * Jobs this operator can still be paid for: assigned to them, carrying a
     * level, and not already credited through the wallet.
     *
     * @return array<int, string>
     */
    private function payableJobs($wallet): array
    {
        $paid = $this->WalletTransactions->paidJobIds((int)$wallet->id);

        $jobs = $this->Jobs->find('all', skipOrgFilter: true)
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
            $options[$job->id] = sprintf(
                '%s%s — %s%s',
                (string)$job->job_number,
                $job->hasValue('level') ? ' (' . $job->level->label . ')' : '',
                (string)$job->title,
                $amount !== null ? ' [' . number_format((float)$amount, 2) . ']' : ''
            );
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