<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$this->assign('title', 'My Profile');
?>
<div class="page-card card">
    <div class="card-header">
        <h3 class="card-title m-0"><i class="fas fa-user-circle me-2"></i>My Profile</h3>
    </div>
    <div class="card-body">
        <?= $this->Form->create($user, ['type' => 'file']) ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label>Full name</label>
                <?= $this->Form->control('name', [
                    'class' => 'form-control',
                    'label' => false,
                    'required' => true,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
            <div class="col-md-6">
                <label>Email address</label>
                <?= $this->Form->control('email', [
                    'class' => 'form-control',
                    'label' => false,
                    'required' => true,
                    'readonly' => true,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
            <div class="col-md-6">
                <label>New password <span class="text-muted small">(leave blank to keep current)</span></label>
                <?= $this->Form->control('password', [
                    'type' => 'password',
                    'class' => 'form-control',
                    'label' => false,
                    'minLength' => 6,
                    'value' => '',
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
            <div class="col-md-6">
                <label>Confirm password</label>
                <?= $this->Form->control('password_confirm', [
                    'type' => 'password',
                    'class' => 'form-control',
                    'label' => false,
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                ]) ?>
            </div>
        </div>

        <hr>

        <h4 class="mb-3"><i class="fas fa-id-card me-2"></i>Additional Information</h4>
        <div class="row g-3">
            <div class="col-md-6">
                <label>Phone</label>
                <?= $this->Form->control('phone', [
                    'class' => 'form-control',
                    'label' => false,
                    'placeholder' => '+1 (555) 000-0000',
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                    'value' => $user->user_detail->phone ?? '',
                ]) ?>
            </div>
            <div class="col-md-6">
                <label>Location</label>
                <?= $this->Form->control('location', [
                    'class' => 'form-control',
                    'label' => false,
                    'placeholder' => 'City, Country',
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                    'value' => $user->user_detail->location ?? '',
                ]) ?>
            </div>
            <div class="col-md-6">
                <label>Website</label>
                <?= $this->Form->control('website', [
                    'class' => 'form-control',
                    'label' => false,
                    'placeholder' => 'https://example.com',
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                    'value' => $user->user_detail->website ?? '',
                ]) ?>
            </div>
            <div class="col-md-6">
                <label>Profile picture</label>
                <?php if (!empty($user->user_detail->avatar)): ?>
                    <div class="mb-2">
                        <img src="<?= $this->Url->webroot($user->user_detail->avatar) ?>" alt="Avatar" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                    </div>
                <?php endif; ?>
                <?= $this->Form->control('avatar', [
                    'type' => 'file',
                    'class' => 'form-control',
                    'label' => false,
                    'accept' => 'image/*',
                    'templates' => ['inputContainer' => '{{content}}'],
                ]) ?>
                <div class="form-text">JPG, PNG or GIF. Max 2MB.</div>
            </div>
            <div class="col-12">
                <label>Bio</label>
                <?= $this->Form->control('bio', [
                    'type' => 'textarea',
                    'class' => 'form-control',
                    'label' => false,
                    'rows' => 3,
                    'placeholder' => 'Tell us a bit about yourself...',
                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                    'value' => $user->user_detail->bio ?? '',
                ]) ?>
            </div>
        </div>

        <div class="form-actions mt-4">
            <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i>Back', ['action' => 'index'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
            <?= $this->Form->button('<i class="fas fa-save me-1"></i>Save changes', [
                'class' => 'btn btn-primary',
                'type' => 'submit',
                'escapeTitle' => false,
            ]) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>
