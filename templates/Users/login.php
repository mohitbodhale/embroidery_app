<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Sign In');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="TrackBridge — Sign in to your workspace">
    <title>Sign In &middot; TrackBridge</title>
    <?= $this->Html->meta('icon', '/img/brand-logo.svg') ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">

    <?= $this->Html->css([
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
        '/css/welcome',
    ]) ?>

    <style>
        :root {
            --tb-primary: #3b82f6;
            --tb-primary-dark: #2563eb;
            --tb-accent: #a78bfa;
            --tb-ink: #f8fafc;
            --tb-ink-soft: #94a3b8;
            --tb-bg: #0a0e1a;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow-x: hidden;
            font-family: 'Inter', 'Source Sans 3', sans-serif;
        }

        body.tb-body {
            background-color: var(--tb-bg);
            color: var(--tb-ink);
            overflow-x: hidden;
        }

        /* Background blobs */
        .tb-bg {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: float 20s ease-in-out infinite;
        }
        .blob-1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.3), transparent 70%);
            top: -200px; left: -100px;
            animation-delay: 0s;
        }
        .blob-2 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(167, 139, 250, 0.25), transparent 70%);
            bottom: -150px; right: -100px;
            animation-delay: -7s;
        }
        .blob-3 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.2), transparent 70%);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -14s;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -30px) scale(1.05); }
            50% { transform: translate(-20px, 20px) scale(0.95); }
            75% { transform: translate(20px, 30px) scale(1.02); }
        }

        /* Grid overlay */
        .tb-optical-grid {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            background-image:
                linear-gradient(rgba(59, 130, 246, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(59, 130, 246, 0.03) 1px, transparent 1px);
            background-size: 60px 60px;
        }

        /* Auth page layout */
        .auth-page {
            display: flex;
            width: 100vw;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* Left Panel */
        .auth-aside {
            flex: 0 0 45%;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(30, 41, 59, 0.9) 100%);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
            overflow: hidden;
        }
        .auth-aside::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.15), transparent 70%);
            top: -100px; right: -100px;
            border-radius: 50%;
        }

        .auth-aside-inner {
            max-width: 480px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .auth-aside-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            color: #ffffff;
            text-decoration: none;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 2.5rem;
            transition: opacity 0.3s ease;
        }
        .auth-aside-brand:hover {
            opacity: 0.85;
        }

        .auth-brand-logo {
            height: 40px;
            width: auto;
        }

        .auth-aside-title {
            font-size: 2.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            letter-spacing: -0.02em;
        }

        .auth-aside-title span {
            background: linear-gradient(135deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .auth-aside-text {
            color: #94a3b8;
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .auth-aside-features {
            list-style: none;
            padding: 0;
            margin: 0 0 3rem 0;
        }

        .auth-aside-features li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            color: #cbd5e1;
            font-size: 0.95rem;
            margin-bottom: 0.85rem;
            line-height: 1.5;
        }

        .auth-aside-features i {
            color: #34d399;
            margin-top: 0.2rem;
            flex-shrink: 0;
        }

        .auth-aside-foot {
            color: #64748b;
            font-size: 0.8rem;
            margin: 0;
        }

        /* Right Panel */
        .auth-main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }

        .auth-form-wrap {
            width: 100%;
            max-width: 420px;
        }

        .auth-mobile-brand {
            display: none;
        }

        /* Card */
        .auth-card {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.25rem;
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        .auth-card-body {
            padding: 2.5rem 2rem;
        }

        .auth-icon-wrap {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(167, 139, 250, 0.15));
            color: #60a5fa;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .auth-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 0.5rem 0;
        }

        .auth-sub {
            color: #94a3b8;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .auth-field {
            margin-bottom: 1.25rem;
        }

        .auth-label {
            display: block;
            color: #cbd5e1;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .auth-input-group {
            display: flex;
            border-radius: 0.625rem;
            overflow: hidden;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .auth-input-group:focus-within {
            border-color: var(--tb-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: #64748b;
            padding: 0.85rem 1rem;
            display: flex;
            align-items: center;
            transition: color 0.3s ease;
        }

        .auth-input-group:focus-within .input-group-text {
            color: #60a5fa;
        }

        .auth-input {
            width: 100%;
            background: transparent;
            border: none;
            color: #ffffff;
            padding: 0.85rem 1rem 0.85rem 0;
            outline: none;
            font-size: 0.95rem;
        }

        .auth-input::placeholder {
            color: #475569;
        }

        .auth-remember-label {
            color: #94a3b8;
            font-size: 0.85rem;
        }

        .form-check-input:checked {
            background-color: var(--tb-primary);
            border-color: var(--tb-primary);
        }

        .btn-auth {
            padding: 0.85rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 0.625rem;
            background: linear-gradient(135deg, var(--tb-primary), var(--tb-primary-dark));
            border: none;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.4);
            transition: all 0.3s ease;
        }

        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.5);
            background: linear-gradient(135deg, var(--tb-primary-dark), #1d4ed8);
        }

        .auth-divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #475569;
            margin: 1.5rem 0;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .auth-divider span {
            padding: 0 0.75rem;
        }

        .auth-foot a {
            color: #60a5fa;
            text-decoration: none;
            font-size: 0.9rem;
            display: block;
            text-align: center;
            transition: color 0.3s ease;
        }

        .auth-foot a:hover {
            color: #93c5fd;
        }

        .auth-link {
            color: #60a5fa;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .auth-link:hover {
            color: #93c5fd;
            text-decoration: underline;
        }

        /* Alert styling */
        .alert {
            border-radius: 0.625rem;
            border: none;
            font-size: 0.9rem;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
            border-left: 4px solid #ef4444;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            color: #86efac;
            border-left: 4px solid #22c55e;
        }

        /* Mobile Responsive */
        @media (max-width: 991px) {
            .auth-page {
                flex-direction: column;
            }
            .auth-aside {
                display: none;
            }
            .auth-mobile-brand {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                color: #fff;
                text-decoration: none;
                font-weight: 700;
                font-size: 1.1rem;
                margin-bottom: 1.5rem;
            }
            .auth-aside-title {
                font-size: 2rem;
            }
            .auth-card-body {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body class="tb-body">
    <!-- Background effects -->
    <div class="tb-bg" aria-hidden="true">
        <span class="blob blob-1"></span>
        <span class="blob blob-2"></span>
        <span class="blob blob-3"></span>
    </div>
    <div class="tb-optical-grid" aria-hidden="true"></div>

    <div class="auth-page">
        <!-- Left panel -->
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

                <div class="auth-card">
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

                            <div class="auth-field d-flex justify-content-between align-items-center mb-3">
                                <label class="mb-0">
                                    <?= $this->Form->checkbox('remember_me', ['class' => 'form-check-input me-2']) ?>
                                    <span class="auth-remember-label">Remember me</span>
                                </label>
                                <?= $this->Html->link('Forgot password?', ['action' => 'forgotPassword'], ['class' => 'auth-link small']) ?>
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
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
