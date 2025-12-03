# Authentication

This guide covers user authentication, login, logout, registration, and authorization.

## Table of Contents

- [Introduction](#introduction)
- [Authentication Methods](#authentication-methods)
- [Login](#login)
- [Logout](#logout)
- [Registration](#registration)
- [Protecting Routes](#protecting-routes)
- [Authorization](#authorization)
- [Best Practices](#best-practices)

---

## Introduction

OxygenFramework provides a simple, session-based authentication system through the Auth class.

**File:** `app/Core/Auth.php`

---

## Authentication Methods

### Check if Authenticated

```php
if (auth()->check()) {
    // User is logged in
}
```

### Get Current User

```php
$user = auth()->user();

// Access user data
echo $user['name'];
echo $user['email'];
```

### Get User ID

```php
$userId = auth()->id();
```

---

## Login

### Attempt Login

```php
$email = request()->post('email');
$password = request()->post('password');

if (auth()->attempt($email, $password)) {
    // Login successful
    redirect('/dashboard');
} else {
    // Login failed
    Flash::set('error', 'Invalid credentials');
    back();
}
```

### Manual Login

```php
$user = User::find(1);
auth()->login($user);
```

### Login Controller Example

```php
<?php

namespace Oxygen\Controllers;

use Oxygen\Core\Controller;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        echo view('auth/login');
    }
    
    public function login()
    {
        $email = request()->post('email');
        $password = request()->post('password');
        
        if (auth()->attempt($email, $password)) {
            redirect('/dashboard');
        }
        
        Flash::set('error', 'Invalid credentials');
        back();
    }
}
```

### Login Form

```twig
{% extends "layouts/app.twig" %}

{% block content %}
    <h1>Login</h1>
    
    {{ flash_display() }}
    
    <form method="POST" action="/login">
        {{ csrf_field|raw }}
        
        <div>
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        
        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        
        <button type="submit">Login</button>
    </form>
{% endblock %}
```

---

## Logout

### Logout Method

```php
auth()->logout();
redirect('/');
```

### Logout Controller

```php
public function logout()
{
    auth()->logout();
    redirect('/');
}
```

### Logout Link

```twig
<a href="/logout">Logout</a>
```

---

## Registration

### Register Method

```php
$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'secret123'
];

$user = auth()->register($data);

if ($user) {
    // Registration successful, user is auto-logged in
    redirect('/dashboard');
} else {
    // Registration failed (email exists)
    Flash::set('error', 'Email already exists');
    back();
}
```

### Registration Controller

```php
public function showRegisterForm()
{
    echo view('auth/register');
}

public function register()
{
    $data = request()->only(['name', 'email', 'password']);
    
    // Validate
    $validator = Validator::make($data, [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
        'password' => 'required|min:8'
    ]);
    
    if ($validator->fails()) {
        Flash::set('errors', $validator->errors());
        back();
    }
    
    // Register
    $user = auth()->register($data);
    
    if ($user) {
        redirect('/dashboard');
    }
    
    Flash::set('error', 'Email already exists');
    back();
}
```

### Registration Form

```twig
<form method="POST" action="/register">
    {{ csrf_field|raw }}
    
    <div>
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name') }}" required>
    </div>
    
    <div>
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required>
    </div>
    
    <div>
        <label>Password</label>
        <input type="password" name="password" required>
    </div>
    
    <button type="submit">Register</button>
</form>
```

---

## Protecting Routes

### In Controllers

```php
public function dashboard()
{
    if (!auth()->check()) {
        redirect('/login');
    }
    
    $user = auth()->user();
    echo view('dashboard', compact('user'));
}
```

### Using Middleware

```php
// In routes/web.php
$router->before('GET|POST', '/dashboard.*', function() {
    if (!auth()->check()) {
        redirect('/login');
    }
});

Route::get($router, '/dashboard', 'DashboardController@index');
```

### Route Groups

```php
Route::group($router, ['middleware' => 'auth'], function($router) {
    Route::get($router, '/dashboard', 'DashboardController@index');
    Route::get($router, '/profile', 'ProfileController@index');
    Route::resource($router, '/posts', 'PostController');
});
```

---

## Authorization

### Check User Role

```php
if (auth()->hasRole('admin')) {
    // User is admin
}
```

### Check Permission

```php
if (auth()->can('edit-posts')) {
    // User has permission
}
```

### Check if Admin

```php
if (auth()->isAdmin()) {
    // User is admin
}
```

### In Controllers

```php
public function edit($id)
{
    if (!auth()->can('edit-posts')) {
        \Oxygen\Core\Error\ErrorHandler::handle(403, 'Access denied');
    }
    
    $post = Post::find($id);
    echo view('posts/edit', compact('post'));
}
```

### In Templates

```twig
{% if auth.check %}
    {% if auth_user().role == 'admin' %}
        <a href="/admin">Admin Panel</a>
    {% endif %}
{% endif %}
```

---

## Best Practices

### 1. Always Hash Passwords

```php
// Good - auto-hashed by auth()->register()
$user = auth()->register($data);

// Manual hashing
$hash = password_hash($password, PASSWORD_DEFAULT);
```

### 2. Regenerate Session on Login

```php
// Automatically done by auth()->login()
auth()->login($user);
```

### 3. Validate Input

```php
$validator = Validator::make($data, [
    'email' => 'required|email',
    'password' => 'required|min:8'
]);

if ($validator->fails()) {
    // Handle errors
}
```

### 4. Use CSRF Protection

```twig
<form method="POST">
    {{ csrf_field|raw }}
    <!-- fields -->
</form>
```

### 5. Protect Sensitive Routes

```php
// Use middleware
Route::group($router, ['middleware' => 'auth'], function($router) {
    // Protected routes
});
```

---

## See Also

- [Middleware](MIDDLEWARE.md)
- [Security](SECURITY.md)
- [Session Management](SESSION.md)
