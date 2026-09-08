<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Event\EventInterface;
use Authentication\PasswordHasher\DefaultPasswordHasher;
/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        if ($response = $this->requireAdmin()) {
            return $response;
        }
        $query = $this->Users->find()
            ->contain(['Organizations', 'UserDetails']);
        $users = $this->paginate($query);

        $this->set(compact('users'));
    }

    public function export()
    {
        if ($response = $this->requireAdmin()) {
            return $response;
        }
        $this->request->allowMethod(['get']);
        $users = $this->Users->find()
            ->contain(['Organizations'])
            ->orderBy(['Users.id' => 'ASC'])
            ->all();

        $filename = 'users_export_' . date('Y-m-d') . '.csv';
        
        $this->response = $this->response
            ->withType('csv')
            ->withDownload($filename)
            ->withHeader('Content-Type', 'text/csv; charset=utf-8')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        $this->viewBuilder()->disableAutoLayout();
        
        $csv = fopen('php://output', 'w');
        fputcsv($csv, ['ID', 'Name', 'Email', 'Role', 'Organization', 'Created']);
        foreach ($users as $user) {
            fputcsv($csv, [
                $user->id,
                $user->name,
                $user->email,
                $user->role,
                $user->hasValue('organization') ? $user->organization->name : '',
                $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : ''
            ]);
        }
        fclose($csv);
        
        return $this->response;
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        if ($response = $this->requireAdmin()) {
            return $response;
        }
        $user = $this->Users->get($id, contain: ['Organizations', 'JobLogs', 'UserDetails.WorkTypes']);
        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        if ($response = $this->requireAdmin()) {
            return $response;
        }
        $user = $this->Users->newEmptyEntity();
        $organizations = $this->Users->Organizations->find('list', limit: 200)->all();
        $roles = $this->fetchTable('Roles')->find()
            ->orderBy(['sort_order' => 'ASC', 'label' => 'ASC'])
            ->all()
            ->combine('name', 'label')
            ->toArray();

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            if (($data['password'] ?? '') !== ($data['password_confirm'] ?? '')) {
                $this->Flash->error(__('Password confirmation does not match.'));
                $this->set(compact('user', 'organizations', 'roles'));
                return;
            }
            $currentUser = $this->getCurrentUser();
            // New staff belong to the administrator's organization.
            $data['organization_id'] = $currentUser->organization_id;
            unset($data['password_confirm']);
            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user', 'organizations', 'roles'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        if ($response = $this->requireAdmin()) {
            return $response;
        }
        $user = $this->Users->get($id, contain: []);
        $organizations = $this->Users->Organizations->find('list', limit: 200)->all();
        $roles = $this->fetchTable('Roles')->find()
            ->orderBy(['sort_order' => 'ASC', 'label' => 'ASC'])
            ->all()
            ->combine('name', 'label')
            ->toArray();
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Password confirmation validation
            if (!empty($data['password'])) {
                if (empty($data['password_confirm'])) {
                $this->Flash->error(__('Please confirm the new password.'));
                $this->set(compact('user', 'organizations', 'roles'));
                return;
            }
            if ($data['password'] !== $data['password_confirm']) {
                $this->Flash->error(__('Password and confirmation do not match.'));
                $this->set(compact('user', 'organizations', 'roles'));
                return;
            }
            if (strlen($data['password']) < 6) {
                $this->Flash->error(__('Password must be at least 6 characters.'));
                $this->set(compact('user', 'organizations', 'roles'));
                return;
            }
            }
            
            // Clear password fields if password is empty (keep existing password)
            if (empty($data['password'])) {
                unset($data['password']);
            }
            unset($data['password_confirm']);
            
            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user', 'organizations', 'roles'));
    }

    public function resetPassword($id = null)
    {
        if ($response = $this->requireAdmin()) {
            return $response;
        }
        $this->request->allowMethod(['post']);
        $user = $this->Users->get($id, contain: []);
        
        $temporaryPassword = bin2hex(random_bytes(8));
        $temporaryPassword .= 'A1!';
        
        $data = [
            'email' => $user->email,
            'password' => $temporaryPassword,
            'must_change_password' => true
        ];
        
        $user = $this->Users->patchEntity($user, $data);
        
        if ($this->Users->save($user)) {
            $this->set(compact('user', 'temporaryPassword'));
        } else {
            $this->Flash->error(__('Could not reset password. Please try again.'));
            return $this->redirect(['action' => 'index']);
        }
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        if ($response = $this->requireAdmin()) {
            return $response;
        }
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Edit own profile
     */
    public function editProfile()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return $this->redirect(['action' => 'login']);
        }

        $user = $this->Users->get($currentUser->id, contain: ['UserDetails']);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Handle user data
            $userData = [
                'name' => $data['name'] ?? $user->name,
                // Email is not allowed to change after account creation
                'email' => $user->email,
            ];
            
            // Handle password change if provided
            if (!empty($data['password'])) {
                if (empty($data['password_confirm'])) {
                    $this->Flash->error(__('Please confirm your new password.'));
                    $this->set(compact('user'));
                    return;
                }
                if ($data['password'] !== $data['password_confirm']) {
                    $this->Flash->error(__('Password and confirmation do not match.'));
                    $this->set(compact('user'));
                    return;
                }
                if (strlen($data['password']) < 6) {
                    $this->Flash->error(__('Password must be at least 6 characters.'));
                    $this->set(compact('user'));
                    return;
                }
                $userData['password'] = $data['password'];
            }
            
            $user = $this->Users->patchEntity($user, $userData);
            
            // Handle user details
            $detailData = [
                'phone' => $data['phone'] ?? null,
                'bio' => $data['bio'] ?? null,
                'website' => $data['website'] ?? null,
                'location' => $data['location'] ?? null,
                'work_type_id' => $data['work_type_id'] ?? null,
            ];
            
            if (!empty($data['avatar']) && $data['avatar'] instanceof \Laminas\Diactoros\UploadedFile) {
                $avatarPath = $this->uploadAvatar($data['avatar']);
                if ($avatarPath !== null) {
                    $detailData['avatar'] = $avatarPath;
                } else {
                    $this->Flash->error(__('Invalid avatar file. Please upload a JPG, PNG, GIF, or WebP image.'));
                }
            }
            
            if ($user->hasErrors()) {
                $this->Flash->error(__('Please fix the errors below.'));
            } else {
                if ($this->Users->save($user)) {
                    // Save user details
                    $existingDetails = $this->Users->UserDetails->find()
                        ->where(['user_id' => $user->id])
                        ->first();
                    
                    if ($existingDetails) {
                        $userDetails = $this->Users->UserDetails->patchEntity($existingDetails, $detailData);
                    } else {
                        $detailData['user_id'] = $user->id;
                        $userDetails = $this->Users->UserDetails->newEntity($detailData);
                    }
                    $this->Users->UserDetails->save($userDetails);
                    
                    $this->Flash->success(__('Profile updated successfully.'));
                    return $this->redirect(['action' => 'editProfile']);
                }
                $this->Flash->error(__('Could not update profile. Please try again.'));
            }
        }
        
        $this->set(compact('user'));
    }
    
    /**
     * Upload avatar image
     */
    private function uploadAvatar(\Laminas\Diactoros\UploadedFile|array $file): ?string
    {
        if ($file instanceof \Laminas\Diactoros\UploadedFile) {
            if ($file->getError() !== UPLOAD_ERR_OK) {
                return null;
            }
            $originalName = $file->getClientFilename() ?? '';
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($extension, $allowed, true)) {
                return null;
            }
            $filename = 'avatar_' . time() . '_' . uniqid() . '.' . $extension;
            $uploadDir = WWW_ROOT . 'uploads' . DS . 'avatars';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $destination = $uploadDir . DS . $filename;
            $stream = $file->getStream();
            if ($stream && $stream->getSize() > 0) {
                file_put_contents($destination, $stream->getContents());
                return 'uploads/avatars/' . $filename;
            }
            return null;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowed, true)) {
            return null;
        }
        
        $uploadDir = WWW_ROOT . 'uploads' . DS . 'avatars';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filename = 'avatar_' . time() . '_' . uniqid() . '.' . $extension;
        $destination = $uploadDir . DS . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return 'uploads/avatars/' . $filename;
        }
        
        return null;
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        // Whitelist awaitingApproval so unapproved users can access it without infinite redirect loops
        $this->request->getSession()->write('App.unauthenticatedActions', [
            'login', 'logout', 'register', 'awaitingApproval',
            'forgotPassword', 'resetWithToken'
        ]);
    }
    
    public function login()
    {
        $this->viewBuilder()->disableAutoLayout();
        $this->request->allowMethod(['get', 'post']);
        $authentication = $this->request->getAttribute('authentication');
        $result = $authentication->getResult();

        if ($this->request->is('post')) {
            if ($result && $result->isValid()) {
                $user = $this->getCurrentUser();

                $userRole = strtolower(trim((string)($user->role ?? '')));

                // Admins always bypass the pending page and go straight to User Management
                if ($userRole === 'admin') {
                    return $this->redirect(['action' => 'index']);
                }

                // Non-admin pending users are routed to awaitingApproval
                if ($userRole === 'pending') {
                    return $this->redirect(['action' => 'awaitingApproval']);
                }

                // Redirect approved non-admin users to Jobs index
                $target = $authentication->getLoginRedirect($this->request);
                if (!$target
                    || (is_string($target) && str_contains(strtolower($target), 'awaiting-approval'))
                    || (is_array($target)
                        && ($target['controller'] ?? null) === 'Users'
                        && ($target['action'] ?? null) === 'awaitingApproval')) {
                    $target = ['controller' => 'Jobs', 'action' => 'index'];
                }
                return $this->redirect($target);
            }
            
            $this->Flash->error(__('Invalid email or password.'));
        }
    }

    /**
     * Page shown to registered users until an administrator grants a role.
     */
    public function awaitingApproval()
    {
        $this->viewBuilder()->disableAutoLayout();
        $user = $this->getCurrentUser();
        if (!$user) {
            return $this->redirect(['action' => 'login']);
        }

        $role = strtolower(trim((string)($user->role ?? '')));

        // Redirect admins and non-pending users away from this page
        if ($role !== 'pending') {
            $target = ($role === 'admin') 
                ? ['controller' => 'Users', 'action' => 'index'] 
                : ['controller' => 'Jobs', 'action' => 'index'];
            return $this->redirect($target);
        }
    }

    /**
     * Public signup creates a pending account. Only an Admin assigns a role.
     */
    public function register()
    {
        $this->viewBuilder()->disableAutoLayout();
        $user = $this->Users->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            if (($data['password'] ?? '') !== ($data['password_confirm'] ?? '')) {
                $this->Flash->error(__('Password confirmation does not match.'));
            } else {
                $organization = $this->Users->Organizations->find()
                    ->where(['status' => 'active'])->orderByAsc('id')->first();
                if (!$organization) {
                    $this->Flash->error(__('Registration is unavailable until an organization is configured.'));
                } else {
                    $data['role'] = 'pending';
                    $data['organization_id'] = $organization->id;
                    unset($data['password_confirm']);
                    $user = $this->Users->patchEntity($user, $data);
                    if ($this->Users->save($user)) {
                        $this->Flash->success(__('Registration successful. Please sign in.'));
                        return $this->redirect(['action' => 'login']);
                    }
                    $this->Flash->error(__('Your account could not be created. Please review the form.'));
                }
            }
        }
        $this->set(compact('user'));
    }

    public function logout()
    {
        // Use the Authentication service to properly clear identity so the
        // AuthenticationMiddleware doesn't re-persist the user in its
        // post-handler hook after this action runs.
        $authentication = $this->request->getAttribute('authentication');
        if ($authentication !== null) {
            $authentication->clearIdentity(
                $this->request,
                $this->response
            );
        }

        // Destroy the session entirely to remove identity and any app state
        $session = $this->request->getSession();
        $session->destroy();

        // Set a flash on the new session and redirect to login
        $this->Flash->success(__('You have been logged out.'));
        return $this->redirect(['action' => 'login']);
    }

    /** Restrict staff management to signed-in administrators. */
    private function requireAdmin()
    {
        return $this->requireRole(['admin']);
    }

    /**
     * Admin action to approve user and assign role
     */
    public function assignRole($id = null)
    {
        if ($response = $this->requireAdmin()) {
            return $response;
        }

        $user = $this->Users->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('User role updated successfully.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Could not update user role. Please try again.'));
        }

        $roles = $this->fetchTable('Roles')->find()
            ->orderBy(['sort_order' => 'ASC', 'label' => 'ASC'])
            ->all()
            ->combine('name', 'label')
            ->toArray();

        $this->set(compact('user', 'roles'));
    }

    public function forgotPassword()
    {
        $this->viewBuilder()->disableAutoLayout();
        if ($this->request->is('post')) {
            $email = trim((string)$this->request->getData('email'));
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->Flash->error(__('Please enter a valid email address.'));
                $this->set(compact('email'));
                return;
            }
            
            $user = $this->Users->find()->where(['email' => $email])->first();
            
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiresAt = new \DateTime('+1 hour');
                
                $tokenEntity = $this->Users->PasswordResetTokens->newEntity([
                    'user_id' => $user->id,
                    'token' => hash('sha256', $token),
                    'expires_at' => $expiresAt,
                    'used' => false,
                ]);
                
                if ($this->Users->PasswordResetTokens->save($tokenEntity)) {
                    $mailer = new \App\Mailer\UserMailer();
                    $mailer->send('forgotPassword', [$user, $token]);
                    
                    \Cake\Log\Log::write('info', 'Password reset requested for: ' . $user->email);
                }
            }
            
            $this->Flash->success(__('If an account exists with that email, a password reset link has been sent.'));
            return $this->redirect(['action' => 'resetWithToken']);
        }
    }

    public function resetWithToken(string $token = null)
    {
        $this->viewBuilder()->disableAutoLayout();
        if (!$token) {
            return $this->redirect(['action' => 'forgotPassword']);
        }
        
        $hashedToken = hash('sha256', $token);
        $resetToken = $this->Users->PasswordResetTokens->find()
            ->where([
                'token' => $hashedToken,
                'used' => false,
                'expires_at >' => new \DateTime(),
            ])
            ->contain(['Users'])
            ->first();
        
        if (!$resetToken) {
            $this->Flash->error(__('Invalid or expired password reset link.'));
            return $this->redirect(['action' => 'login']);
        }
        
        $user = $resetToken->user;
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $newPassword = $this->request->getData('password');
            $confirmPassword = $this->request->getData('password_confirm');
            
            if ($newPassword !== $confirmPassword) {
                $this->Flash->error(__('Passwords do not match.'));
            } elseif (strlen($newPassword) < 6) {
                $this->Flash->error(__('Password must be at least 6 characters.'));
            } else {
                $user = $this->Users->patchEntity($user, [
                    'password' => $newPassword,
                    'email' => $user->email,
                    'organization_id' => $user->organization_id,
                ]);
                if ($this->Users->save($user)) {
                    $resetToken->used = true;
                    $this->Users->PasswordResetTokens->save($resetToken);
                    
                    $this->Flash->success(__('Password has been reset successfully. Please log in.'));
                    return $this->redirect(['action' => 'login']);
                }
                $this->Flash->error(__('Could not reset password. Please try again.'));
            }
        }
        
        $this->set(compact('token', 'user'));
    }
}
