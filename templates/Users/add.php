<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var \Cake\Collection\CollectionInterface|string[] $organizations
 */
$this->assign('title', 'Create user');
?>
<div class="page-card card">
    <div class="card-body">
        <?= $this->Form->create($user, ['class' => 'user-add-form']) ?>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Full name</label>
                <?= $this->Form->control('name', [
                    'class' => 'form-control',
                    'placeholder' => 'Jane Doe',
                    'required' => true,
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
            <div class="col-md-6 mb-3">
                <label>Email address</label>
                <?= $this->Form->control('email', [
                    'class' => 'form-control',
                    'type' => 'email',
                    'placeholder' => 'jane@example.com',
                    'required' => true,
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Temporary password</label>
                <?= $this->Form->control('password', [
                    'type' => 'password',
                    'class' => 'form-control',
                    'placeholder' => 'Minimum 6 characters',
                    'required' => true,
                    'minlength' => 6,
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
            <div class="col-md-6 mb-3">
                <label>Confirm password</label>
                <?= $this->Form->control('password_confirm', [
                    'type' => 'password',
                    'class' => 'form-control',
                    'placeholder' => 'Repeat password',
                    'required' => true,
                    'minlength' => 6,
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
        </div>

        <div class="mb-4">
            <label>Role</label>
            <?= $this->Form->control('role', [
                'options' => $roles ?? [
                    'scheduler' => 'Scheduler',
                    'digitizer' => 'Digitizer',
                    'quality_checker' => 'Quality Checker (QC)',
                    'production' => 'Production',
                    'admin' => 'Admin',
                ],
                'empty' => 'Select role',
                'class' => 'form-select',
                'required' => true,
                'label' => false,
                'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
            ]) ?>
        </div>

        <?= $this->Form->hidden('organization_id') ?>

        <div class="form-actions">
            <?= $this->Html->link('<i class="fas fa-list me-1"></i>Back to users', ['action' => 'index'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
            <?= $this->Form->button('<i class="fas fa-save me-2"></i>Create user', [
                'class' => 'btn btn-primary',
                'type' => 'submit',
                'escapeTitle' => false,
            ]) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>