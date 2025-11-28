<?php

if (!function_exists('redirect')) {
    /**
     * Redirect to a URL
     * 
     * @param string $url
     * @param int $statusCode
     * @return void
     */
    function redirect($url, $statusCode = 302)
    {
        header("Location: $url", true, $statusCode);
        exit;
    }
}

if (!function_exists('back')) {
    /**
     * Redirect back to previous page
     * 
     * @return void
     */
    function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        redirect($referer);
    }
}

if (!function_exists('app')) {
    /**
     * Get the application instance
     * 
     * @return \Oxygen\Core\Application
     */
    function app()
    {
        return \Oxygen\Core\Application::getInstance();
    }
}

if (!function_exists('view')) {
    /**
     * Render a view
     * 
     * @param string $template
     * @param array $data
     * @return string
     */
    function view($template, $data = [])
    {
        $view = app()->make(\Oxygen\Core\View::class);
        return $view->render($template, $data);
    }
}

if (!function_exists('auth')) {
    /**
     * Get the auth instance
     * 
     * @return \Oxygen\Core\Auth
     */
    function auth()
    {
        return app()->make(\Oxygen\Core\Auth::class);
    }
}

if (!function_exists('session')) {
    /**
     * Get or set session values
     * 
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function session($key = null, $default = null)
    {
        if ($key === null) {
            return \Oxygen\Core\OxygenSession::class;
        }

        return \Oxygen\Core\OxygenSession::get($key, $default);
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function old($key, $default = null)
    {
        return \Oxygen\Core\OxygenSession::get('_old_input.' . $key, $default);
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function config($key, $default = null)
    {
        return \Oxygen\Core\OxygenConfig::get($key, $default);
    }
}

if (!function_exists('env')) {
    /**
     * Get environment variable
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die
     * 
     * @param mixed ...$vars
     * @return void
     */
    function dd(...$vars)
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
        die(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump variable
     * 
     * @param mixed ...$vars
     * @return void
     */
    function dump(...$vars)
    {
        foreach ($vars as $var) {
            echo '<pre>';
            var_dump($var);
            echo '</pre>';
        }
    }
}
