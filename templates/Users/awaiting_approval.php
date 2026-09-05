<?php $this->assign('title', 'Account Pending'); ?>
<div class="auth-page auth-page-centered">
    <div class="auth-card-center">
        <div class="pending-icon"><i class="fas fa-hourglass-half"></i></div>
        <h1 class="auth-title">Account awaiting approval</h1>
        <p class="auth-sub">Your registration was successful. An administrator must assign your role before you can access the workflow.</p>
        <?= $this->Html->link('<i class="fas fa-sign-out-alt me-2"></i>Sign out', ['action' => 'logout'], ['class' => 'btn btn-outline-secondary', 'escape' => false]) ?>
    </div>
</div>