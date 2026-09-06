<?php /** @var \App\View\AppView $this */ $this->assign('title', 'Create account'); ?>
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
                                'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
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
                                'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
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
                                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
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
                                    'templates' => ['inputContainer' => '{{content}}', 'inputContainerError' => '{{content}}{{error}}'],
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
