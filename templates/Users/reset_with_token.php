<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $token
 */
$this->assign('title', 'Reset Password');
?>
<div class="auth-page auth-page-fit">
    <aside class="auth-aside">
        <div class="auth-aside-inner">
            <a href="<?= $this->Url->build('/') ?>" class="auth-aside-brand">
                <span class="auth-aside-logo"><i class="fas fa-stitches"></i></span>
                <strong>Embroidery</strong>
            </a>
        </div>
    </aside>
    <main class="auth-main">
        <div class="auth-card">
            <div class="auth-card-header">
                <h1 class="auth-title">Choose a new password</h1>
                <p class="auth-sub">Resetting password for <strong><?= h($user->email) ?></strong></p>
            </div>

            <?= $this->Flash->render() ?>

            <?= $this->Form->create(null, ['class' => 'auth-form']) ?>
                <div class="auth-field">
                    <label class="auth-label" for="password">New Password</label>
                    <?= $this->Form->control('password', [
                        'class' => 'auth-input',
                        'label' => false,
                        'placeholder' => 'Enter new password',
                        'required' => true,
                        'minLength' => 6,
                    ]) ?>
                </div>

                <div class="auth-field">
                    <label class="auth-label" for="password_confirm">Confirm Password</label>
                    <?= $this->Form->control('password_confirm', [
                        'type' => 'password',
                        'class' => 'auth-input',
                        'label' => false,
                        'placeholder' => 'Repeat new password',
                        'required' => true,
                    ]) ?>
                </div>

                <div class="auth-foot">
                    <button class="btn btn-primary w-100 btn-auth" type="submit">
                        <i class="fas fa-key me-2"></i>Reset Password
                    </button>
                </div>
            <?= $this->Form->end() ?>

            <div class="auth-foot" style="margin-top: 20px;">
                <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i> Back to login', ['action' => 'login'], ['escape' => false]) ?>
            </div>
        </div>
    </main>
</div>
