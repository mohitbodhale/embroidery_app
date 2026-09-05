<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Role $role
 */
$this->assign('title', 'Add Role');
?>
<div class="page-card card">
    <div class="card-header">
        <h3 class="card-title m-0"><i class="fas fa-user-shield me-2"></i>Add Role</h3>
    </div>
    <div class="card-body">
        <?= $this->Form->create($role, ['url' => ['action' => 'add']]) ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label>Name (internal, lowercase)</label>
                    <?= $this->Form->control('name', ['class' => 'form-control', 'placeholder' => 'e.g. supervisor', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-md-4">
                    <label>Display Label</label>
                    <?= $this->Form->control('label', ['class' => 'form-control', 'placeholder' => 'e.g. Supervisor', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
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
                    <?= $this->Form->control('description', ['class' => 'form-control', 'placeholder' => 'What this role does', 'label' => false, 'templates' => ['inputContainer' => '{{content}}']]) ?>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <?= $this->Form->checkbox('is_active', ['class' => 'form-check-input', 'checked' => true]) ?>
                        <label class="form-check-label">Active (available for assignment)</label>
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i>Cancel', ['action' => 'index'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
                <?= $this->Form->button('<i class="fas fa-save me-1"></i>Create Role', ['type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false]) ?>
            </div>
        <?= $this->Form->end() ?>
    </div>
</div>
