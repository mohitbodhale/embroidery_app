<?php
/**
 * AdminLTE Layout for CakePHP
 * @var \App\View\AppView $this
 *
 * Detects "auth" pages (login / register / reset / awaiting approval / debug)
 * and renders a slim, centered layout without navbar, sidebar, or footer chrome.
 */
$req = $this->request ?? null;
$controller = $req ? (string)$req->getParam('controller') : '';
$action = $req ? (string)$req->getParam('action') : '';
$isAuthPage = ($controller === 'Users' && in_array($action, ['login','register','resetAdminPassword','awaitingApproval'], true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?= $this->fetch('title') ?: 'Embroidery App' ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">

    <!-- Local Font Awesome -->
    <link rel="stylesheet" href="<?= $this->Url->build('/vendor/fontawesome/all.min.css') ?>">

    <!-- Local Bootstrap 5 CSS -->
    <link rel="stylesheet" href="<?= $this->Url->build('/vendor/bootstrap/bootstrap.min.css') ?>">

    <!-- Local AdminLTE 3 CSS -->
    <link rel="stylesheet" href="<?= $this->Url->build('/vendor/adminlte/adminlte.min.css') ?>">

    <!-- Custom CSS -->
    <?= $this->Html->css(['cake', 'custom']) ?>
    <?= $this->fetch('css') ?>

    <style>
        /* Minimal layout primitives — typography & polish live in webroot/css/custom.css */
        html, body {
            height: 100%;
            background-color: #f4f6f9;
        }

        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-body {
            display: flex;
            flex: 1 1 auto;
            min-height: 0;
        }

        .main-sidebar {
            flex-shrink: 0;
        }

        .content-wrapper {
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
            min-height: 0;
            overflow-y: auto;
        }

        .nav-sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            border-left-color: #fff;
            font-weight: 600;
        }
        .nav-sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05);
        }

        /* Slim body for auth pages — removes body padding, hides scrollbar */
        body.auth-page {
            overflow: hidden;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed<?= $isAuthPage ? ' auth-page' : '' ?>">
<?php if ($isAuthPage): ?>
    <?= $this->fetch('content') ?>
<?php else: ?>
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= $this->Url->build(['controller' => 'Jobs', 'action' => 'index']) ?>" class="nav-link brand-text-nav">
                    <strong>TrackBridge</strong>
                    <span class="text-muted small ms-1">Job &middot; Workflow &middot; QC</span>
                </a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle user-menu" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle"></i>
                    <span class="user-name d-none d-md-inline"><?= h($currentUser->name ?? 'Account') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><h6 class="dropdown-header">Account</h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'editProfile']) ?>">
                            <i class="fas fa-user me-2"></i>My Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'logout']) ?>">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main body wrapper -->
    <div class="main-body">
        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="<?= $this->Url->build(['controller' => 'Jobs', 'action' => 'index']) ?>" class="brand-link d-flex align-items-center">
                <img src="<?= $this->Url->build('/img/brand-logo.svg') ?>" alt="TrackBridge" class="brand-image brand-image-logo" />
                <span class="brand-text fw-light">
                    TrackBridge
                    <small class="d-block brand-slogan">Job &middot; Workflow &middot; QC</small>
                </span>
            </a>

            <!-- Sidebar -->
<?php
            $rawUser = $currentUser ?? null;
            $userId = null;
            $userRole = '';
            if (is_array($rawUser)) {
                $userId = $rawUser['id'] ?? null;
                $userRole = $rawUser['role'] ?? '';
            } elseif (is_object($rawUser)) {
                $userId = $rawUser->id ?? null;
                $userRole = $rawUser->role ?? '';
            }
            $isLoggedIn = !empty($userId);
            $role = $isLoggedIn ? strtolower(trim((string)$userRole)) : null;
            $workspaceLabel = [
                'admin' => 'Operations Dashboard',
                'scheduler' => 'Scheduling Board',
                'operator' => 'Jobs',
                'quality_checker' => 'Jobs',
                'production' => 'Jobs',
            ][$role] ?? 'Jobs';
            ?>
            <div class="sidebar">
                <?php if ($isLoggedIn): ?>
                <?php if ($role === 'pending'): ?>
                <nav class="mt-4">
                    <ul class="nav nav-pills nav-sidebar flex-column">
                        <li class="nav-item">
                            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'awaitingApproval']) ?>" class="nav-link active">
                                <i class="nav-icon fas fa-clock"></i>
                                <p>Awaiting Approval</p>
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'logout']) ?>" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php else: ?>
                <!-- Sidebar Menu -->
                <nav class="mt-4">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <?php if ($role === 'admin'): ?>
                        <!-- Admin: management only, no job operations -->
                        <li class="nav-item">
                            <a href="<?= $this->Url->build(['controller' => 'Pages', 'action' => 'adminDashboard']) ?>" class="nav-link <?= ($this->request->getParam('controller') === 'Pages' && $this->request->getParam('action') === 'adminDashboard') ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-chart-line"></i>
                                <p>Operations Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-header">MASTERS</li>
                        <li class="nav-item">
                            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'index']) ?>" class="nav-link <?= ($this->request->getParam('controller') === 'Users') ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Users</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= $this->Url->build(['controller' => 'Roles', 'action' => 'index']) ?>" class="nav-link <?= ($this->request->getParam('controller') === 'Roles') ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-user-shield"></i>
                                <p>Roles</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= $this->Url->build(['controller' => 'WorkTypes', 'action' => 'index']) ?>" class="nav-link <?= ($this->request->getParam('controller') === 'WorkTypes') ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-tools"></i>
                                <p>Work Types</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= $this->Url->build(['controller' => 'JobStatuses', 'action' => 'index']) ?>" class="nav-link <?= ($this->request->getParam('controller') === 'JobStatuses') ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-tags"></i>
                                <p>Job Statuses</p>
                            </a>
                        </li>
                        <li class="nav-header">ACTIVITY</li>
                        <li class="nav-item">
                            <a href="<?= $this->Url->build(['controller' => 'JobLogs', 'action' => 'index']) ?>" class="nav-link <?= ($this->request->getParam('controller') === 'JobLogs') ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-history"></i>
                                <p>Activity Logs</p>
                            </a>
                        </li>
                        <?php else: ?>
                        <!-- Non-admin: job workspace -->
                        <li class="nav-item">
                            <a href="<?= $this->Url->build(['controller' => 'Jobs', 'action' => 'index']) ?>" class="nav-link <?= ($this->request->getParam('controller') === 'Jobs') ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-briefcase"></i>
                                <p><?= h($workspaceLabel) ?></p>
                            </a>
                        </li>

                        <?php if ($role === 'scheduler'): ?>
                        <li class="nav-item">
                            <a href="<?= $this->Url->build(['controller' => 'Jobs', 'action' => 'add']) ?>" class="nav-link">
                                <i class="nav-icon fas fa-plus-circle"></i>
                                <p>Create Job</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <li class="nav-item mt-4">
                            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'logout']) ?>" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
                <?php endif; ?>
                <?php endif; ?>
                <?php else: ?>
                <nav class="mt-4">
                    <ul class="nav nav-pills nav-sidebar flex-column">
                        <li class="nav-item">
                            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'login']) ?>" class="nav-link">
                                <i class="nav-icon fas fa-sign-in-alt"></i>
                                <p>Login</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= $this->Url->build(['controller' => 'Users', 'action' => 'register']) ?>" class="nav-link">
                                <i class="nav-icon fas fa-user-plus"></i>
                                <p>Register</p>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="d-flex align-items-center justify-content-end">
                        <?php
                            $homeTarget = ($role === 'admin')
                                ? ['controller' => 'Pages', 'action' => 'adminDashboard']
                                : ['controller' => 'Jobs', 'action' => 'index'];
                            $pageIcon = $this->fetch('page_icon') ?: 'fas fa-home';
                        ?>
                        <ol class="breadcrumb float-sm-end mb-0">
                            <li class="breadcrumb-item"><a href="<?= $this->Url->build($homeTarget) ?>"><i class="fas fa-home"></i></a></li>
                            <li class="breadcrumb-item active">
                                <i class="<?= h($pageIcon) ?> me-1"></i><?= h($this->fetch('title') ?: 'Dashboard') ?>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content flex-grow-1">
                <div class="container-fluid">
                    <!-- Flash Messages -->
                    <?= $this->Flash->render() ?>

                    <!-- Page content -->
                    <?= $this->fetch('content') ?>
                </div>
            </section>
            <!-- /.content -->

        </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container-fluid">
            <div class="footer-row">
                <div class="footer-brand">
                    <img src="<?= $this->Url->build('/img/brand-logo.svg') ?>" alt="TrackBridge" class="footer-brand-logo" />
                    <span><strong>TrackBridge</strong> &middot; <span class="text-muted">From job upload to QC sign-off — one workflow.</span></span>
                </div>
                <div class="footer-actions">
                    <span class="version-badge d-none d-sm-inline-flex">
                        <i class="fas fa-code-branch"></i>
                        <span>v1.0.0</span>
                    </span>
                    <button type="button" class="btn btn-test" id="openAutoTestBtn" data-bs-toggle="modal" data-bs-target="#autoTestModal">
                        <i class="fas fa-vial"></i>
                        <span class="d-none d-sm-inline ms-1">Automatic testing</span>
                        <span class="d-inline d-sm-none ms-1">Tests</span>
                    </button>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal markup is fetched lazily so non-JS users don't get a giant footer -->
    <div id="autoTestMount"></div>

</div>
<!-- ./wrapper -->
<?php endif; ?>

<!-- Local jQuery -->
<script src="<?= $this->Url->build('/vendor/jquery/jquery.min.js') ?>"></script>

<!-- Local Bootstrap 5 JS Bundle (includes Popper) -->
<script src="<?= $this->Url->build('/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>

<!-- Local AdminLTE JS -->
<script src="<?= $this->Url->build('/vendor/adminlte/adminlte.min.js') ?>"></script>

<!-- Custom JS for sidebar toggle -->
<script src="<?= $this->Url->build('/js/embroidery.js') ?>"></script>

<?= $this->fetch('script') ?>
</body>
</html>

