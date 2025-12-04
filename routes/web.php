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

// Prodyc Resource Routes
Route::get($router, '/prodycs', 'ProdycController@index');
Route::get($router, '/prodycs/create', 'ProdycController@create');
Route::post($router, '/prodycs/store', 'ProdycController@store');
Route::get($router, '/prodycs/(\d+)', 'ProdycController@show');
Route::get($router, '/prodycs/(\d+)/edit', 'ProdycController@edit');
Route::post($router, '/prodycs/(\d+)/update', 'ProdycController@update');
Route::get($router, '/prodycs/(\d+)/delete', 'ProdycController@destroy');

// Poster Resource Routes
Route::get($router, '/posters', 'PosterController@index');
Route::get($router, '/posters/create', 'PosterController@create');
Route::post($router, '/posters/store', 'PosterController@store');
Route::get($router, '/posters/(\d+)', 'PosterController@show');
Route::get($router, '/posters/(\d+)/edit', 'PosterController@edit');
Route::post($router, '/posters/(\d+)/update', 'PosterController@update');
Route::get($router, '/posters/(\d+)/delete', 'PosterController@destroy');

// Poster Resource Routes
Route::get($router, '/posters', 'PosterController@index');
Route::get($router, '/posters/create', 'PosterController@create');
Route::post($router, '/posters/store', 'PosterController@store');
Route::get($router, '/posters/(\d+)', 'PosterController@show');
Route::get($router, '/posters/(\d+)/edit', 'PosterController@edit');
Route::post($router, '/posters/(\d+)/update', 'PosterController@update');
Route::get($router, '/posters/(\d+)/delete', 'PosterController@destroy');
