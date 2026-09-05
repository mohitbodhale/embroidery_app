<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\JobStatus $jobStatus
 */
$this->assign('title', 'Add Status');
?>
<div class="page-card card">
    <div class="card-header">
        <h3 class="card-title m-0"><i class="fas fa-tag me-2"></i>Add Job Status</h3>
    </div>
    <div class="card-body">
        <?= $this->Form->create($jobStatus, ['url' => ['action' => 'add']]) ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Name (internal, lowercase)</label>
                    <?= $this->Form->control('name', ['class' => 'form-control', 'placeholder' => 'e.g. on_hold', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-4">
                    <label>Display Label</label>
                    <?= $this->Form->control('label', ['class' => 'form-control', 'placeholder' => 'e.g. On Hold', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-2">
                    <label>Color</label>
                    <?= $this->Form->control('color', ['class' => 'form-control', 'type' => 'color', 'value' => '#6c757d', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-2">
                    <label>Sort Order</label>
                    <?= $this->Form->control('sort_order', ['class' => 'form-control', 'type' => 'number', 'value' => 99, 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-12">
                    <label>Description</label>
                    <?= $this->Form->control('description', ['class' => 'form-control', 'placeholder' => 'What this status means', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-12 d-flex gap-4">
                    <div class="form-check form-switch">
                        <?= $this->Form->checkbox('is_active', ['class' => 'form-check-input', 'checked' => true]) ?>
                        <label class="form-check-label">Active</label>
                    </div>
                    <div class="form-check form-switch">
                        <?= $this->Form->checkbox('is_terminal', ['class' => 'form-check-input']) ?>
                        <label class="form-check-label">Terminal (job is finished)</label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i>Cancel', ['action' => 'index'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
                <?= $this->Form->button('<i class="fas fa-save me-1"></i>Create Status', ['type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false]) ?>
            </div>
        <?= $this->Form->end() ?>
    </div>
</div>
