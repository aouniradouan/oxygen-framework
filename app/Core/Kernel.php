<?php

namespace Oxygen\Core;

use Oxygen\Core\Middleware\MiddlewareStack;

/**
 * Kernel - Application HTTP Kernel
 * 
 * The Kernel is responsible for handling incoming HTTP requests and
 * passing them through the global middleware stack before handing
 * them off to the router.
 * 
 * @package    Oxygen\Core
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
class Kernel
{
    /**
     * The application instance
     * 
     * @var Application
     */
    protected $app;

    /**
     * The middleware stack instance
     * 
     * @var MiddlewareStack
     */
    protected $middlewareStack;

    /**
     * Global Middleware
     * 
     * These middleware run on every request to the application.
     * 
     * @var array
     */
    protected $middleware = [
        \Oxygen\Http\Middleware\OxygenCsrfMiddleware::class,
        \Oxygen\Http\Middleware\OxygenLocaleMiddleware::class,
    ];

    /**
     * Route Middleware
     * 
     * These middleware can be assigned to specific routes.
     * 
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Oxygen\Http\Middleware\OxygenAuthMiddleware::class,
        'guest' => \Oxygen\Http\Middleware\OxygenGuestMiddleware::class,
        'csrf' => \Oxygen\Http\Middleware\OxygenCsrfMiddleware::class,
        'cors' => \Oxygen\Http\Middleware\OxygenCorsMiddleware::class,
        'api' => \Oxygen\Http\Middleware\OxygenApiMiddleware::class,
    ];

    /**
     * Create a new Kernel instance
     * 
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->middlewareStack = new MiddlewareStack();

        // Register global middleware
        foreach ($this->middleware as $middleware) {
            $this->middlewareStack->add(new $middleware());
        }
    }

    /**
     * Handle an incoming HTTP request
     * 
     * @param Request $request
     * @return void
     */
    public function handle(Request $request)
    {
        // Execute the global middleware stack
        // The destination closure runs the application router
        $response = $this->middlewareStack->handle($request, function ($request) {
            $this->app->run();
        });

        return $response;
    }

    /**
     * Get the route middleware array
     * 
     * @return array
     */
    public function getRouteMiddleware()
    {
        return $this->routeMiddleware;
    }
}
