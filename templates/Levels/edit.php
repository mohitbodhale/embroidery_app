<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Level $level
 */
$this->assign('title', 'Edit Level');
?>
<div class="page-card card">
    <div class="card-header">
        <h3 class="card-title m-0"><i class="fas fa-layer-group me-2"></i>Edit Level</h3>
    </div>
    <div class="card-body">
        <?= $this->Form->create($level) ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Name (internal, lowercase)</label>
                    <?= $this->Form->control('name', ['class' => 'form-control', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-4">
                    <label>Display Label</label>
                    <?= $this->Form->control('label', ['class' => 'form-control', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-2">
                    <label>Color</label>
                    <?= $this->Form->control('color', ['class' => 'form-control', 'type' => 'color', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-2">
                    <label>Sort Order</label>
                    <?= $this->Form->control('sort_order', ['class' => 'form-control', 'type' => 'number', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-8">
                    <label>Description</label>
                    <?= $this->Form->control('description', ['class' => 'form-control', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-4">
                    <label>Default rate</label>
                    <?= $this->Form->control('payment_amount', ['class' => 'form-control', 'type' => 'number', 'step' => '0.01', 'min' => '0', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                    <div class="form-text">Used only when no payment period matches a job's date.</div>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <?= $this->Form->checkbox('is_active', ['class' => 'form-check-input']) ?>
                        <label class="form-check-label">Active (available when assigning a job)</label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i>Cancel', ['action' => 'index'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
                <?= $this->Form->button('<i class="fas fa-save me-1"></i>Save Level', ['type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false]) ?>
            </div>
        <?= $this->Form->end() ?>

        <hr>

        <h4 class="mb-3"><i class="fas fa-calendar-alt me-2"></i>Payment periods</h4>
        <p class="text-muted">
            The amount an operator earns depends on the job's scheduled date.
            Add one period per rate; the period whose dates cover the job's
            date is the one that applies.
        </p>

        <?php if (!empty($level->level_rates)): ?>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Valid from</th>
                        <th>Valid to</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Applies now</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($level->level_rates as $rate): ?>
                    <tr>
                        <td><?= $rate->valid_from ? h($rate->valid_from->format('d M Y')) : '<span class="text-muted">beginning</span>' ?></td>
                        <td><?= $rate->valid_to ? h($rate->valid_to->format('d M Y')) : '<span class="text-muted">open</span>' ?></td>
                        <td class="text-end"><?= h($rate->payment_formatted) ?></td>
                        <td class="text-center">
                            <?php if ($rate->coversToday): ?>
                                <span class="badge bg-success">Current</span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?= $this->Form->postLink('<i class="fas fa-trash"></i>', ['action' => 'deleteRate', $level->id, $rate->id], [
                                'confirm' => __('Remove this payment period?'),
                                'class' => 'btn btn-icon btn-outline-danger btn-sm',
                                'escape' => false,
                                'title' => 'Remove',
                            ]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">No payment periods yet. Add one below.</div>
        <?php endif; ?>

        <div class="card border-primary border-2">
            <div class="card-header bg-white">
                <h5 class="card-title m-0"><i class="fas fa-plus me-2"></i>Add payment period</h5>
            </div>
            <div class="card-body">
                <?= $this->Form->create(null, ['url' => ['action' => 'addRate', $level->id]]) ?>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label>Valid from</label>
                            <?= $this->Form->control('valid_from', [
                                'type' => 'date',
                                'class' => 'form-control',
                                'label' => false,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <label>Valid to</label>
                            <?= $this->Form->control('valid_to', [
                                'type' => 'date',
                                'class' => 'form-control',
                                'label' => false,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                            <div class="form-text">Leave empty to keep it open.</div>
                        </div>
                        <div class="col-md-3">
                            <label>Amount (Rs)</label>
                            <?= $this->Form->control('payment_amount', [
                                'type' => 'number',
                                'step' => '0.01',
                                'min' => '0',
                                'class' => 'form-control',
                                'label' => false,
                                'required' => true,
                                'templates' => ['inputContainer' => '{{content}}'],
                            ]) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $this->Form->button('<i class="fas fa-plus me-1"></i>Add period', [
                                'type' => 'submit',
                                'class' => 'btn btn-primary w-100',
                                'escapeTitle' => false,
                            ]) ?>
                        </div>
                    </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
