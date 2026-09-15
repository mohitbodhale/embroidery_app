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
            // ->add(new CsrfProtectionMiddleware([
            //     'httponly' => true,
            // ]))
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
    private array $unauthenticatedActions = [
        'Users' => ['login', 'logout', 'register', 'resetAdminPassword', 'awaitingApproval'],
        'SystemTests' => ['panel', 'run'],
        'Pages' => ['admin_demo'],
    ];

    public function __construct(AuthenticationServiceProviderInterface $provider)
    {
        $this->authMiddleware = new AuthenticationMiddleware($provider);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get the controller and action from the request
        $controller = $request->getAttribute('controller');
        $action = $request->getAttribute('action');

        // Check if this action should be accessible without authentication
        if (isset($this->unauthenticatedActions[$controller]) && 
            in_array($action, $this->unauthenticatedActions[$controller])) {
            // Skip authentication for this action
            return $handler->handle($request);
        }

        // Otherwise, apply authentication middleware
        return $this->authMiddleware->process($request, $handler);
    }
}
