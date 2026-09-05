<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $temporaryPassword
 */
$this->assign('title', 'Password Reset');
?>
<div class="page-card card">
    <div class="card-body text-center py-5">
        <div class="mb-4">
            <i class="fas fa-key fa-3x text-primary"></i>
        </div>
        <h3 class="mb-3">Temporary Password Generated</h3>
        <p class="text-muted mb-4">
            A temporary password has been generated for <strong><?= h($user->name) ?></strong>.
            Please provide this password to the user. They will be required to change it on first login.
        </p>
        
        <div class="alert alert-info mb-4">
            <div class="mb-2">
                <strong>Email:</strong> <?= h($user->email) ?>
            </div>
            <div class="mb-2">
                <strong>Temporary Password:</strong>
                <div class="input-group mt-2">
                    <input type="text" class="form-control form-control-lg text-center" 
                           value="<?= h($temporaryPassword) ?>" 
                           id="tempPassword" 
                           readonly
                           style="font-family: monospace; font-size: 1.25rem; letter-spacing: 0.1em;">
                    <button class="btn btn-outline-secondary" type="button" onclick="copyPassword()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>
        </div>
        
        <div class="alert alert-warning mb-4">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Important:</strong> This password will only be shown once. Make sure to copy it before closing this page.
        </div>
        
        <div class="d-flex justify-content-center gap-2">
            <button class="btn btn-primary" onclick="copyPassword()">
                <i class="fas fa-copy me-1"></i>Copy Password
            </button>
            <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Users
            </a>
        </div>
    </div>
</div>

<script>
function copyPassword() {
    const passwordField = document.getElementById('tempPassword');
    passwordField.select();
    passwordField.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(passwordField.value).then(function() {
        alert('Password copied to clipboard!');
    });
}
</script>
