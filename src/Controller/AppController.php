<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\Http\Exception\ForbiddenException;


/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/4/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Default layout for this application
     *
     * @var string
     */
    protected $layout = 'adminlte';

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Use the AdminLTE layout for all views by default
        $this->viewBuilder()->setLayout('adminlte');

        $this->loadComponent('Flash');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/4/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        // Make current user available to all views
        $currentUser = $this->getCurrentUser();
        $this->set('currentUser', $currentUser);
        $this->set('currentRole', $currentUser ? $this->normalizedRole($currentUser) : null);
    }

    /**
     * Return the currently authenticated user as an object or null.
     * Works with Authentication plugin identity or legacy session 'Auth' keys.
     */
    protected function getCurrentUser(): ?object
    {
        \Cake\Log\Log::write('debug', 'getCurrentUser called');
        $identity = $this->request->getAttribute('identity');
        \Cake\Log\Log::write('debug', 'Identity attribute: ' . ($identity ? get_class($identity) : 'null'));
        if ($identity) {
            if (is_object($identity) && method_exists($identity, 'getOriginalData')) {
                \Cake\Log\Log::write('debug', 'Identity has getOriginalData');
                $data = $identity->getOriginalData();
                \Cake\Log\Log::write('debug', 'Original data: ' . print_r($data, true));
                return $this->refreshCurrentUser(is_object($data) ? $data : (object)$data);
            }
            if (is_object($identity) && method_exists($identity, 'get')) {
                \Cake\Log\Log::write('debug', 'Identity has get method');
                return $this->refreshCurrentUser((object)[
                    'id' => method_exists($identity, 'getIdentifier') ? $identity->getIdentifier() : $identity->get('id'),
                    'name' => $identity->get('name'),
                    'role' => $identity->get('role'),
                    'organization_id' => $identity->get('organization_id'),
                ]);
            }
            \Cake\Log\Log::write('debug', 'Identity is object, using directly');
            return $this->refreshCurrentUser(is_object($identity) ? $identity : (object)$identity);
        }

        // Fallback to session-based auth written by the app or SessionAuthenticator.
        // The SessionAuthenticator stores the entire user entity under 'Auth', while
        // legacy/manual writes used 'Auth.user_id' / 'Auth.role' / 'Auth.organization_id'.
        // Handle both shapes.
        $sessionAuth = $this->request->getSession()->read('Auth');
        \Cake\Log\Log::write('debug', 'Session Auth: ' . print_r($sessionAuth, true));
        if (!empty($sessionAuth)) {
            if (is_array($sessionAuth) && (isset($sessionAuth['user_id']) || isset($sessionAuth['role']) || isset($sessionAuth['id']))) {
                \Cake\Log\Log::write('debug', 'Session Auth is array with user_id/role/id');
                return $this->refreshCurrentUser((object)[
                    'id' => $sessionAuth['id'] ?? $sessionAuth['user_id'] ?? null,
                    'role' => $sessionAuth['role'] ?? null,
                    'organization_id' => $sessionAuth['organization_id'] ?? null,
                ]);
            }
            if (is_object($sessionAuth)) {
                \Cake\Log\Log::write('debug', 'Session Auth is object: ' . get_class($sessionAuth));
                $id = $sessionAuth->id ?? null;
                if ($id === null) {
                    // Try to refresh from session-stored id via getIdentifier()
                    if (method_exists($sessionAuth, 'getIdentifier')) {
                        $id = $sessionAuth->getIdentifier();
                    } elseif (isset($sessionAuth->user_id)) {
                        $id = $sessionAuth->user_id;
                    }
                }
                return $this->refreshCurrentUser((object)[
                    'id' => $id,
                    'role' => $sessionAuth->role ?? null,
                    'organization_id' => $sessionAuth->organization_id ?? null,
                ]);
            }
        }

        \Cake\Log\Log::write('debug', 'No identity or session auth found');
        return null;
    }

    /**
     * Require that the current user has one of the provided roles.
     * If not, either throw ForbiddenException or redirect to login depending on auth state.
     *
     * Usage: $this->requireRole(['admin','scheduler']);
     */
    protected function requireRole(array $roles)
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            // Not authenticated - redirect to login
            $this->Flash->error(__('Please login to access that page.'));
            return $this->redirect(['controller' => 'Users', 'action' => 'login']);
        }

        $userRole = $this->normalizedRole($user);
        $roles = array_map(static fn(string $role): string => strtolower(trim($role)), $roles);
        if (!in_array($userRole, $roles, true)) {
            // Authenticated but not authorized
            throw new ForbiddenException(__('You are not authorized to access that page.'));
        }

        return null;
    }

    /** Return the canonical role string regardless of how legacy data was entered. */
    protected function normalizedRole(object $user): string
    {
        return strtolower(trim((string)($user->role ?? '')));
    }

    /**
     * Session identities can be stale after an Admin changes a user's role.
     * Reload the record so access and sidebar options reflect the current role.
     */
    private function refreshCurrentUser(object $user): object
    {
        if (empty($user->id)) {
            return $user;
        }

        try {
            return $this->fetchTable('Users')->get($user->id);
        } catch (\Throwable) {
            return $user;
        }
    }

    /**
     * Simple policy authorization helper.
     * $resource can be a string resource name (e.g., 'Job') or an entity object.
     * $action is the action name (e.g., 'edit','delete','assign','approve').
     * Returns true if allowed, false otherwise.
     */
    protected function authorizeAction($resource, string $action): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }

        // Determine policy class based on resource
        $policyClass = null;
        if (is_string($resource)) {
            $resourceName = $resource;
        } elseif (is_object($resource)) {
            $resourceName = (new \ReflectionClass($resource))->getShortName();
        } else {
            return false;
        }

        $policyClass = '\\App\\Policy\\' . $resourceName . 'Policy';

        if (!class_exists($policyClass)) {
            // no policy found -> default deny
            return false;
        }

        $policy = new $policyClass();
        $method = 'can' . ucfirst($action);
        if (!method_exists($policy, $method)) {
            // method not defined
            return false;
        }

        return (bool)$policy->{$method}($user, $resource);
    }

    public function beforeRender(\Cake\Event\EventInterface $event)
    {
        parent::beforeRender($event);

        $currentUser = $this->getCurrentUser();

        $userRole = '';
        if ($currentUser) {
            if (is_array($currentUser)) {
                $userRole = $currentUser['role'] ?? '';
            } else {
                $userRole = $currentUser->role ?? '';
            }
        }

        $userRole = strtolower(trim((string)$userRole));

        // Only expose currentUser + currentRole to views. We intentionally
        // do NOT set a 'role' view variable because it collides with domain
        // entities named $role (e.g. App\Model\Entity\Role in the Roles
        // controller templates). Use $currentRole for the signed-in user's role.
        $this->set([
            'currentUser' => $currentUser,
            'currentRole' => $userRole,
        ]);
    }

    /**
     * Normalize uploaded files from either single or multiple upload input.
     * Returns array of file arrays or empty array.
     */
    protected function normalizeFiles(?array $files): array
    {
        if (empty($files) || !isset($files['name'])) {
            return [];
        }
        if (!is_array($files['name'])) {
            return [$files];
        }
        $out = [];
        foreach ($files['name'] as $i => $name) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $out[] = [
                'name' => $name,
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$i] ?? 0,
            ];
        }
        return $out;
    }

    /**
     * Save a single uploaded file and return metadata, or false on failure.
     */
    protected function saveUploadedFile(array $file, $jobId): array|false
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }
        $maxBytes = 20 * 1024 * 1024;
        if ((int)$file['size'] > $maxBytes) {
            return false;
        }
        $jobId = $jobId ?: 0;
        $uploadDir = WWW_ROOT . 'uploads' . DS . 'attachments' . DS . $jobId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $basename = bin2hex(random_bytes(8)) . ($ext ? '.' . strtolower($ext) : '');
        $dest = $uploadDir . DS . $basename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return false;
        }
        clearstatcache(true, $dest);
        $actualSize = filesize($dest);
        if (!$actualSize) {
            $actualSize = (int)$file['size'];
        }
        $mime = function_exists('mime_content_type') ? mime_content_type($dest) : ($file['type'] ?? 'application/octet-stream');
        $rel = 'uploads/attachments/' . $jobId . '/' . $basename;
        return [
            'name' => $file['name'],
            'path' => '/' . $rel,
            'type' => strtolower($ext),
            'size' => (int)$actualSize,
            'mime' => $mime ?: 'application/octet-stream',
        ];
    }
}
