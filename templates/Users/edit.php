<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string[]|\Cake\Collection\CollectionInterface $organizations
 * @var string[]|\Cake\Collection\CollectionInterface $roles
 */
$this->assign('title', 'Edit user ' . $user->name);
?>
<div class="page-card card">
    <div class="card-body">
        <?= $this->Form->create($user, ['class' => 'user-edit-form']) ?>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Full name</label>
                <?= $this->Form->control('name', [
                    'class' => 'form-control',
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
                    'required' => true,
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>New password <small class="text-muted">(leave blank to keep current)</small></label>
                <?= $this->Form->control('password', [
                    'type' => 'password',
                    'class' => 'form-control',
                    'placeholder' => 'Leave blank to keep current password',
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
            <div class="col-md-6 mb-3">
                <label>Confirm new password</label>
                <?= $this->Form->control('password_confirm', [
                    'type' => 'password',
                    'class' => 'form-control',
                    'placeholder' => 'Re-enter new password',
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Role</label>
                <?= $this->Form->control('role', [
                    'options' => $roles ?? [
                        'pending' => 'Pending — no access',
                        'scheduler' => 'Scheduler',
                        'operator' => 'Operator',
                        'quality_checker' => 'Quality Checker (QC)',
                        'production' => 'Production',
                        'admin' => 'Admin',
                    ],
                    'class' => 'form-select',
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
            <div class="col-md-4 mb-3">
                <label>Organization</label>
                <?= $this->Form->control('organization_id', [
                    'options' => $organizations,
                    'class' => 'form-select',
                    'label' => false,
                    'empty' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
        </div>

        <div class="form-actions">
            <?= $this->Html->link('<i class="fas fa-list me-1"></i>Back to users', ['action' => 'index'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
            <div class="d-flex gap-2">
                <?= $this->Form->postLink('<i class="fas fa-trash me-1"></i>Delete', ['action' => 'delete', $user->id], [
                    'confirm' => __('Delete user # {0}?', $user->id),
                    'class' => 'btn btn-outline-danger',
                    'escape' => false,
                ]) ?>
                <?= $this->Form->button('<i class="fas fa-save me-2"></i>Save changes', [
                    'class' => 'btn btn-primary',
                    'type' => 'submit',
                    'escapeTitle' => false,
                ]) ?>
                <?= $this->Form->postLink('<i class="fas fa-key me-1"></i>Reset Password', ['action' => 'resetPassword', $user->id], [
                    'confirm' => __('Generate a new temporary password for {0}?', $user->name),
                    'class' => 'btn btn-warning',
                    'escape' => false,
                ]) ?>
            </div>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>