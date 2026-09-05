<?php
/**
 * @var \App\Model\Entity\User $user
 * @var string $resetUrl
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= __('Password Reset') ?></title>
</head>
<body>
    <p><?= __('Hello {0},', h($user->name)) ?></p>
    <p><?= __('You have requested to reset your password. Click the button below to choose a new password:') ?></p>
    <p style="text-align: center; margin: 30px 0;">
        <a href="<?= h($resetUrl) ?>" style="background-color: #0d6efd; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold;">
            <?= __('Reset Password') ?>
        </a>
    </p>
    <p><?= __('Or copy and paste this link into your browser:') ?></p>
    <p><code><?= h($resetUrl) ?></code></p>
    <p><?= __('This link will expire in 1 hour.') ?></p>
    <p><?= __('If you did not request this, please ignore this email.') ?></p>
</body>
</html>
