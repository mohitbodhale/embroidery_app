<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $token
 */
$this->assign('title', 'Reset Password');
?>
<div class="auth-page auth-page-centered">
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
                    <i class="fas fa-key"></i>
                </div>

                <h1 class="auth-title">Choose a new password</h1>
                <p class="auth-sub">Resetting password for <strong><?= h($user->email) ?></strong></p>

                <?= $this->Flash->render() ?>

                <?= $this->Form->create(null, ['class' => 'auth-form']) ?>
                    <div class="auth-field">
                        <label class="auth-label" for="password">New Password</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <?= $this->Form->control('password', [
                                'class' => 'form-control auth-input',
                                'label' => false,
                                'placeholder' => 'Enter new password',
                                'required' => true,
                                'minLength' => 6,
                            ]) ?>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label class="auth-label" for="password_confirm">Confirm Password</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                            <?= $this->Form->control('password_confirm', [
                                'class' => 'form-control auth-input',
                                'type' => 'password',
                                'label' => false,
                                'placeholder' => 'Repeat new password',
                                'required' => true,
                            ]) ?>
                        </div>
                    </div>

                    <div class="auth-foot">
                        <button class="btn btn-primary w-100 btn-auth" type="submit">
                            <i class="fas fa-key me-2"></i>Reset Password
                        </button>
                    </div>
                <?= $this->Form->end() ?>

                <div class="auth-divider"><span>or</span></div>

                <div class="auth-foot">
                    <?= $this->Html->link('<i class="fas fa-arrow-left me-1"></i> Back to sign in', ['action' => 'login'], ['escape' => false]) ?>
                </div>

                <div class="auth-help">
                    <i class="fas fa-shield-halved me-1"></i>
                    Reset links expire after 1 hour for security.
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
