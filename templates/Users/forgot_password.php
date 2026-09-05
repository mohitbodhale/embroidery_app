<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Forgot Password');
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
                <h1 class="auth-title">Forgot password?</h1>
                <p class="auth-sub">Enter your email and we'll send you a reset link.</p>
            </div>

            <?= $this->Flash->render() ?>

            <?= $this->Form->create(null, ['class' => 'auth-form']) ?>
                <div class="auth-field">
                    <label class="auth-label" for="email">Email</label>
                    <?= $this->Form->control('email', [
                        'class' => 'auth-input',
                        'label' => false,
                        'placeholder' => 'you@example.com',
                        'required' => true,
                    ]) ?>
                </div>

                <div class="auth-foot">
                    <button class="btn btn-primary w-100 btn-auth" type="submit">
                        <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                    </button>
                </div>
            <?= $this->Form->end() ?>

            <div class="auth-foot" style="margin-top: 20px;">
                <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i> Back to login', ['action' => 'login'], ['escape' => false]) ?>
            </div>
        </div>
    </main>
</div>
