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
 * @since     3.3.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App;

use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;

use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Middleware\AuthenticationMiddleware;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Application setup class.
 *
 * This defines the bootstrapping logic and middleware layers you
 * want to use in your application.
 *
 * @extends \Cake\Http\BaseApplication<\App\Application>
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        // Call parent to load bootstrap from files.
        parent::bootstrap();

        if (PHP_SAPI === 'cli') {
            $this->bootstrapCli();
        } else {
            FactoryLocator::add(
                'Table',
                (new TableLocator())->allowFallbackClass(false)
            );
        }

        /*
         * Only try to load DebugKit in development mode
         * Debug Kit should not be installed on a production system
         */
        if (Configure::read('debug')) {
            $this->addPlugin('DebugKit');
        }

        // Load more plugins here
        $this->addPlugin('Authentication');
        $this->addPlugin('Migrations');
    }

    /**
     * Setup the middleware queue your application will use.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
     * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
     */
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // Catch any exceptions in the lower layers,
            // and make an error page/response
            ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

            // Handle plugin/theme assets like CakePHP normally does.
            ->add(new AssetMiddleware([
                'cacheTime' => Configure::read('Asset.cacheTime'),
            ]))

            
            // Add routing middleware.
            // If you have a large number of routes connected, turning on routes
            // caching in production could improve performance.
            // See https://github.com/CakeDC/cakephp-cached-routing
            ->add(new RoutingMiddleware($this))
            // Authentication must run on the login route too: the Form
            // authenticator processes the submitted credentials there.
            // The wrapper around AuthenticationMiddleware also whitelists a
            // handful of public actions (Users::login/register/... and
            // SystemTests::panel/run) so the Automatic-testing button works
            // even when the user is not signed in.
            ->add(new ProtectedAuthenticationMiddleware($this))
            // Parse various types of encoded request bodies so that they are
            // available as array through $request->getData()
            // https://book.cakephp.org/4/en/controllers/middleware.html#body-parser-middleware
            ->add(new BodyParserMiddleware())

// Cross Site Request Forgery (CSRF) Protection Middleware
            // https://book.cakephp.org/4/en/security/csrf.html#cross-site-request-forgery-middleware
            ->add(new CsrfProtectionMiddleware([
                'httponly' => true,
            ]))
            ;

        return $middlewareQueue;
    }

    /**
     * Register application container services.
     *
     * @param \Cake\Core\ContainerInterface $container The Container to update.
     * @return void
     * @link https://book.cakephp.org/4/en/development/dependency-injection.html#dependency-injection
     */
    public function services(ContainerInterface $container): void
    {
    }

    /**
     * Bootstrapping for CLI application.
     *
     * That is when running commands.
     *
     * @return void
     */
    protected function bootstrapCli(): void
    {
        $this->addOptionalPlugin('Bake');

        // $this->addPlugin('Migrations');

        // Load more plugins here
    }
    public function getAuthenticationService(ServerRequestInterface $request): AuthenticationServiceInterface
    {
        $service = new AuthenticationService([
            'unauthenticatedRedirect' => $request->getAttribute('webroot') . 'users/login',
            'queryParam' => 'redirect',
        ]);

        // Load the ORM table for user lookups
        $ormResolver = [
            'className' => 'Authentication.Orm',
            'userModel' => 'Users',
            'finder' => 'all',
            'collection' => 'Users',
        ];

        // Load identifiers - Password identifier will use ORM to find users
        $service->loadIdentifier('Authentication.Password', [
            'resolver' => $ormResolver,
            'fields' => [
                'username' => 'email',
                'password' => 'password',
            ],
        ]);

        // Load authenticators
        $service->loadAuthenticator('Authentication.Session');
        $service->loadAuthenticator('Authentication.Form', [
            'fields' => [
                'username' => 'email',
                'password' => 'password',
            ],
        ]);

        return $service;
    }
}

/**
 * Protected Authentication Middleware
 * Allows certain actions to be accessed without authentication
 */
class ProtectedAuthenticationMiddleware implements MiddlewareInterface
{
    private AuthenticationMiddleware $authMiddleware;
    private AuthenticationServiceProviderInterface $provider;

    /**
     * Public actions, keyed by lowercased controller name (dot prefixed when the
     * controller belongs to a plugin) with lowercased action names.
     */
    private array $unauthenticatedActions = [
        'users' => [
            'login',
            'logout',
            'register',
            'resetadminpassword',
            'forgotpassword',
            'resetwithtoken',
            'awaitingapproval',
        ],
        // '/' (Pages::dashboard) and the TrackBridge landing page are public;
        // dashboard() redirects to welcome() when nobody is signed in.
        'pages' => ['admin_demo', 'dashboard', 'welcome', 'display'],
    ];

    public function __construct(AuthenticationServiceProviderInterface $provider)
    {
        $this->provider = $provider;
        $this->authMiddleware = new AuthenticationMiddleware($provider);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // RoutingMiddleware only sets the 'params' attribute; controller/action
        // request attributes are not populated yet at this point in the queue.
        $params = (array)$request->getAttribute('params');
        $plugin = (string)($params['plugin'] ?? '');
        $controller = strtolower((string)($params['controller'] ?? $request->getAttribute('controller') ?? ''));
        $action = strtolower((string)($params['action'] ?? $request->getAttribute('action') ?? ''));
        if ($plugin !== '') {
            $controller = strtolower($plugin) . '.' . $controller;
        }

        // Check if this action should be accessible without authentication
        $isPublic = $this->isToolingRequest($request)
            || (isset($this->unauthenticatedActions[$controller]) &&
                in_array($action, $this->unauthenticatedActions[$controller], true));

        // Authentication plugin 3.x always sets the identity attribute (null when
        // nobody is signed in) and leaves enforcement to the application. Without
        // this guard an expired/absent session reaches every policy with a null
        // user and surfaces as a raw ForbiddenException instead of a login prompt.
        // AuthenticationMiddleware itself must still run for public actions: the
        // Form authenticator handles the login POST and controllers read the
        // 'authentication' request attribute.
        $guard = new class ($handler, $this->provider->getAuthenticationService($request), $isPublic) implements RequestHandlerInterface {
            public function __construct(
                private RequestHandlerInterface $handler,
                private AuthenticationServiceInterface $service,
                private bool $isPublic,
            ) {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                if (
                    !$this->isPublic &&
                    $request->getAttribute($this->service->getIdentityAttribute()) === null
                ) {
                    $url = $this->service->getUnauthenticatedRedirectUrl($request);
                    if ($url !== null) {
                        return new RedirectResponse($url);
                    }
                }

                return $this->handler->handle($request);
            }
        };

        return $this->authMiddleware->process($request, $guard);
    }

    /**
     * Requests made by development tooling (the DebugKit toolbar) are not user
     * navigation and must not be bounced to the login screen, otherwise the
     * toolbar requests a login page, which then builds another toolbar panel
     * and burns memory in a loop.
     */
    private function isToolingRequest(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        $webroot = rtrim(WWW_ROOT, '\\/');
        $base = basename($webroot);
        if ($base !== '' && str_starts_with($path, '/' . $base)) {
            $path = substr($path, strlen($base) + 1);
        }

        return str_starts_with($path, '/debug-kit/');
    }
}
