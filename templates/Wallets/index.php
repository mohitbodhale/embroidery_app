<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\OperatorWallet> $wallets
 */
$this->assign('title', 'Operator Wallets');
$totalBalance = 0.0;
$totalEarned = 0.0;
$totalPaid = 0.0;
foreach ($wallets as $wallet) {
    $totalBalance += (float)$wallet->balance;
    $totalEarned += (float)$wallet->total_earned;
    $totalPaid += (float)$wallet->total_paid;
}
?>
<div class="page-card card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0"><i class="fas fa-wallet me-2"></i>Operator Wallets</h3>
        <span class="text-muted small">Income is recorded here per job level</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($wallets) || iterator_count($wallets) === 0): ?>
            <div class="empty-state">
                <i class="fas fa-wallet"></i>
                <h4>No operators yet</h4>
                <p>Wallets appear here once an operator account exists.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table mb-0">
                    <thead>
                        <tr>
                            <th>Operator</th>
                            <th>Balance</th>
                            <th>Total earned</th>
                            <th>Total paid</th>
                            <th>Last activity</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wallets as $wallet): ?>
                            <tr>
                                <td>
                                    <strong><?= h($wallet->hasValue('user') ? $wallet->user->name : ('User #' . $wallet->user_id)) ?></strong>
                                    <?php if ($wallet->hasValue('user') && $wallet->user->email): ?>
                                        <div class="text-muted small"><?= h($wallet->user->email) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= (float)$wallet->balance > 0 ? 'success' : ((float)$wallet->balance < 0 ? 'danger' : 'secondary') ?>">
                                        <?= h(number_format((float)$wallet->balance, 2)) ?>
                                    </span>
                                </td>
                                <td><?= h(number_format((float)$wallet->total_earned, 2)) ?></td>
                                <td><?= h(number_format((float)$wallet->total_paid, 2)) ?></td>
                                <td class="text-muted small"><?= $wallet->modified_at ? h($wallet->modified_at->format('M d, Y H:i')) : '—' ?></td>
                                <td class="text-end">
                                    <div class="row-actions justify-content-end">
                                        <?= $this->Html->link('<i class="fas fa-eye me-1"></i>Open', ['action' => 'view', $wallet->id], ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false]) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-group-divider">
                        <tr class="fw-semibold">
                            <td>Total</td>
                            <td><?= h(number_format($totalBalance, 2)) ?></td>
                            <td><?= h(number_format($totalEarned, 2)) ?></td>
                            <td><?= h(number_format($totalPaid, 2)) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>