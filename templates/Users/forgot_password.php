<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Forgot Password');
?>
<div class="auth-page auth-page-centered">
    <main class="auth-main auth-main-centered">
        <div class="auth-card auth-card-narrow auth-card-elevated">
            <div class="auth-card-brand">
                <img src="<?= $this->Url->build('/img/brand-logo.svg') ?>" alt="TrackBridge" class="auth-brand-logo" />
                <div class="auth-brand-text">
                    <strong>TrackBridge</strong>
                    <span class="auth-slogan">Job &middot; Workflow &middot; QC</span>
                </div>
            </div>

            <div class="auth-card-body">
                <div class="auth-icon-wrap">
                    <i class="fas fa-lock"></i>
                </div>

                <h1 class="auth-title">Forgot your password?</h1>
                <p class="auth-sub">Enter the email address associated with your account and we'll send you a secure link to reset your password.</p>

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

                    <div class="auth-foot">
                        <button class="btn btn-primary w-100 btn-auth" type="submit">
                            <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                        </button>
                    </div>

                    <div class="auth-divider"><span>or</span></div>

                    <div class="auth-foot">
                        <a href="<?= $this->Url->build(['action' => 'login']) ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Sign In
                        </a>
                    </div>

                    <div class="auth-help">
                        <i class="fas fa-shield-halved me-1"></i>
                        Reset links expire after 1 hour for security.
                    </div>
                <?= $this->Form->end() ?>

                <div class="auth-card-footer">
                    <span class="auth-copy">© <?= date('Y') ?> TrackBridge</span>
                    <span class="auth-divider-inline">·</span>
                    <span class="auth-slogan">Job &middot; Workflow &middot; QC</span>
                    <span class="auth-divider-inline">·</span>
                    <a href="<?= $this->Url->build(['controller' => 'Pages', 'action' => 'welcome']) ?>" class="auth-home-link">Home</a>
                </div>
            </div>
        </div>
    </main>
</div>
