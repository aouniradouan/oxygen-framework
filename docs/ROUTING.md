# Routing

This guide covers the routing system in OxygenFramework, including basic routing, route parameters, resource routing, route groups, and middleware.

## Table of Contents

- [Introduction](#introduction)
- [Basic Routing](#basic-routing)
- [Route Parameters](#route-parameters)
- [HTTP Methods](#http-methods)
- [Route Closures](#route-closures)
- [Controllers](#controllers)
- [Resource Routing](#resource-routing)
- [Route Groups](#route-groups)
- [Middleware](#middleware)
- [404 Handling](#404-handling)

---

## Introduction

OxygenFramework uses Bramus Router for HTTP routing with a clean, expressive syntax provided by the Route helper class.

### Route Files

Routes are defined in two files:

- **`routes/web.php`** - Web application routes
- **`routes/api.php`** - API routes (optional)

### Router Instance

All routes require a router instance:

```php
use Oxygen\Core\Route;
use Bramus\Router\Router;

$router = app()->make(Router::class);
```

---

## Basic Routing

### Simple GET Route

```php
Route::get($router, '/', function() {
    echo view('welcome');
});
```

### Route to Controller

```php
Route::get($router, '/users', 'UserController@index');
```

### Multiple Routes

```php
Route::get($router, '/', 'HomeController@index');
Route::get($router, '/about', 'PageController@about');
Route::get($router, '/contact', 'PageController@contact');
```

---

## Route Parameters

### Required Parameters

```php
// Single parameter
Route::get($router, '/users/(\d+)', 'UserController@show');

// Multiple parameters
Route::get($router, '/posts/(\d+)/comments/(\d+)', 'CommentController@show');
```

### Parameter Patterns

```php
// Numeric ID
Route::get($router, '/users/(\d+)', 'UserController@show');

// Alphanumeric slug
Route::get($router, '/posts/([a-zA-Z0-9-]+)', 'PostController@show');

// Any characters
Route::get($router, '/search/([^/]+)', 'SearchController@index');
```

### Optional Parameters

```php
Route::get($router, '/search/([^/]*)?', 'SearchController@index');
```

### Using Parameters in Controllers

```php
class UserController extends Controller
{
    public function show($id)
    {
        $user = User::find($id);
        echo view('users/show', compact('user'));
    }
}
```

---

## HTTP Methods

### GET

```php
Route::get($router, '/posts', 'PostController@index');
```

### POST

```php
Route::post($router, '/posts', 'PostController@store');
```

### PUT

```php
Route::put($router, '/posts/(\d+)', 'PostController@update');
```

### PATCH

```php
Route::patch($router, '/posts/(\d+)', 'PostController@update');
```

### DELETE

```php
Route::delete($router, '/posts/(\d+)', 'PostController@destroy');
```

### Multiple Methods

```php
// Using Bramus Router directly
$router->match('GET|POST', '/form', 'FormController@handle');
```

---

## Route Closures

### Basic Closure

```php
Route::get($router, '/about', function() {
    echo view('about');
});
```

### Closure with Parameters

```php
Route::get($router, '/users/(\d+)', function($id) {
    $user = User::find($id);
    echo view('users/show', compact('user'));
});
```

### Accessing Services in Closures

```php
Route::get($router, '/dashboard', function() {
    $view = app()->make(\Oxygen\Core\View::class);
    $auth = app()->make(\Oxygen\Core\Auth::class);
    
    if (!$auth->check()) {
        redirect('/login');
    }
    
    echo $view->render('dashboard', ['user' => $auth->user()]);
});
```

---

## Controllers

### Controller Namespace Resolution

The Route helper automatically resolves controller namespaces:

```php
// Short syntax
Route::get($router, '/users', 'UserController@index');

// Resolves to
'Oxygen\Controllers\UserController@index'
```

### Namespaced Controllers

```php
// Admin namespace
Route::get($router, '/admin/users', 'Admin\UserController@index');

// Resolves to
'Oxygen\Controllers\Admin\UserController@index'
```

### Full Controller Example

```php
<?php

namespace Oxygen\Controllers;

use Oxygen\Core\Controller;
use Oxygen\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        echo view('users/index', compact('users'));
    }
    
    public function show($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            \Oxygen\Core\Error\ErrorHandler::handle(404, 'User not found');
        }
        
        echo view('users/show', compact('user'));
    }
}
```

---

## Resource Routing

Resource routing creates all CRUD routes with a single line:

```php
Route::resource($router, '/posts', 'PostController');
```

### Generated Routes

| Method | URI | Action | Route Name |
|--------|-----|--------|------------|
| GET | /posts | index | posts.index |
| GET | /posts/create | create | posts.create |
| POST | /posts | store | posts.store |
| GET | /posts/{id} | show | posts.show |
| GET | /posts/{id}/edit | edit | posts.edit |
| PUT | /posts/{id} | update | posts.update |
| DELETE | /posts/{id} | destroy | posts.destroy |

### Resource Controller

```php
class PostController extends Controller
{
    public function index()
    {
        // GET /posts
    }
    
    public function create()
    {
        // GET /posts/create
    }
    
    public function store()
    {
        // POST /posts
    }
    
    public function show($id)
    {
        // GET /posts/{id}
    }
    
    public function edit($id)
    {
        // GET /posts/{id}/edit
    }
    
    public function update($id)
    {
        // PUT /posts/{id}
    }
    
    public function destroy($id)
    {
        // DELETE /posts/{id}
    }
}
```

---

## Route Groups

Route groups allow you to share attributes across multiple routes.

### Prefix

```php
Route::group($router, ['prefix' => '/admin'], function($router) {
    Route::get($router, '/dashboard', 'Admin\DashboardController@index');
    // URL: /admin/dashboard
    
    Route::get($router, '/users', 'Admin\UserController@index');
    // URL: /admin/users
});
```

### Middleware

```php
Route::group($router, ['middleware' => 'auth'], function($router) {
    Route::get($router, '/dashboard', 'DashboardController@index');
    Route::get($router, '/profile', 'ProfileController@index');
});
```

### Combined Attributes

```php
Route::group($router, [
    'prefix' => '/admin',
    'middleware' => 'auth'
], function($router) {
    Route::get($router, '/dashboard', 'Admin\DashboardController@index');
    Route::get($router, '/users', 'Admin\UserController@index');
});
```

---

## Middleware

### Applying Middleware to Routes

```php
// Before hook for middleware
$router->before('GET|POST', '/admin/.*', function() {
    $middleware = new \Oxygen\Http\Middleware\OxygenAuthMiddleware();
    $middleware->handle(\Oxygen\Core\Request::capture(), function($req) {});
});
```

### Conditional Middleware

```php
$router->before('GET|POST|PUT|DELETE', '/admin/.*', function() {
    // Skip login/logout routes
    $uri = $_SERVER['REQUEST_URI'];
    if (strpos($uri, '/admin/login') !== false || strpos($uri, '/admin/logout') !== false) {
        return;
    }
    
    $middleware = new \Oxygen\Http\Middleware\OxygenAuthMiddleware();
    $middleware->handle(\Oxygen\Core\Request::capture(), function($req) {});
});
```

### Multiple Middleware

```php
Route::group($router, ['middleware' => ['auth', 'csrf']], function($router) {
    Route::post($router, '/posts', 'PostController@store');
});
```

---

## 404 Handling

### Default 404 Handler

The framework automatically handles 404 errors:

```php
// Registered in Application::run()
$router->set404(function() {
    \Oxygen\Core\Error\ErrorHandler::handle(404, 'Page not found');
});
```

### Custom 404 Route

```php
$router->get('/404', function() {
    $view = app()->make(\Oxygen\Core\View::class);
    echo $view->render('errors/404.twig.html');
});
```

---

## Complete Example

Here's a complete routing example from `routes/web.php`:

```php
<?php

use Oxygen\Core\Application;
use Oxygen\Core\Route;

$router = Application::getInstance()->make(\Bramus\Router\Router::class);

// Public routes
$router->get('/', function() {
    $view = Application::getInstance()->make(\Oxygen\Core\View::class);
    echo $view->render('welcome/home.twig.html');
});

// Language switcher
Route::get($router, '/lang/(\\w+)', 'LanguageController@switch');

// Auth routes
Route::get($router, '/login', 'AuthController@showLoginForm');
Route::post($router, '/login', 'AuthController@login');
Route::get($router, '/register', 'AuthController@showRegisterForm');
Route::post($router, '/register', 'AuthController@register');
Route::get($router, '/logout', 'AuthController@logout');

// Protected routes
$router->before('GET|POST', '/dashboard.*', function() {
    $auth = Application::getInstance()->make(\Oxygen\Core\Auth::class);
    if (!$auth->check()) {
        header('Location: /login');
        exit;
    }
});

Route::get($router, '/dashboard', function() {
    $auth = Application::getInstance()->make(\Oxygen\Core\Auth::class);
    $view = Application::getInstance()->make(\Oxygen\Core\View::class);
    echo $view->render('dashboard/index.twig.html', [
        'user' => $auth->user()
    ]);
});

// Resource routes
Route::resource($router, '/posts', 'PostController');
```

---

## Best Practices

### 1. Use Resource Routes

```php
// Good - concise
Route::resource($router, '/posts', 'PostController');

// Avoid - verbose
Route::get($router, '/posts', 'PostController@index');
Route::get($router, '/posts/create', 'PostController@create');
Route::post($router, '/posts', 'PostController@store');
// ... etc
```

### 2. Group Related Routes

```php
Route::group($router, ['prefix' => '/admin', 'middleware' => 'auth'], function($router) {
    Route::get($router, '/dashboard', 'Admin\DashboardController@index');
    Route::resource($router, '/users', 'Admin\UserController');
    Route::resource($router, '/posts', 'Admin\PostController');
});
```

### 3. Use Descriptive Route Patterns

```php
// Good - clear intent
Route::get($router, '/users/(\\d+)', 'UserController@show');

// Avoid - too permissive
Route::get($router, '/users/(.+)', 'UserController@show');
```

### 4. Keep Controllers Thin

```php
// Good - delegate to services
public function store()
{
    $data = request()->only(['title', 'content']);
    $this->postService->create($data);
    redirect('/posts');
}

// Avoid - too much logic in controller
public function store()
{
    // 50 lines of validation, processing, etc.
}
```

---

## See Also

- [Controllers](ROUTING.md#controllers)
- [Middleware](MIDDLEWARE.md)
- [Request and Response](REQUEST_RESPONSE.md)
