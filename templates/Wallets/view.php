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
 * @var array<int, string>|null $payableJobs
 */
$this->assign('title', 'Wallet — ' . ($wallet->hasValue('user') ? $wallet->user->name : ('User #' . $wallet->user_id)));
$canManage = $currentRole === 'admin';
$operatorName = $wallet->hasValue('user') ? $wallet->user->name : ('User #' . $wallet->user_id);
?>
<div class="page-card card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0"><i class="fas fa-wallet me-2"></i><?= h($operatorName) ?></h3>
        <?php if ($canManage): ?>
            <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>All wallets
            </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small text-uppercase">Balance</div>
                    <div class="fs-4 fw-bold text-<?= (float)$wallet->balance > 0 ? 'success' : ((float)$wallet->balance < 0 ? 'danger' : 'muted') ?>">
                        <?= h(number_format((float)$wallet->balance, 2)) ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small text-uppercase">Total earned</div>
                    <div class="fs-5 fw-semibold"><?= h(number_format((float)$wallet->total_earned, 2)) ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted small text-uppercase">Total paid out</div>
                    <div class="fs-5 fw-semibold"><?= h(number_format((float)$wallet->total_paid, 2)) ?></div>
                </div>
            </div>
        </div>

        <?php if ($canManage): ?>
        <div class="card border-primary border-2 mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-plus-circle text-primary me-2"></i>
                    <h5 class="card-title mb-0">Record wallet entry</h5>
                </div>
                <?= $this->Form->create($entry, ['url' => ['action' => 'addEntry', $wallet->id], 'class' => 'wallet-entry-form']) ?>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label>Type</label>
                            <?= $this->Form->control('type', [
                                'options' => [
                                    'earning' => 'Income (job payment)',
                                    'adjustment' => 'Adjustment',
                                    'payout' => 'Payment to operator',
                                ],
                                'class' => 'form-select',
                                'label' => false,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <label>Job (optional)</label>
                            <?= $this->Form->control('job_id', [
                                'options' => $payableJobs ?? [],
                                'empty' => 'No job / manual entry',
                                'class' => 'form-select',
                                'label' => false,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                            <div class="form-text">Amounts in brackets come from the job level.</div>
                        </div>
                        <div class="col-md-2">
                            <label>Amount</label>
                            <?= $this->Form->control('amount', [
                                'class' => 'form-control',
                                'type' => 'number',
                                'step' => '0.01',
                                'min' => '0',
                                'placeholder' => '0.00',
                                'label' => false,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <label>Reference</label>
                            <?= $this->Form->control('reference', [
                                'class' => 'form-control',
                                'placeholder' => 'e.g. cash',
                                'label' => false,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <div class="col-md-2">
                            <label>Notes</label>
                            <?= $this->Form->control('notes', [
                                'class' => 'form-control',
                                'placeholder' => 'Optional',
                                'label' => false,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check me-1"></i>Save entry
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