<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Sign In');
?>
<div class="auth-page auth-page-fit">
    <!-- Left brand panel (desktop only) -->
    <aside class="auth-aside">
        <div class="auth-aside-inner">
            <a href="<?= $this->Url->build('/') ?>" class="auth-aside-brand">
                <span class="auth-aside-logo"><i class="fas fa-stitches"></i></span>
                <span>Embroidery</span>
            </a>
            <h2 class="auth-aside-title">Production workflow, simplified.</h2>
            <p class="auth-aside-text">
                Schedule embroidery jobs, track digitizing &amp; QC, and keep your shop floor in sync — all from one place.
            </p>
            <ul class="auth-aside-features">
                <li><i class="fas fa-check-circle"></i> Role-aware dashboards for every team</li>
                <li><i class="fas fa-check-circle"></i> Real-time job status &amp; activity logs</li>
                <li><i class="fas fa-check-circle"></i> Attachments, QC review, production tracking</li>
            </ul>
            <div class="auth-aside-foot">
                <span>&copy; <?= date('Y') ?> Embroidery System</span>
                <span>v1.0.0</span>
            </div>
        </div>
    </aside>

    <!-- Right form panel -->
    <main class="auth-main">
        <div class="auth-form-wrap">
            <a href="<?= $this->Url->build('/') ?>" class="auth-mobile-brand">
                <span class="auth-aside-logo"><i class="fas fa-stitches"></i></span>
                <strong>Embroidery</strong>
            </a>

            <h1 class="auth-title">Welcome back</h1>
            <p class="auth-sub">Sign in to continue to your workspace</p>

            <?= $this->Flash->render() ?>

            <?= $this->Form->create(null, ['class' => 'auth-form']) ?>
                <div class="auth-field">
                    <label for="email">Email address</label>
                    <div class="input-group">
                        <?= $this->Form->control('email', [
                            'class' => 'form-control',
                            'placeholder' => 'you@example.com',
                            'required' => true,
                            'autofocus' => true,
                            'label' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'inputContainerError' => '{{content}}{{error}}',
                            ],
                        ]) ?>
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    </div>
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <?= $this->Form->control('password', [
                            'class' => 'form-control',
                            'placeholder' => 'Enter your password',
                            'required' => true,
                            'label' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'inputContainerError' => '{{content}}{{error}}',
                            ],
                        ]) ?>
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                </div>

                <?= $this->Form->button('<i class="fas fa-arrow-right me-2"></i>Sign In', [
                    'class' => 'btn btn-primary w-100 btn-auth',
                    'type' => 'submit',
                    'escapeTitle' => false,
                ]) ?>
            <?= $this->Form->end() ?>

            <div class="auth-foot">
                <?= $this->Html->link('<i class="fas fa-user-plus me-1"></i> Create account', ['action' => 'register'], ['escape' => false]) ?>
                <span class="auth-foot-sep">·</span>
                <?= $this->Html->link('<i class="fas fa-key me-1"></i> Forgot password?', ['action' => 'forgotPassword'], ['escape' => false]) ?>
            </div>
        </div>
    </main>
</div>