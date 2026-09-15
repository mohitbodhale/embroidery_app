<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\WorkType $workType
 */
$this->assign('title', 'Edit Work Type');
?>
<div class="page-card card">
    <div class="card-header">
        <h3 class="card-title m-0"><i class="fas fa-pen me-2"></i>Edit Work Type: <?= h($workType->label) ?></h3>
    </div>
    <div class="card-body">
        <?= $this->Form->create($workType) ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Name (internal, lowercase)</label>
                    <?= $this->Form->control('name', ['class' => 'form-control', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-4">
                    <label>Display Label</label>
                    <?= $this->Form->control('label', ['class' => 'form-control', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-4">
                    <label>Color</label>
                    <?= $this->Form->control('color', ['class' => 'form-control', 'type' => 'color', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-4">
                    <label>Sort Order</label>
                    <?= $this->Form->control('sort_order', ['class' => 'form-control', 'type' => 'number', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-12">
                    <label>Description</label>
                    <?= $this->Form->control('description', ['class' => 'form-control', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <?= $this->Form->checkbox('is_active', ['class' => 'form-check-input']) ?>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i>Cancel', ['action' => 'index'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
                <?= $this->Form->button('<i class="fas fa-save me-1"></i>Save Changes', ['type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false]) ?>
            </div>
        <?= $this->Form->end() ?>
    </div>
</div>
