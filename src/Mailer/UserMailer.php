<?php
declare(strict_types=1);

namespace App\Mailer;

use Cake\Mailer\Mailer;
use Cake\Routing\Router;

class UserMailer extends Mailer
{
    public function forgotPassword(\App\Model\Entity\User $user, string $token): void
    {
        $resetUrl = Router::url(['controller' => 'Users', 'action' => 'resetWithToken', $token], true);
        
        $this
            ->setTo($user->email)
            ->setSubject('Password Reset Request')
            ->setEmailFormat('both');
            
        $this->viewBuilder()
            ->setTemplate('forgot_password')
            ->setLayout('default');
            
        $this->setViewVars([
            'user' => $user,
            'resetUrl' => $resetUrl,
        ]);
    }
}
