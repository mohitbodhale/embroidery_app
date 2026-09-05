<?php
/**
 * @var \App\Model\Entity\User $user
 * @var string $resetUrl
 */

echo __('Hello {0},', $user->name) . "\n\n";
echo __('You have requested to reset your password. Click the link below to choose a new password:') . "\n\n";
echo $resetUrl . "\n\n";
echo __('This link will expire in 1 hour.') . "\n\n";
echo __('If you did not request this, please ignore this email.') . "\n";
