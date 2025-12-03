<?php

use Oxygen\Core\Application;
use Oxygen\Core\Route;

$router = Application::getInstance()->make(\Bramus\Router\Router::class);

// ============================================
// Public Routes
// ============================================

$router->get('/', function () {
    $view = Application::getInstance()->make(\Oxygen\Core\View::class);
    echo $view->render('welcome/home.twig.html');
});

$router->get('/error', function () {
    throw new Exception("Redwan Test This One Day!");
});

// Language Switcher
Route::get($router, '/lang/(\w+)', 'LanguageController@switch');

// ============================================
// Admin Authentication Routes
// ============================================

// Apply Auth Middleware to all /admin routes except login
$router->before('GET|POST|PUT|DELETE', '/admin/.*', function () {
    // Skip login/logout routes
    $uri = $_SERVER['REQUEST_URI'];
    if (strpos($uri, '/admin/login') !== false || strpos($uri, '/admin/logout') !== false) {
        return;
    }

    $middleware = new \Oxygen\Http\Middleware\OxygenAuthMiddleware();
    $middleware->handle(\Oxygen\Core\Request::capture(), function ($request) {
        // Continue
    });
});

// if 404 route not found
$router->get('/404', function () {
    $view = Application::getInstance()->make(\Oxygen\Core\View::class);
    echo $view->render('errors/404.twig.html');
});

Route::get($router, '/admin/login', 'Admin\AuthController@login');
Route::post($router, '/admin/login', 'Admin\AuthController@authenticate');
Route::get($router, '/admin/logout', 'Admin\AuthController@logout');

// ============================================
// Admin Dashboard Routes
// ============================================
Route::get($router, '/admin/dashboard', 'Admin\AdminController@index');


// Auth Routes
Route::get($router, '/login', 'AuthController@showLoginForm');
Route::post($router, '/login', 'AuthController@login');
Route::get($router, '/register', 'AuthController@showRegisterForm');
Route::post($router, '/register', 'AuthController@register');
Route::get($router, '/logout', 'AuthController@logout');
Route::post($router, '/logout', 'AuthController@logout');

// Password Reset Routes
Route::get($router, '/password/reset', 'AuthController@showResetRequestForm');
Route::post($router, '/password/email', 'AuthController@sendResetLink');
Route::get($router, '/password/reset/token', 'AuthController@showResetForm');

// Dashboard (Protected Route)
Route::get($router, '/dashboard', function () {
    $auth = Application::getInstance()->make(\Oxygen\Core\Auth::class);
    if (!$auth->check()) {
        header('Location: /login');
        exit;
    }

    $view = Application::getInstance()->make(\Oxygen\Core\View::class);
    echo $view->render('dashboard/index.twig.html', [
        'user' => $auth->user()
    ]);
});