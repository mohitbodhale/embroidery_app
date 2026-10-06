<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Level> $levels
 */
$this->assign('title', 'Levels');
?>
<div class="page-card card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title m-0"><i class="fas fa-layer-group me-2"></i>Levels</h3>
        <?= $this->Html->link('<i class="fas fa-plus me-1"></i>New Level', ['action' => 'add'], ['class' => 'btn btn-primary btn-sm', 'escape' => false]) ?>
    </div>
    <div class="card-body p-0">
        <?php if (empty($levels) || iterator_count($levels) === 0): ?>
            <div class="empty-state">
                <i class="fas fa-layer-group"></i>
                <h4>No levels defined yet</h4>
                <p>Define the pay grades and set the payment amount earned per job.</p>
                <?= $this->Html->link('Add Level', ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table data-table mb-0">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Name</th>
                            <th>Label</th>
                            <th>Rate now</th>
                            <th>Periods</th>
                            <th>Description</th>
                            <th>Color</th>
                            <th>Active</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($levels as $level): ?>
                            <tr>
                                <td><?= h($level->sort_order) ?></td>
                                <td><code><?= h($level->name) ?></code></td>
                                <td>
                                    <span class="badge" style="background-color: <?= h($level->color ?: '#6c757d') ?>; color: white;">
                                        <?= h($level->label) ?>
                                    </span>
                                </td>
                                <td><strong><?= h(number_format((float)($currentRates[$level->id] ?? $level->payment_amount), 2)) ?></strong></td>                                <td class="text-muted small">
                                    <?php if (!empty($level->level_rates)): ?>
                                        <?= h(count($level->level_rates)) ?> payment period<?= count($level->level_rates) === 1 ? '' : 's' ?>
                                    <?php else: ?>
                                        <span class="text-muted">No periods</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= h($level->description) ?></td>
                                <td><code><?= h($level->color) ?></code></td>
                                <td>
                                    <?php if ($level->is_active): ?>
                                        <span class="status-dot status-dot-completed"></span> Active
                                    <?php else: ?>
                                        <span class="status-dot status-dot-rejected"></span> Inactive
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="row-actions">
                                        <?= $this->Html->link('<i class="fas fa-pen"></i>', ['action' => 'edit', $level->id], ['class' => 'btn-icon', 'escape' => false, 'title' => 'Edit']) ?>
                                        <?= $this->Form->postLink('<i class="fas fa-trash"></i>', ['action' => 'delete', $level->id], ['class' => 'btn-icon btn-icon-danger', 'escape' => false, 'title' => 'Delete', 'confirm' => __('Delete level "{0}"?', $level->label)]) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted small">
                Changing a level's payment amount only affects jobs assigned to it from now on.
                Jobs already assigned keep the payment amount recorded on the job.
            </div>
        <?php endif; ?>
    </div>
</div>