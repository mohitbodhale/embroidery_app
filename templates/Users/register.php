<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Create account');
?>
<div class="auth-page">
    <!-- Left panel — brand / illustration -->
    <aside class="auth-aside">
        <div class="auth-aside-inner">
            <a href="<?= $this->Url->build('/') ?>" class="auth-aside-brand">
                <img src="<?= $this->Url->build('/img/brand-logo.svg') ?>" alt="TrackBridge" class="auth-brand-logo" />
                <span>TrackBridge</span>
            </a>

            <h2 class="auth-aside-title">Join the workflow.<br><span>One team.</span><br>One pipeline.</h2>
            <p class="auth-aside-text">Create your account to get started. An admin will assign your role and you will be ready to schedule jobs, digitize work, run QC, or move jobs through production.</p>

            <ul class="auth-aside-features">
                <li><i class="fas fa-check-circle"></i> Scheduler — post and assign jobs</li>
                <li><i class="fas fa-check-circle"></i> Digitizer — digitize and submit for QC</li>
                <li><i class="fas fa-check-circle"></i> QC — review, approve, or reject work</li>
                <li><i class="fas fa-check-circle"></i> Production — complete approved jobs</li>
            </ul>

            <p class="auth-aside-foot">&copy; <?= date('Y') ?> TrackBridge. All rights reserved.</p>
        </div>
    </aside>

    <!-- Right panel — form -->
    <main class="auth-main">
        <div class="auth-form-wrap">
            <a href="<?= $this->Url->build('/') ?>" class="auth-mobile-brand">
                <img src="<?= $this->Url->build('/img/brand-logo.svg') ?>" alt="TrackBridge" class="auth-brand-logo" />
                <span>TrackBridge</span>
            </a>

            <div class="auth-card auth-card-narrow auth-card-elevated">
                <div class="auth-card-body">
                    <div class="auth-icon-wrap">
                        <i class="fas fa-user-plus"></i>
                    </div>

                    <h1 class="auth-title">Create your account</h1>
                    <p class="auth-sub">Join TrackBridge — an admin will assign your role.</p>

                    <?= $this->Flash->render() ?>

                    <?= $this->Form->create($user ?? null, ['class' => 'auth-form']) ?>

                        <div class="auth-field">
                            <label class="auth-label" for="name">Full name</label>
                            <div class="input-group auth-input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <?= $this->Form->control('name', [
                                    'class' => 'form-control auth-input',
                                    'placeholder' => 'Jane Doe',
                                    'required' => true,
                                    'label' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'inputContainerError' => '{{content}}{{error}}',
                                    ],
                                ]) ?>
                            </div>
                        </div>

                        <div class="auth-field">
                            <label class="auth-label" for="email">Email address</label>
                            <div class="input-group auth-input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <?= $this->Form->control('email', [
                                    'class' => 'form-control auth-input',
                                    'type' => 'email',
                                    'placeholder' => 'you@example.com',
                                    'required' => true,
                                    'label' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'inputContainerError' => '{{content}}{{error}}',
                                    ],
                                ]) ?>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6 auth-field mb-0">
                                <label class="auth-label" for="password">Password</label>
                                <div class="input-group auth-input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <?= $this->Form->control('password', [
                                        'class' => 'form-control auth-input',
                                        'type' => 'password',
                                        'placeholder' => 'Min 6 characters',
                                        'required' => true,
                                        'label' => false,
                                        'templates' => [
                                            'inputContainer' => '{{content}}',
                                            'inputContainerError' => '{{content}}{{error}}',
                                        ],
                                    ]) ?>
                                </div>
                            </div>
                            <div class="col-md-6 auth-field mb-0">
                                <label class="auth-label" for="password_confirm">Confirm password</label>
                                <div class="input-group auth-input-group">
                                    <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                                    <?= $this->Form->control('password_confirm', [
                                        'class' => 'form-control auth-input',
                                        'type' => 'password',
                                        'placeholder' => 'Repeat password',
                                        'required' => true,
                                        'label' => false,
                                        'templates' => [
                                            'inputContainer' => '{{content}}',
                                            'inputContainerError' => '{{content}}{{error}}',
                                        ],
                                    ]) ?>
                                </div>
                            </div>
                        </div>

                        <div class="auth-foot mt-3">
                            <button class="btn btn-primary w-100 btn-auth" type="submit">
                                <i class="fas fa-paper-plane me-2"></i>Create account
                            </button>
                        </div>
                    <?= $this->Form->end() ?>

                    <div class="auth-divider"><span>or</span></div>

                    <div class="auth-foot mt-2">
                        <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i> Already have an account? Sign in', ['action' => 'login'], ['escape' => false]) ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
