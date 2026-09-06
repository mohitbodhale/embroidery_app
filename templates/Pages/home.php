<?php
/**
 * @var \App\View\AppView $this
 * @var bool|null $loggedIn
 */
$this->disableAutoLayout();
$loggedIn = $loggedIn ?? ($currentUser ?? null) ? true : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="TrackBridge — Schedule jobs, route work to operators, and run QC reviews without losing anything.">
    <title>TrackBridge &middot; Schedule. Work. QC.</title>
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
    </style>
</head>
<body class="tb-body">
    <!-- Background orbs -->
    <div class="tb-bg" aria-hidden="true">
        <span class="blob blob-1"></span>
        <span class="blob blob-2"></span>
        <span class="blob blob-3"></span>
    </div>

    <!-- Subtle grid overlay -->
    <div class="tb-optical-grid" aria-hidden="true"></div>

    <!-- Minimal 3D elements -->
    <div class="tb-3d-elements" aria-hidden="true">
        <div class="tb-3d-shape tb-3d-cube">
            <div class="face f1"></div>
            <div class="face f2"></div>
            <div class="face f3"></div>
            <div class="face f4"></div>
        </div>
        <div class="tb-3d-shape tb-3d-ico">
            <div class="ico-face i1"></div>
            <div class="ico-face i2"></div>
            <div class="ico-face i3"></div>
            <div class="ico-face i4"></div>
            <div class="ico-face i5"></div>
        </div>
        <div class="tb-3d-shape tb-3d-ring">
            <div class="ring-seg rs1"></div>
            <div class="ring-seg rs2"></div>
            <div class="ring-seg rs3"></div>
            <div class="ring-seg rs4"></div>
            <div class="ring-seg rs5"></div>
            <div class="ring-seg rs6"></div>
        </div>
    </div>

    <!-- Floating particles -->
    <div class="tb-parallax-particles" id="tbParticles" aria-hidden="true"></div>

    <!-- Navigation -->
    <header class="tb-nav">
        <div class="container tb-nav-inner">
            <a class="tb-brand" href="/">
                <img src="<?= $this->Url->build('/img/brand-logo.svg') ?>" alt="TrackBridge" class="tb-brand-mark">
                <span class="tb-brand-name">Track<span>Bridge</span></span>
            </a>
            <nav class="tb-nav-links">
                <a href="#features">Features</a>
                <a href="#workflow">Workflow</a>
                <a href="#roles">Roles</a>
                <a href="#faq">FAQ</a>
            </nav>
            <div class="tb-nav-cta">
                <?php if ($loggedIn): ?>
                    <a class="btn btn-primary tb-btn tb-btn-lg" href="<?= $this->Url->build(['controller' => 'Pages', 'action' => 'dashboard']) ?>">
                        <i class="fas fa-gauge-high me-1"></i> Open dashboard
                    </a>
                <?php endif; ?>
            </div>
            <button class="tb-burger" id="tbBurger" aria-label="Toggle menu"><i class="fas fa-bars"></i></button>
        </div>
    </header>

    <!-- Hero -->
    <section class="tb-hero">
        <div class="container tb-hero-grid">
            <div class="tb-hero-copy">
                <span class="tb-eyebrow">
                    <i class="fas fa-bridge me-1"></i> Built for modern workshops — all job types, one pipeline
                </span>
                <h1 class="tb-hero-title">
                    Schedule jobs.<br>
                    <span class="tb-grad">Track every handoff.</span><br>
                    Catch every QC fix.
                </h1>
                <p class="tb-hero-sub">
                    TrackBridge connects your schedulers, operators, and QC into a single,
                    visible pipeline. Post a job, assign the right person, and review the
                    output — without losing a file, a note, or a revision.
                </p>
                <div class="tb-hero-cta">
                    <?php if ($loggedIn): ?>
                        <a class="btn btn-primary tb-btn tb-btn-lg" href="<?= $this->Url->build(['controller' => 'Pages', 'action' => 'dashboard']) ?>">
                            <i class="fas fa-gauge-high me-2"></i> Go to dashboard
                        </a>
                    <?php else: ?>
                        <a class="btn btn-primary tb-btn tb-btn-lg" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>">
                            <i class="fas fa-right-to-bracket me-2"></i> Sign in
                        </a>
                        <a class="btn btn-outline-primary tb-btn tb-btn-lg tb-btn-ghost" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'register']) ?>">
                            Create account
                        </a>
                    <?php endif; ?>
                </div>
                <ul class="tb-hero-bullets">
                    <li><i class="fas fa-check-circle"></i> Schedulers post any job — designs, files, instructions</li>
                    <li><i class="fas fa-check-circle"></i> Operators work the same way, regardless of their role</li>
                    <li><i class="fas fa-check-circle"></i> QC reviews and returns work to the original operator</li>
                </ul>
            </div>

            <div class="tb-hero-art">
                <div class="tb-card tb-card-float">
                    <div class="tb-card-head">
                        <div class="tb-dots"><span></span><span></span><span></span></div>
                        <span class="tb-card-title">Job #TB-1042 · Logo design</span>
                    </div>
                    <div class="tb-card-body">
                        <div class="tb-track">
                            <div class="tb-step done">
                                <span class="dot"></span>
                                <div>
                                    <strong>Posted by Scheduler</strong>
                                    <small>2 attachments, 1 instruction</small>
                                </div>
                                <em>10:14</em>
                            </div>
                            <div class="tb-step done">
                                <span class="dot"></span>
                                <div>
                                    <strong>Assigned → Operator</strong>
                                    <small>Anna K.</small>
                                </div>
                                <em>10:18</em>
                            </div>
                            <div class="tb-step active">
                                <span class="dot pulse"></span>
                                <div>
                                    <strong>In progress</strong>
                                    <small>file uploaded: design.dst</small>
                                </div>
                                <em>now</em>
                            </div>
                            <div class="tb-step">
                                <span class="dot"></span>
                                <div>
                                    <strong>QC review</strong>
                                    <small>awaiting review</small>
                                </div>
                                <em>—</em>
                            </div>
                            <div class="tb-step">
                                <span class="dot"></span>
                                <div>
                                    <strong>Production</strong>
                                    <small>—</small>
                                </div>
                                <em>—</em>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tb-card tb-card-mini tb-card-reject">
                    <i class="fas fa-rotate-left"></i>
                    <div>
                        <strong>Returned to operator</strong>
                        <small>QC: "Reduce stitch density on leaves"</small>
                    </div>
                </div>
                <div class="tb-card tb-card-mini tb-card-approve">
                    <i class="fas fa-circle-check"></i>
                    <div>
                        <strong>QC approved</strong>
                        <small>Sent to production queue</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <section class="tb-stats">
        <div class="container">
            <div class="tb-stats-grid tb-reveal">
                <div class="tb-stat">
                    <span class="tb-stat-num">1</span>
                    <span class="tb-stat-label">pipeline from post to production</span>
                </div>
                <div class="tb-stat">
                    <span class="tb-stat-num">5</span>
                    <span class="tb-stat-label">roles working in lockstep</span>
                </div>
                <div class="tb-stat">
                    <span class="tb-stat-num">100%</span>
                    <span class="tb-stat-label">of every revision tracked</span>
                </div>
                <div class="tb-stat">
                    <span class="tb-stat-num">0</span>
                    <span class="tb-stat-label">files lost between handoffs</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="tb-section tb-section-alt">
        <div class="container">
            <div class="tb-section-head tb-reveal">
                <span class="tb-eyebrow"><i class="fas fa-sparkles me-1"></i> What you get</span>
                <h2>One workspace for the whole job lifecycle</h2>
                <p>Every step is recorded. Every handoff is visible. Nothing falls through the cracks.</p>
            </div>
            <div class="tb-features">
                <div class="tb-feature tb-reveal">
                    <div class="tb-feature-ic tb-ic-blue"><i class="fas fa-diagram-project"></i></div>
                    <h3>Visible pipeline</h3>
                    <p>See where every job sits — in progress, under review, approved, or in production — at a glance.</p>
                </div>
                <div class="tb-feature tb-reveal">
                    <div class="tb-feature-ic tb-ic-violet"><i class="fas fa-people-arrows"></i></div>
                    <h3>Smart assignment</h3>
                    <p>Schedulers pick the right operator for the work. One flexible <em>assigned&nbsp;to</em> field — no rigid role columns.</p>
                </div>
                <div class="tb-feature tb-reveal">
                    <div class="tb-feature-ic tb-ic-cyan"><i class="fas fa-magnifying-glass-chart"></i></div>
                    <h3>QC return-to-sender</h3>
                    <p>When QC spots an issue, the job goes straight back to the original operator — with notes, every time.</p>
                </div>
                <div class="tb-feature tb-reveal">
                    <div class="tb-feature-ic tb-ic-green"><i class="fas fa-paperclip"></i></div>
                    <h3>Files stay attached</h3>
                    <p>Designs, instructions, references and outputs all live with the job. Everyone sees the same source of truth.</p>
                </div>
                <div class="tb-feature tb-reveal">
                    <div class="tb-feature-ic tb-ic-orange"><i class="fas fa-clock-rotate-left"></i></div>
                    <h3>Full audit trail</h3>
                    <p>Every assignment, every QC decision, every revision is timestamped and reviewable later.</p>
                </div>
                <div class="tb-feature tb-reveal">
                    <div class="tb-feature-ic tb-ic-pink"><i class="fas fa-user-shield"></i></div>
                    <h3>Role-aware access</h3>
                    <p>Schedulers, operators, QC, production &amp; admins each get a workspace that fits what they actually do.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Workflow -->
    <section id="workflow" class="tb-section">
        <div class="container">
            <div class="tb-section-head tb-reveal">
                <span class="tb-eyebrow"><i class="fas fa-route me-1"></i> How it flows</span>
                <h2>From post to production, without losing anything</h2>
                <p>TrackBridge is built around the real way workshops work.</p>
            </div>

            <ol class="tb-flow">
                <li class="tb-reveal">
                    <span class="tb-flow-num">1</span>
                    <div class="tb-flow-card">
                        <h3><i class="fas fa-cloud-arrow-up me-2"></i>Scheduler posts the job</h3>
                        <p>Drop in designs, files, and instructions. Anyone in the shop sees the brief.</p>
                    </div>
                </li>
                <li class="tb-reveal">
                    <span class="tb-flow-num">2</span>
                    <div class="tb-flow-card">
                        <h3><i class="fas fa-user-plus me-2"></i>Scheduler assigns the right operator</h3>
                        <p>Any operator — whoever the work needs. One assignment, fully tracked.</p>
                    </div>
                </li>
                <li class="tb-reveal">
                    <span class="tb-flow-num">3</span>
                    <div class="tb-flow-card">
                        <h3><i class="fas fa-screwdriver-wrench me-2"></i>Operator does the work</h3>
                        <p>Uploads the output, leaves notes, marks the step complete. Same flow for every role.</p>
                    </div>
                </li>
                <li class="tb-reveal">
                    <span class="tb-flow-num">4</span>
                    <div class="tb-flow-card">
                        <h3><i class="fas fa-magnifying-glass me-2"></i>QC reviews the output</h3>
                        <p>Checks files, references and the original brief. Approves — or sends it back with clear notes.</p>
                    </div>
                </li>
                <li class="tb-reveal">
                    <span class="tb-flow-num">5</span>
                    <div class="tb-flow-card">
                        <h3><i class="fas fa-rotate-left me-2"></i>Returned to operator if needed</h3>
                        <p>The job goes straight back to the original operator. The cycle repeats — until it's right.</p>
                    </div>
                </li>
                <li class="tb-reveal">
                    <span class="tb-flow-num">6</span>
                    <div class="tb-flow-card tb-flow-card-final">
                        <h3><i class="fas fa-flag-checkered me-2"></i>Approved → Production</h3>
                        <p>Once QC signs off, the job moves to the production queue. Everyone knows it's done.</p>
                    </div>
                </li>
            </ol>
        </div>
    </section>

    <!-- Roles -->
    <section id="roles" class="tb-section tb-section-alt">
        <div class="container">
            <div class="tb-section-head tb-reveal">
                <span class="tb-eyebrow"><i class="fas fa-users me-1"></i> Roles</span>
                <h2>One team, one pipeline</h2>
                <p>Everyone logs in, sees what they need, and does their part.</p>
            </div>
            <div class="tb-roles">
                <div class="tb-role tb-reveal">
                    <div class="tb-role-avatar tb-role-scheduler"><i class="fas fa-calendar-plus"></i></div>
                    <h3>Scheduler</h3>
                    <p>Posts jobs, attaches files &amp; instructions, picks the right operator and the right QC.</p>
                </div>
                <div class="tb-role tb-reveal">
                    <div class="tb-role-avatar tb-role-operator"><i class="fas fa-screwdriver-wrench"></i></div>
                    <h3>Operator</h3>
                    <p>Picks up assigned jobs from a clean queue and gets the work done.</p>
                </div>
                <div class="tb-role tb-reveal">
                    <div class="tb-role-avatar tb-role-qc"><i class="fas fa-clipboard-check"></i></div>
                    <h3>QC</h3>
                    <p>Reviews the operator's output, leaves clear notes, approves or returns the job.</p>
                </div>
                <div class="tb-role tb-reveal">
                    <div class="tb-role-avatar tb-role-production"><i class="fas fa-industry"></i></div>
                    <h3>Production</h3>
                    <p>Picks up QC-approved jobs and moves them through the shop.</p>
                </div>
                <div class="tb-role tb-reveal">
                    <div class="tb-role-avatar tb-role-admin"><i class="fas fa-user-shield"></i></div>
                    <h3>Admin</h3>
                    <p>Manages users, roles, and oversight across the whole workflow.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="tb-section">
        <div class="container">
            <div class="tb-section-head tb-reveal">
                <span class="tb-eyebrow"><i class="fas fa-circle-question me-1"></i> FAQ</span>
                <h2>Quick answers</h2>
            </div>
            <div class="tb-faq">
                <details class="tb-reveal">
                    <summary>Who posts jobs?</summary>
                    <p>Schedulers post any job — designs, files, instructions. They also pick the operator who will do the work and the QC who will check it.</p>
                </details>
                <details class="tb-reveal">
                    <summary>Which operators pick up jobs?</summary>
                    <p>No operator type is locked. Any operator can pick up any job from the same queue.</p>
                </details>
                <details class="tb-reveal">
                    <summary>What happens if QC rejects the work?</summary>
                    <p>The job is automatically returned to the operator who did it, along with QC's notes. They fix it and resubmit. The whole loop is recorded.</p>
                </details>
                <details class="tb-reveal">
                    <summary>Do I need to install anything?</summary>
                    <p>No. TrackBridge is a web app. Open it in a browser, sign in, and you're working.</p>
                </details>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="tb-cta">
        <div class="container">
            <div class="tb-cta-inner">
                <h2>Ready to streamline your workshop?</h2>
                <p>Join teams that have replaced scattered chats and lost files with one visible pipeline.</p>
                <div class="tb-cta-buttons">
                    <?php if ($loggedIn): ?>
                        <a class="btn btn-light" href="<?= $this->Url->build(['controller' => 'Pages', 'action' => 'dashboard']) ?>">
                            <i class="fas fa-gauge-high me-1"></i> Open dashboard
                        </a>
                    <?php else: ?>
                        <a class="btn btn-light" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'register']) ?>">
                            Create account <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                        <a class="btn btn-outline-light" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>">
                            Sign in
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <footer class="tb-footer">
        <div class="container tb-footer-inner">
            <div class="tb-footer-brand">
                <img src="<?= $this->Url->build('/img/brand-logo.svg') ?>" alt="TrackBridge" class="tb-footer-mark">
                <div>
                    <strong>TrackBridge</strong>
                    <span>Schedule &middot; Work &middot; QC</span>
                </div>
            </div>
            <div class="tb-footer-copy">
                &copy; <?= date('Y') ?> TrackBridge. All rights reserved.
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu
        const burger = document.getElementById('tbBurger');
        const nav = document.querySelector('.tb-nav');
        if (burger) {
            burger.addEventListener('click', () => {
                nav.classList.toggle('tb-nav-open');
            });
        }

        // FAQ: keep one open at a time
        document.querySelectorAll('.tb-faq details').forEach((d) => {
            d.addEventListener('toggle', () => {
                if (d.open) {
                    document.querySelectorAll('.tb-faq details').forEach((o) => {
                        if (o !== d) o.open = false;
                    });
                }
            });
        });

        // 3D parallax on mouse move
        const cube = document.getElementById('tbCube');
        const ico = document.getElementById('tbIco');
        const ring = document.getElementById('tbRing');
        const shapes = [cube, ico, ring].filter(Boolean);
        
        if (shapes.length) {
            document.addEventListener('mousemove', (e) => {
                const x = (e.clientX / window.innerWidth - 0.5) * 2;
                const y = (e.clientY / window.innerHeight - 0.5) * 2;
                if (cube) cube.style.transform = `rotateX(${-12 * y}deg) rotateY(${15 * x}deg)`;
                if (ico) ico.style.transform = `rotateY(${15 * x}deg) rotateX(${10 * y}deg)`;
                if (ring) ring.style.transform = `rotateY(${10 * x}deg) rotateX(${-8 * y}deg)`;
            });
            document.addEventListener('mouseleave', () => {
                shapes.forEach(el => { el.style.transform = ''; });
            });
        }

        // Floating particles — reduced count for performance
        const particlesContainer = document.getElementById('tbParticles');
        if (particlesContainer) {
            const particleCount = 15;
            const sizes = ['sm', 'md', 'lg'];
            for (let i = 0; i < particleCount; i++) {
                const p = document.createElement('div');
                const size = sizes[Math.floor(Math.random() * sizes.length)];
                p.className = `tb-particle ${size}`;
                p.style.left = `${Math.random() * 100}%`;
                p.style.top = `${Math.random() * 100}%`;
                p.style.animationDelay = `${Math.random() * 18}s`;
                p.style.animationDuration = `${15 + Math.random() * 15}s`;
                particlesContainer.appendChild(p);
            }
        }

        // Scroll reveal
        const revealElements = document.querySelectorAll('.tb-reveal');
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        revealElements.forEach((el) => revealObserver.observe(el));
    </script>
</body>
</html>
