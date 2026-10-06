<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Level $level
 */
$this->assign('title', 'Add Level');
?>
<div class="page-card card">
    <div class="card-header">
        <h3 class="card-title m-0"><i class="fas fa-layer-group me-2"></i>Add Level</h3>
    </div>
    <div class="card-body">
        <?= $this->Form->create($level, ['url' => ['action' => 'add']]) ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Name (internal, lowercase)</label>
                    <?= $this->Form->control('name', ['class' => 'form-control', 'placeholder' => 'e.g. level_2', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-4">
                    <label>Display Label</label>
                    <?= $this->Form->control('label', ['class' => 'form-control', 'placeholder' => 'e.g. Level 2', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-4">
                    <label>Payment per job</label>
                    <?= $this->Form->control('payment_amount', ['class' => 'form-control', 'type' => 'number', 'step' => '0.01', 'min' => '0', 'value' => '0.00', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                    <div class="form-text">Amount the operator earns for one job at this level.</div>
                </div>
                <div class="col-md-2">
                    <label>Color</label>
                    <?= $this->Form->control('color', ['class' => 'form-control', 'type' => 'color', 'value' => '#0dcaf0', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-2">
                    <label>Sort Order</label>
                    <?= $this->Form->control('sort_order', ['class' => 'form-control', 'type' => 'number', 'value' => 99, 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-8">
                    <label>Description</label>
                    <?= $this->Form->control('description', ['class' => 'form-control', 'placeholder' => 'What work this level covers', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <?= $this->Form->checkbox('is_active', ['class' => 'form-check-input', 'checked' => true]) ?>
                        <label class="form-check-label">Active (available when assigning a job)</label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i>Cancel', ['action' => 'index'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
                <?= $this->Form->button('<i class="fas fa-save me-1"></i>Create Level', ['type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false]) ?>
            </div>
        <?= $this->Form->end() ?>
    </div>
</div>