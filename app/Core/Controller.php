<?php

namespace Oxygen\Core;

/**
 * Base Controller Class
 * 
 * All controllers should extend this class
 */
abstract class Controller
{
    /**
     * Application instance
     * 
     * @var Application
     */
    protected $app;

    /**
     * View instance
     * 
     * @var View
     */
    protected $view;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->view = $this->app->make(View::class);
    }

    /**
     * Render a view
     * 
     * @param string $template
     * @param array $data
     * @return string
     */
    protected function render($template, $data = [])
    {
        return $this->view->render($template, $data);
    }

    /**
     * Render a view (Alias for render)
     * 
     * @param string $template
     * @param array $data
     * @return void
     */
    protected function view($template, $data = [])
    {
        echo $this->render($template, $data);
    }

    /**
     * Redirect to a URL
     * 
     * @param string $url
     * @param int $statusCode
     * @return void
     */
    protected function redirect($url, $statusCode = 302)
    {
        redirect($url, $statusCode);
    }

    /**
     * Get request input
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function input($key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Get all request input
     * 
     * @return array
     */
    protected function all()
    {
        return array_merge($_GET, $_POST);
    }

    /**
     * Check if request is POST
     * 
     * @return bool
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request is GET
     * 
     * @return bool
     */
    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Get authenticated user
     * 
     * @return array|null
     */
    protected function user()
    {
        return auth()->user();
    }

    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    protected function isAuthenticated()
    {
        return auth()->check();
    }
}
