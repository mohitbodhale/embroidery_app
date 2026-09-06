<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Sign In');
?>
<div class="auth-page auth-page-centered auth-page-fit">
    <!-- 3D floating elements -->
    <div class="auth-3d-elements" aria-hidden="true">
        <div class="auth-3d-cube">
            <div class="cube-face face-1"></div>
            <div class="cube-face face-2"></div>
            <div class="cube-face face-3"></div>
            <div class="cube-face face-4"></div>
        </div>
        <div class="auth-3d-prism">
            <div class="prism-face f1"></div>
            <div class="prism-face f2"></div>
            <div class="prism-face f3"></div>
        </div>
        <div class="auth-3d-dots">
            <span></span><span></span><span></span>
        </div>
    </div>

    <main class="auth-main auth-main-centered">
        <div class="auth-card auth-card-narrow auth-card-elevated auth-card-center">
            <div class="auth-card-brand">
                <img src="<?= $this->Url->build('/img/brand-logo.svg') ?>" alt="TrackBridge" class="auth-brand-logo" />
                <div class="auth-brand-text">
                    <strong>TrackBridge</strong>
                    <span class="auth-slogan">Job &middot; Workflow &middot; QC</span>
                </div>
            </div>

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

            <div class="auth-card-footer">
                <span class="auth-copy">&copy; <?= date('Y') ?> TrackBridge</span>
                <span class="auth-divider-inline">·</span>
                <span class="auth-slogan">Job &middot; Workflow &middot; QC</span>
                <span class="auth-divider-inline">·</span>
                <?= $this->Html->link('<i class="fas fa-house me-1"></i> Home', ['controller' => 'Pages', 'action' => 'welcome'], ['escape' => false, 'class' => 'auth-home-link']) ?>
            </div>
        </div>
    </main>
</div>
