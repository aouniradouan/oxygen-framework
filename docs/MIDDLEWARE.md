# Middleware

This guide covers the middleware system, creating custom middleware, applying middleware to routes, and built-in middleware.

## Table of Contents

- [Introduction](#introduction)
- [How Middleware Works](#how-middleware-works)
- [Global Middleware](#global-middleware)
- [Route Middleware](#route-middleware)
- [Creating Middleware](#creating-middleware)
- [Built-in Middleware](#built-in-middleware)
- [Best Practices](#best-practices)

---

## Introduction

Middleware provides a convenient mechanism for filtering HTTP requests entering your application. Each middleware can inspect, modify, or reject requests before they reach your application logic.

---

## How Middleware Works

Middleware operates in a stack, processing requests in order:

```
Request → Middleware 1 → Middleware 2 → Controller → Response
```

Each middleware can:
- Inspect the request
- Modify the request
- Pass the request to the next middleware
- Terminate the request early (redirect, error, etc.)

---

## Global Middleware

Global middleware runs on every request.

### Defining Global Middleware

**File:** `app/Core/Kernel.php`

```php
protected $middleware = [
    \Oxygen\Http\Middleware\OxygenCsrfMiddleware::class,
    \Oxygen\Http\Middleware\OxygenLocaleMiddleware::class,
];
```

These middleware execute on every HTTP request.

---

## Route Middleware

Route middleware runs only on specific routes.

### Defining Route Middleware

**File:** `app/Core/Kernel.php`

```php
protected $routeMiddleware = [
    'auth' => \Oxygen\Http\Middleware\OxygenAuthMiddleware::class,
    'csrf' => \Oxygen\Http\Middleware\OxygenCsrfMiddleware::class,
    'cors' => \Oxygen\Http\Middleware\OxygenCorsMiddleware::class,
    'api' => \Oxygen\Http\Middleware\OxygenApiMiddleware::class,
];
```

### Applying to Routes

```php
// Before hook
$router->before('GET|POST', '/admin/.*', function() {
    $middleware = new \Oxygen\Http\Middleware\OxygenAuthMiddleware();
    $middleware->handle(\Oxygen\Core\Request::capture(), function($req) {});
});

// In route group
Route::group($router, ['middleware' => 'auth'], function($router) {
    Route::get($router, '/dashboard', 'DashboardController@index');
});

// Multiple middleware
Route::group($router, ['middleware' => ['auth', 'csrf']], function($router) {
    Route::post($router, '/posts', 'PostController@store');
});
```

---

## Creating Middleware

### Generate Middleware

```bash
php oxygen make:middleware CheckAge
```

### Middleware Structure

**File:** `app/Http/Middleware/CheckAge.php`

```php
<?php

namespace Oxygen\Http\Middleware;

use Oxygen\Core\Request;

class CheckAge
{
    public function handle(Request $request, $next)
    {
        $age = $request->input('age');
        
        if ($age < 18) {
            redirect('/underage');
            return;
        }
        
        return $next($request);
    }
}
```

### Register Middleware

Add to `app/Core/Kernel.php`:

```php
protected $routeMiddleware = [
    'age' => \Oxygen\Http\Middleware\CheckAge::class,
];
```

### Use Middleware

```php
Route::group($router, ['middleware' => 'age'], function($router) {
    Route::get($router, '/adult-content', 'ContentController@index');
});
```

---

## Built-in Middleware

### OxygenAuthMiddleware

Requires user authentication.

```php
// Checks if user is logged in
// Redirects to /login if not authenticated

Route::group($router, ['middleware' => 'auth'], function($router) {
    Route::get($router, '/dashboard', 'DashboardController@index');
});
```

### OxygenCsrfMiddleware

Protects against CSRF attacks.

```php
// Validates CSRF tokens on POST requests
// Automatically enabled globally

// In forms
<form method="POST">
    {{ csrf_field|raw }}
    <!-- fields -->
</form>
```

### OxygenCorsMiddleware

Adds CORS headers for API requests.

```php
// Configurable allowed origins and methods
// Apply to API routes

Route::group($router, ['middleware' => 'cors'], function($router) {
    // API routes
});
```

### OxygenLocaleMiddleware

Sets application locale from session/request.

```php
// Automatically sets locale
// Enabled globally by default
```

### OxygenRateLimitMiddleware

Limits requests per time window.

```php
// Uses token bucket algorithm
// Configurable limits

Route::group($router, ['middleware' => 'throttle'], function($router) {
    // Rate-limited routes
});
```

---

## Best Practices

### 1. Keep Middleware Focused

```php
// Good - single responsibility
class CheckAge
{
    public function handle(Request $request, $next)
    {
        if ($request->input('age') < 18) {
            redirect('/underage');
            return;
        }
        return $next($request);
    }
}

// Avoid - too many responsibilities
class CheckEverything
{
    public function handle(Request $request, $next)
    {
        // Check age
        // Check auth
        // Check permissions
        // Validate input
        // etc.
    }
}
```

### 2. Order Matters

```php
// Good - auth before other checks
protected $middleware = [
    \Oxygen\Http\Middleware\OxygenAuthMiddleware::class,
    \Oxygen\Http\Middleware\CheckPermissions::class,
];

// Avoid - wrong order
protected $middleware = [
    \Oxygen\Http\Middleware\CheckPermissions::class,  // Runs before auth!
    \Oxygen\Http\Middleware\OxygenAuthMiddleware::class,
];
```

### 3. Use Early Returns

```php
public function handle(Request $request, $next)
{
    if (!$this->isValid($request)) {
        redirect('/error');
        return;  // Stop here
    }
    
    return $next($request);  // Continue
}
```

---

## See Also

- [Routing](ROUTING.md)
- [Authentication](AUTHENTICATION.md)
- [Security](SECURITY.md)
