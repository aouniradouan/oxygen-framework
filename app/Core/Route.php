<?php

namespace Oxygen\Core;

use Bramus\Router\Router;
use Oxygen\Core\Application;
use Oxygen\Core\Request;

/**
 * Route Helper Class
 * 
 * Provides clean, modern routing syntax without requiring full namespaces.
 * Automatically resolves controller names to their full namespaced class.
 * 
 * @package    Oxygen\Core
 * @author     OxygenFramework
 * @copyright  2024 - OxygenFramework
 * 
 * Usage:
 *   Route::get($router, '/blog', 'BlogController@index');
 *   Route::post($router, '/admin/articles', 'Admin\ArticleController@store');
 */
class Route
{
    /**
     * Default controller namespace
     */
    const CONTROLLER_NAMESPACE = 'Oxygen\\Controllers\\';

    /**
     * Register a GET route
     * 
     * @param Router $router
     * @param string $path
     * @param string|callable $action Controller@method or closure
     * @return void
     */
    public static function get(Router $router, string $path, $action)
    {
        $router->get($path, self::resolveAction($action));
    }

    /**
     * Register a POST route
     * 
     * @param Router $router
     * @param string $path
     * @param string|callable $action Controller@method or closure
     * @return void
     */
    public static function post(Router $router, string $path, $action)
    {
        $router->post($path, self::resolveAction($action));
    }

    /**
     * Register a PUT route
     * 
     * @param Router $router
     * @param string $path
     * @param string|callable $action Controller@method or closure
     * @return void
     */
    public static function put(Router $router, string $path, $action)
    {
        $router->put($path, self::resolveAction($action));
    }

    /**
     * Register a PATCH route
     * 
     * @param Router $router
     * @param string $path
     * @param string|callable $action Controller@method or closure
     * @return void
     */
    public static function patch(Router $router, string $path, $action)
    {
        $router->patch($path, self::resolveAction($action));
    }

    /**
     * Register a DELETE route
     * 
     * @param Router $router
     * @param string $path
     * @param string|callable $action Controller@method or closure
     * @return void
     */
    public static function delete(Router $router, string $path, $action)
    {
        $router->delete($path, self::resolveAction($action));
    }

    /**
     * Resolve controller action to full namespaced class
     * 
     * Converts short syntax like 'BlogController@index' to full namespace
     * 'Oxygen\Controllers\BlogController@index'
     * 
     * @param string|callable $action
     * @return string|callable
     */
    protected static function resolveAction($action)
    {
        // If it's a closure or already has full namespace, return as-is
        if (!is_string($action) || strpos($action, '\\') !== false) {
            return $action;
        }

        // Split controller and method
        if (strpos($action, '@') !== false) {
            list($controller, $method) = explode('@', $action);

            // Add namespace if not present
            if (strpos($controller, '\\') === false) {
                $controller = self::CONTROLLER_NAMESPACE . $controller;
            }

            return $controller . '@' . $method;
        }

        return $action;
    }

    /**
     * Register a resource route (all CRUD operations)
     * 
     * @param Router $router
     * @param string $path Base path for the resource
     * @param string $controller Controller name
     * @return void
     */
    public static function resource(Router $router, string $path, string $controller)
    {
        $resolvedController = self::resolveAction($controller . '@index');
        $baseController = explode('@', $resolvedController)[0];

        // Index - List all
        $router->get($path, $baseController . '@index');

        // Create - Show create form
        $router->get($path . '/create', $baseController . '@create');

        // Store - Save new resource
        $router->post($path, $baseController . '@store');

        // Show - Display single resource
        $router->get($path . '/([\d]+)', $baseController . '@show');

        // Edit - Show edit form
        $router->get($path . '/([\d]+)/edit', $baseController . '@edit');

        // Update - Update resource
        $router->put($path . '/([\d]+)', $baseController . '@update');
        $router->patch($path . '/([\d]+)', $baseController . '@update');

        // Destroy - Delete resource
        $router->delete($path . '/([\d]+)', $baseController . '@destroy');
    }

    /**
     * Group routes with common attributes
     * 
     * @param Router $router
     * @param array $attributes Attributes: prefix, middleware, namespace
     * @param callable $callback
     * @return void
     */
    public static function group(Router $router, array $attributes, callable $callback)
    {
        $originalPrefix = '';
        $prefix = $attributes['prefix'] ?? '';
        
        // Apply prefix if router supports it
        if ($prefix && method_exists($router, 'setBasePath')) {
            // Store original if needed
            $originalPrefix = $router->getBasePath() ?? '';
            $router->setBasePath($originalPrefix . $prefix);
        }

        // Apply middleware if specified
        $middleware = $attributes['middleware'] ?? [];
        if (!empty($middleware)) {
            $middlewareClasses = [];
            foreach ((array) $middleware as $mw) {
                $kernel = Application::getInstance()->make(\Oxygen\Core\Kernel::class);
                $routeMiddleware = $kernel->getRouteMiddleware();
                
                if (isset($routeMiddleware[$mw])) {
                    $middlewareClasses[] = $routeMiddleware[$mw];
                }
            }

            // Apply before hook for middleware
            if (!empty($middlewareClasses)) {
                $router->before('GET|POST|PUT|PATCH|DELETE', $prefix . '.*', function () use ($middlewareClasses) {
                    $request = Request::capture();
                    foreach ($middlewareClasses as $middlewareClass) {
                        $middleware = new $middlewareClass();
                        $middleware->handle($request, function ($req) {});
                    }
                });
            }
        }

        // Execute callback
        $callback($router);

        // Restore original prefix if needed
        if ($prefix && $originalPrefix !== '') {
            $router->setBasePath($originalPrefix);
        }
    }
}
