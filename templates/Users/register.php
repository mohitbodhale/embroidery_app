<?php /** @var \App\View\AppView $this */ $this->assign('title', 'Create account'); ?>
<div class="auth-page">
    <aside class="auth-aside">
        <div class="auth-aside-inner">
            <a href="<?= $this->Url->build('/') ?>" class="auth-aside-brand">
                <span class="auth-aside-logo"><i class="fas fa-stitches"></i></span>
                <span>Embroidery</span>
            </a>
            <h2 class="auth-aside-title">Join your production team.</h2>
            <p class="auth-aside-text">
                Create your account and an administrator will assign the role that matches your work — scheduler, digitizer, QC or production.
            </p>
            <ul class="auth-aside-features">
                <li><i class="fas fa-shield-alt"></i> Secure password hashing</li>
                <li><i class="fas fa-user-check"></i> Admin-approved role assignment</li>
                <li><i class="fas fa-bolt"></i> Instant access after approval</li>
            </ul>
            <div class="auth-aside-foot">
                <span>&copy; <?= date('Y') ?> Embroidery System</span>
                <span>v1.0.0</span>
            </div>
        </div>
    </aside>

    <main class="auth-main">
        <div class="auth-form-wrap">
            <a href="<?= $this->Url->build('/') ?>" class="auth-mobile-brand">
                <span class="auth-aside-logo"><i class="fas fa-stitches"></i></span>
                <strong>Embroidery</strong>
            </a>

            <h1 class="auth-title">Create your account</h1>
            <p class="auth-sub">Fill in your details — an admin will assign your role.</p>

            <?= $this->Flash->render() ?>
            <?= $this->Form->create($user, ['class' => 'auth-form']) ?>

                <div class="auth-field">
                    <label>Full name</label>
                    <div class="input-group">
                        <?= $this->Form->control('name', [
                            'class' => 'form-control',
                            'placeholder' => 'Jane Doe',
                            'required' => true,
                            'label' => false,
                            'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                        ]) ?>
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                </div>

                <div class="auth-field">
                    <label>Email address</label>
                    <div class="input-group">
                        <?= $this->Form->control('email', [
                            'class' => 'form-control',
                            'type' => 'email',
                            'placeholder' => 'you@example.com',
                            'required' => true,
                            'label' => false,
                            'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                        ]) ?>
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-md-6 auth-field mb-0">
                        <label>Password</label>
                        <div class="input-group">
                            <?= $this->Form->control('password', [
                                'class' => 'form-control',
                                'type' => 'password',
                                'placeholder' => 'Min 6 characters',
                                'required' => true,
                                'label' => false,
                                'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                            ]) ?>
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                    </div>
                    <div class="col-md-6 auth-field mb-0">
                        <label>Confirm password</label>
                        <div class="input-group">
                            <?= $this->Form->control('password_confirm', [
                                'class' => 'form-control',
                                'type' => 'password',
                                'placeholder' => 'Repeat password',
                                'required' => true,
                                'label' => false,
                                'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
                            ]) ?>
                            <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                        </div>
                    </div>
                </div>

                <?= $this->Form->button('<i class="fas fa-paper-plane me-2"></i>Create account', [
                    'class' => 'btn btn-primary w-100 btn-auth mt-4',
                    'type' => 'submit',
                    'escapeTitle' => false,
                ]) ?>
            <?= $this->Form->end() ?>

            <div class="auth-foot">
                <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i> Already have an account? Sign in', ['action' => 'login'], ['escape' => false]) ?>
            </div>
        </div>
    </main>
</div>