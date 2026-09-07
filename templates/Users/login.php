<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Sign In');
?>
<div class="auth-page">
    <!-- Left panel — brand / illustration -->
    <aside class="auth-aside">
        <div class="auth-aside-inner">
            <a href="<?= $this->Url->build('/') ?>" class="auth-aside-brand">
                <img src="<?= $this->Url->build('/img/brand-logo.svg') ?>" alt="TrackBridge" class="auth-brand-logo" />
                <span>TrackBridge</span>
            </a>

            <h2 class="auth-aside-title">Schedule jobs.<br><span>Track every handoff.</span><br>Catch every QC fix.</h2>
            <p class="auth-aside-text">TrackBridge connects your schedulers, operators, and QC into a single, visible pipeline. Post a job, assign the right person, and review the output — without losing a file, a note, or a revision.</p>

            <ul class="auth-aside-features">
                <li><i class="fas fa-check-circle"></i> Schedulers post any job — designs, files, instructions</li>
                <li><i class="fas fa-check-circle"></i> Operators work the same way, regardless of their role</li>
                <li><i class="fas fa-check-circle"></i> QC reviews and returns work to the original operator</li>
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
                        <i class="fas fa-right-to-bracket"></i>
                    </div>

                    <h1 class="auth-title">Welcome back</h1>
                    <p class="auth-sub">Sign in to your TrackBridge workspace</p>

                    <?= $this->Flash->render() ?>

                    <?= $this->Form->create(null, ['class' => 'auth-form']) ?>
                        <div class="auth-field">
                            <label class="auth-label" for="email">Email address</label>
                            <div class="input-group auth-input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <?= $this->Form->control('email', [
                                    'class' => 'form-control auth-input',
                                    'placeholder' => 'you@example.com',
                                    'required' => true,
                                    'autofocus' => true,
                                    'label' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'inputContainerError' => '{{content}}{{error}}',
                                    ],
                                ]) ?>
                            </div>
                        </div>

                        <div class="auth-field">
                            <label class="auth-label" for="password">Password</label>
                            <div class="input-group auth-input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <?= $this->Form->control('password', [
                                    'class' => 'form-control auth-input',
                                    'placeholder' => 'Enter your password',
                                    'required' => true,
                                    'label' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'inputContainerError' => '{{content}}{{error}}',
                                    ],
                                ]) ?>
                            </div>
                        </div>

                        <div class="auth-field d-flex justify-content-between align-items-center mb-2">
                            <label class="mb-0">
                                <?= $this->Form->checkbox('remember_me', ['class' => 'form-check-input me-1']) ?>
                                <span class="auth-remember-label">Remember me</span>
                            </label>
                        </div>

                        <div class="auth-foot">
                            <button class="btn btn-primary w-100 btn-auth" type="submit">
                                <i class="fas fa-arrow-right me-2"></i>Sign In
                            </button>
                        </div>
                    <?= $this->Form->end() ?>

                    <div class="auth-divider"><span>or</span></div>

                    <div class="auth-foot">
                        <?= $this->Html->link('<i class="fas fa-user-plus me-1"></i> Create account', ['action' => 'register'], ['escape' => false]) ?>
                    </div>

                    <div class="auth-sub small text-center mt-3">
                        <?= $this->Html->link('<i class="fas fa-key me-1"></i> Forgot password?', ['action' => 'forgotPassword'], ['escape' => false]) ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
