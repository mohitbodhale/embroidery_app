<?php
/**
 * Operator wallet detail: balance, ledger and the income entry form.
 *
 * Rendered by Wallets::view (administrator, with the entry form) and
 * Wallets::my (operator, read-only).
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OperatorWallet $wallet
 * @var iterable<\App\Model\Entity\WalletTransaction> $transactions
 * @var \App\Model\Entity\WalletTransaction|null $entry
 * @var array<int, array{label: string, amount: string}>|null $payableJobs
 * @var array<string, mixed> $analytics
 * @var array{current_balance: float, total_earnings: float, paid_to_you: float} $summary
 */
$this->assign('title', 'Wallet — ' . ($wallet->hasValue('user') ? $wallet->user->name : ('User #' . $wallet->user_id)));
$canManage = $currentRole === 'admin';
$operatorName = $wallet->hasValue('user') ? $wallet->user->name : ('User #' . $wallet->user_id);
$payableJobOptions = [];
$payableJobAmounts = [];
foreach ($payableJobs ?? [] as $jobId => $payableJob) {
    $payableJobOptions[$jobId] = $payableJob['label'];
    $payableJobAmounts[$jobId] = $payableJob['amount'];
}
$movement = $analytics['movement'] ?? [];
$monthlyRows = [];
foreach ($analytics['months'] ?? [] as $index => $month) {
    $income = (float)($movement[0]['data'][$index] ?? 0);
    $paid = (float)($movement[1]['data'][$index] ?? 0);
    $adjustments = (float)($movement[2]['data'][$index] ?? 0);
    $monthlyRows[] = [
        'month' => $month,
        'income' => $income,
        'paid' => $paid,
        'adjustments' => $adjustments,
        'change' => $income - $paid + $adjustments,
    ];
}
$monthlyRows = array_slice($monthlyRows, -12);
$monthlyScale = max([1.0, ...array_map(static fn(array $row): float => abs($row['change']), $monthlyRows)]);
$jobRows = [];
foreach ($analytics['jobLabels'] ?? [] as $index => $label) {
    $jobRows[] = ['label' => $label, 'amount' => (float)($analytics['jobAmounts'][$index] ?? 0)];
}
$levelRows = [];
foreach ($analytics['levelLabels'] ?? [] as $index => $label) {
    $levelRows[] = ['label' => $label, 'amount' => (float)($analytics['levelAmounts'][$index] ?? 0)];
}
$maxJobAmount = max([1.0, ...array_column($jobRows, 'amount')]);
$maxLevelAmount = max([1.0, ...array_column($levelRows, 'amount')]);
?>
<div class="page-card card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0"><i class="fas fa-wallet me-2"></i><?= h($operatorName) ?></h3>
        <div class="d-flex gap-2">
            <?= $this->Html->link('<i class="fas fa-file-csv me-1"></i>Export CSV',
                $canManage ? ['action' => 'export', $wallet->id] : ['action' => 'export'],
                ['class' => 'btn btn-sm btn-outline-success', 'escape' => false]) ?>
            <?php if ($canManage): ?>
                <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>All wallets
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="wallet-total wallet-total-balance h-100">
                    <div class="text-muted small text-uppercase">Current balance</div>
                    <div class="fs-4 fw-bold text-<?= (float)$summary['current_balance'] > 0 ? 'success' : 'muted' ?>">
                        <?= h(number_format((float)$summary['current_balance'], 2)) ?>
                    </div>
                    <div class="small text-muted">Total value of all your assigned jobs, in any status</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="wallet-total h-100">
                    <div class="text-muted small text-uppercase">Total earnings</div>
                    <div class="fs-5 fw-semibold"><?= h(number_format((float)$summary['total_earnings'], 2)) ?></div>
                    <div class="small text-muted">Value of jobs marked completed</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="wallet-total h-100">
                    <div class="text-muted small text-uppercase">Paid to you</div>
                    <div class="fs-5 fw-semibold"><?= h(number_format((float)$summary['paid_to_you'], 2)) ?></div>
                    <div class="small text-muted">Job payments credited by admin</div>
                </div>
            </div>
        </div>

        <section class="wallet-analytics mb-4" aria-labelledby="wallet-analytics-title">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
                <div>
                    <h4 id="wallet-analytics-title" class="mb-1">Wallet statement</h4>
                    <p class="text-muted small mb-0">A clear summary of earnings and payments</p>
                </div>
            </div>
            <section class="wallet-statement mb-4" aria-labelledby="monthly-title">
                <div class="wallet-section-heading">
                    <h5 id="monthly-title" class="h6 mb-0">By month</h5>
                    <span class="small text-muted">Latest 12 months</span>
                </div>
                <?php if ($monthlyRows === []): ?>
                    <p class="wallet-empty mb-0">Your monthly activity will appear here when a job payment is added.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 wallet-month-table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th class="text-end">Earned</th>
                                    <th class="text-end">Paid to you</th>
                                    <th class="text-end">Adjustment</th>
                                    <th>Balance change</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_reverse($monthlyRows) as $row): ?>
                                    <?php $changeWidth = min(100, abs($row['change']) / $monthlyScale * 100); ?>
                                    <tr>
                                        <th scope="row" class="fw-medium"><?= h($row['month'] === 'Unknown date' ? $row['month'] : date('M Y', strtotime($row['month'] . '-01'))) ?></th>
                                        <td class="text-end text-success"><?= h(number_format($row['income'], 2)) ?></td>
                                        <td class="text-end"><?= h(number_format($row['paid'], 2)) ?></td>
                                        <td class="text-end"><?= h(number_format($row['adjustments'], 2)) ?></td>
                                        <td>
                                            <div class="wallet-change-cell">
                                                <span class="fw-semibold text-<?= $row['change'] >= 0 ? 'success' : 'danger' ?>"><?= $row['change'] > 0 ? '+' : '' ?><?= h(number_format($row['change'], 2)) ?></span>
                                                <span class="wallet-change-track"><span class="wallet-change-bar <?= $row['change'] >= 0 ? 'is-positive' : 'is-negative' ?>" style="width: <?= h((string)$changeWidth) ?>%"></span></span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <div class="row g-4">
                <div class="col-lg-7">
                    <section class="wallet-statement h-100" aria-labelledby="jobs-title">
                        <div class="wallet-section-heading">
                            <h5 id="jobs-title" class="h6 mb-0">Earnings by job</h5>
                            <span class="small text-muted">Up to 12 jobs</span>
                        </div>
                        <?php if ($jobRows === []): ?>
                            <p class="wallet-empty mb-0">Job earnings will appear here after a payment is added.</p>
                        <?php else: ?>
                            <div class="wallet-breakdown-list">
                                <?php foreach ($jobRows as $row): ?>
                                    <?php $barWidth = min(100, $row['amount'] / $maxJobAmount * 100); ?>
                                    <div class="wallet-breakdown-row">
                                        <span class="wallet-breakdown-label" title="<?= h($row['label']) ?>"><?= h($row['label']) ?></span>
                                        <span class="wallet-breakdown-track"><span class="wallet-breakdown-bar" style="width: <?= h((string)$barWidth) ?>%"></span></span>
                                        <strong><?= h(number_format($row['amount'], 2)) ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
                <div class="col-lg-5">
                    <section class="wallet-statement h-100" aria-labelledby="levels-title">
                        <div class="wallet-section-heading">
                            <h5 id="levels-title" class="h6 mb-0">Earnings by level</h5>
                        </div>
                        <?php if ($levelRows === []): ?>
                            <p class="wallet-empty mb-0">Level totals will appear here after a job payment is added.</p>
                        <?php else: ?>
                            <div class="wallet-breakdown-list">
                                <?php foreach ($levelRows as $row): ?>
                                    <?php $barWidth = min(100, $row['amount'] / $maxLevelAmount * 100); ?>
                                    <div class="wallet-breakdown-row">
                                        <span class="wallet-breakdown-label" title="<?= h($row['label']) ?>"><?= h($row['label']) ?></span>
                                        <span class="wallet-breakdown-track"><span class="wallet-breakdown-bar is-level" style="width: <?= h((string)$barWidth) ?>%"></span></span>
                                        <strong><?= h(number_format($row['amount'], 2)) ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            </div>
        </section>

        <?php if ($canManage): ?>
        <div class="card border-primary border-2 mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-plus-circle text-primary me-2"></i>
                    <h5 class="card-title mb-0">Add job payment</h5>
                </div>
                <?= $this->Form->create($entry, ['url' => ['action' => 'addEntry', $wallet->id], 'class' => 'wallet-entry-form']) ?>
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-8">
                            <label>Unpaid job</label>
                            <?= $this->Form->control('job_id', [
                                'options' => $payableJobOptions,
                                'empty' => empty($payableJobOptions) ? 'No unpaid jobs available' : 'Choose a job',
                                'class' => 'form-select',
                                'label' => false,
                                'required' => true,
                                'disabled' => empty($payableJobOptions),
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                            <div class="form-text">The payment is calculated from the job level and scheduled date.</div>
                        </div>
                        <div class="col-lg-4">
                            <label>Payment to add</label>
                            <div class="form-control bg-light fw-semibold" id="job-payment-preview" aria-live="polite">Choose a job</div>
                        </div>
                        <div class="col-lg-9">
                            <label>Note <span class="text-muted fw-normal">(optional)</span></label>
                            <?= $this->Form->control('notes', [
                                'class' => 'form-control',
                                'placeholder' => 'Add a short note',
                                'label' => false,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <div class="col-lg-3 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Add payment to wallet
                            </button>
                        </div>
                    </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
        <?php endif; ?>

        <strong class="d-block mb-3 section-label"><i class="fas fa-stream me-1"></i>Wallet ledger</strong>
        <?php if (empty($transactions) || iterator_count($transactions) === 0): ?>
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <h4>No entries yet</h4>
                <p><?= $canManage ? 'Record the first income entry for this operator.' : 'Nothing has been recorded yet.' ?></p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Job</th>
                            <th>Amount</th>
                            <th>Balance after</th>
                            <th>Notes</th>
                            <th>Recorded by</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <?php
                                $isCredit = (float)$transaction->amount >= 0;
                                $typeMeta = [
                                    'earning' => ['label' => 'Income', 'class' => 'bg-success'],
                                    'payout' => ['label' => 'Payout', 'class' => 'bg-danger'],
                                    'adjustment' => ['label' => 'Adjustment', 'class' => 'bg-secondary'],
                                ][$transaction->type] ?? ['label' => $transaction->type, 'class' => 'bg-secondary'];
                            ?>
                            <tr>
                                <td class="text-muted small"><?= $transaction->created_at ? h($transaction->created_at->format('M d, Y H:i')) : '—' ?></td>
                                <td><span class="badge <?= h($typeMeta['class']) ?>"><?= h($typeMeta['label']) ?></span></td>
                                <td>
                                    <?php if ($transaction->hasValue('job')): ?>
                                        <?= $this->Html->link(
                                            (string)$transaction->job->job_number,
                                            ['controller' => 'Jobs', 'action' => 'view', $transaction->job_id],
                                            ['class' => 'job-link']
                                        ) ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-semibold text-<?= $isCredit ? 'success' : 'danger' ?>">
                                    <?= $isCredit ? '+' : '-' ?><?= h(number_format(abs((float)$transaction->amount), 2)) ?>
                                </td>
                                <td><?= h(number_format((float)$transaction->balance_after, 2)) ?></td>
                                <td class="text-muted small">
                                    <?= h($transaction->notes) ?>
                                    <?php if ($transaction->reference): ?>
                                        <span class="badge bg-light text-dark border"><?= h($transaction->reference) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= $transaction->hasValue('creator') ? h($transaction->creator->name) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <div class="small text-muted">
                    <?= $this->Paginator->counter(__('Page {0} of {1}, showing {2} record(s)'), $this->Paginator->params()) ?>
                </div>
                <ul class="pagination pagination-sm mb-0">
                    <?= $this->Paginator->prev('« Previous') ?>
                    <?= $this->Paginator->numbers() ?>
                    <?= $this->Paginator->next('Next »') ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
<style>
    .wallet-total,
    .wallet-statement { border: 1px solid #e1e7e9; border-radius: 4px; background: #fff; }
    .wallet-total { padding: 14px 16px; }
    .wallet-total-balance { border-top: 3px solid #218c67; }
    .wallet-statement { padding: 16px; }
    .wallet-section-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
    .wallet-empty { padding: 24px 12px; background: #f7f9f8; color: #65747a; text-align: center; }
    .wallet-month-table { min-width: 660px; }
    .wallet-month-table th { color: #53636a; font-size: 12px; font-weight: 600; white-space: nowrap; }
    .wallet-month-table td { font-size: 13px; white-space: nowrap; }
    .wallet-change-cell { display: grid; grid-template-columns: 75px minmax(56px, 1fr); align-items: center; gap: 8px; }
    .wallet-change-track,
    .wallet-breakdown-track { display: block; height: 7px; overflow: hidden; border-radius: 4px; background: #edf1f0; }
    .wallet-change-bar,
    .wallet-breakdown-bar { display: block; height: 100%; border-radius: inherit; }
    .wallet-change-bar.is-positive { background: #218c67; }
    .wallet-change-bar.is-negative { background: #d65f59; }
    .wallet-breakdown-list { display: grid; gap: 13px; }
    .wallet-breakdown-row { display: grid; grid-template-columns: minmax(80px, 1.1fr) minmax(70px, 1.5fr) minmax(74px, auto); align-items: center; gap: 10px; font-size: 13px; }
    .wallet-breakdown-label { overflow: hidden; color: #46565d; text-overflow: ellipsis; white-space: nowrap; }
    .wallet-breakdown-row strong { text-align: right; white-space: nowrap; }
    .wallet-breakdown-bar { background: #347ea8; }
    .wallet-breakdown-bar.is-level { background: #218c67; }
    @media (max-width: 575.98px) {
        .wallet-statement { padding: 12px; }
        .wallet-breakdown-row { grid-template-columns: minmax(74px, 1fr) minmax(42px, 1fr) minmax(68px, auto); gap: 7px; font-size: 12px; }
        .wallet-change-cell { grid-template-columns: 68px minmax(45px, 1fr); gap: 6px; }
    }
</style>
<?php $this->start('script'); ?>
<script>
(function () {
    var jobAmounts = <?= json_encode($payableJobAmounts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var jobSelect = document.querySelector('.wallet-entry-form select[name*="job_id"]');
    var preview = document.getElementById('job-payment-preview');
    if (!jobSelect || !preview) {
        return;
    }
    function updatePaymentPreview() {
        var amount = jobAmounts[jobSelect.value];
        preview.textContent = amount === undefined
            ? 'Choose a job'
            : new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(amount));
    }
    jobSelect.addEventListener('change', updatePaymentPreview);
    updatePaymentPreview();
})();
</script>
<?php $this->end(); ?>