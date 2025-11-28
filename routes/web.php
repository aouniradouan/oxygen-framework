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
Route::get($router, '/admin/login', 'Admin\AuthController@login');
Route::post($router, '/admin/login', 'Admin\AuthController@authenticate');
Route::get($router, '/admin/logout', 'Admin\AuthController@logout');

// ============================================
// Admin Dashboard Routes
// ============================================
Route::get($router, '/admin/dashboard', 'Admin\AdminController@index');

// ============================================
// Admin Article Management Routes
// ============================================
// ============================================
// Admin Article Management Routes
// ============================================
Route::get($router, '/admin/articles', 'Admin\ArticleController@index');
Route::get($router, '/admin/articles/create', 'Admin\ArticleController@create');
Route::post($router, '/admin/articles/store', 'Admin\ArticleController@store');
Route::get($router, '/admin/articles/edit/(\d+)', 'Admin\ArticleController@edit');
Route::post($router, '/admin/articles/update/(\d+)', 'Admin\ArticleController@update');
Route::get($router, '/admin/articles/delete/(\d+)', 'Admin\ArticleController@destroy');

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
    echo $view->render('dashboard.twig.html', [
        'user' => $auth->user()
    ]);
});

Route::get($router, '/users', 'UserController@index');
// Post Resource Routes
Route::get($router, '/posts', 'PostController@index');
Route::get($router, '/posts/create', 'PostController@create');
Route::post($router, '/posts/store', 'PostController@store');
Route::get($router, '/posts/(\d+)', 'PostController@show');
Route::get($router, '/posts/(\d+)/edit', 'PostController@edit');
Route::post($router, '/posts/(\d+)/update', 'PostController@update');
Route::get($router, '/posts/(\d+)/delete', 'PostController@destroy');