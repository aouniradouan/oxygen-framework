# OxygenFramework Complete Documentation

**Version:** 2.0  
**Author:** REDWAN AOUNI  
**License:** MIT

---

## Table of Contents

1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Architecture Overview](#architecture-overview)
4. [Application Lifecycle](#application-lifecycle)
5. [Dependency Injection Container](#dependency-injection-container)
6. [Configuration System](#configuration-system)
7. [Routing](#routing)
8. [Request and Response](#request-and-response)
9. [Controllers](#controllers)
10. [Models and Database](#models-and-database)
11. [Views and Templating](#views-and-templating)
12. [Middleware](#middleware)
13. [Authentication](#authentication)
14. [Session Management](#session-management)
15. [Validation](#validation)
16. [File Storage](#file-storage)
17. [Error Handling](#error-handling)
18. [CLI Commands](#cli-commands)
19. [Helper Functions](#helper-functions)
20. [Security](#security)
21. [Localization](#localization)
22. [Best Practices](#best-practices)

---

## Introduction

OxygenFramework is a lightweight, modern PHP framework designed for rapid application development. It combines simplicity with powerful features, providing developers with a clean, expressive syntax while maintaining high performance.

### Core Philosophy

- **Simplicity First** - Easy to learn and use
- **Convention over Configuration** - Sensible defaults
- **Performance** - Lightweight core with minimal overhead
- **Flexibility** - Extend and customize as needed
- **Modern PHP** - Utilizes PHP 7.4+ features

### Requirements

- PHP 8.1 or higher
- Composer for dependency management
- MySQL 5.7+ or MariaDB
- Apache or Nginx with mod_rewrite enabled

---

## Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/redwan-aouni/oxygen-framework.git
cd oxygen-framework
```

### Step 2: Install Dependencies

```bash
composer install
```

### Step 3: Configure Environment

```bash
cp .env.example .env
```

Edit `.env` file with your configuration:

```env
APP_NAME="OxygenFramework"
APP_URL=http://localhost:8000
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oxygen
DB_USERNAME=root
DB_PASSWORD=

DEV_MODE=true
```

### Step 4: Create Database

Create a MySQL database matching your `DB_DATABASE` value.

### Step 5: Run Development Server

```bash
php oxygen serve
```

Visit `http://localhost:8000` to see your application running.

---

## Architecture Overview

### Directory Structure

```
oxygen-framework/
├── app/
│   ├── Console/              # CLI commands
│   │   ├── Commands/         # Custom commands
│   │   └── OxygenKernel.php  # Console kernel
│   ├── Controllers/          # Application controllers
│   ├── Core/                 # Framework core
│   │   ├── Application.php   # Application container
│   │   ├── Container.php     # DI container
│   │   ├── Route.php         # Routing helper
│   │   ├── Request.php       # HTTP request
│   │   ├── Response.php      # HTTP response
│   │   ├── Model.php         # Base model
│   │   ├── View.php          # View engine
│   │   ├── Auth.php          # Authentication
│   │   ├── Validator.php     # Validation
│   │   ├── Storage.php       # File storage
│   │   ├── OxygenConfig.php  # Configuration
│   │   ├── OxygenSession.php # Session management
│   │   ├── Kernel.php        # HTTP kernel
│   │   ├── Error/            # Error handling
│   │   ├── Database/         # Database components
│   │   ├── Middleware/       # Middleware system
│   │   └── ...
│   ├── Http/
│   │   └── Middleware/       # Application middleware
│   ├── Models/               # Application models
│   ├── Services/             # Business logic
│   └── helpers/              # Helper functions
├── config/                   # Configuration files
│   ├── app.php              # Application config
│   ├── database.php         # Database config
│   ├── errors.php           # Error handling config
│   └── session.php          # Session config
├── database/
│   └── migrations/          # Database migrations
├── public/                  # Web root
│   ├── index.php           # Entry point
│   └── storage/            # Public storage (symlink)
├── resources/
│   ├── lang/               # Language files
│   └── views/              # Twig templates
├── routes/
│   ├── web.php            # Web routes
│   └── api.php            # API routes
├── storage/
│   ├── app/               # Application files
│   ├── logs/              # Log files
│   └── uploads/           # User uploads
├── vendor/                # Composer dependencies
├── .env                   # Environment configuration
├── composer.json          # PHP dependencies
├── oxygen                 # CLI executable
└── server.php            # Application bootstrap
```

### Core Components

1. **Application** - Main application container and bootstrap
2. **Container** - Dependency injection container
3. **Router** - HTTP routing (Bramus Router)
4. **Request/Response** - HTTP request and response handling
5. **Model** - Database ORM (Active Record pattern)
6. **View** - Template engine (Twig)
7. **Middleware** - HTTP request filtering
8. **Auth** - Authentication system
9. **Validator** - Input validation
10. **Storage** - File upload and storage management

---

## Application Lifecycle

### Bootstrap Process

1. **Entry Point** (`public/index.php`)
   - Checks for vendor directory
   - Loads `server.php`
   - Creates Kernel instance
   - Captures request
   - Handles request

2. **Application Bootstrap** (`server.php`)
   - Loads Composer autoloader
   - Creates Application instance
   - Registers core services
   - Initializes View
   - Loads helper functions

3. **Application Initialization** (`Application.php`)
   - Registers base bindings
   - Loads environment variables
   - Initializes configuration
   - Starts session
   - Registers error handling
   - Registers core services
   - Registers event dispatcher
   - Registers logger
   - Registers service providers

4. **Request Handling** (`Kernel.php`)
   - Executes global middleware stack
   - Runs application router

5. **Router Execution** (`Application::run()`)
   - Boots service providers
   - Registers 404 handler
   - Loads web routes
   - Loads API routes
   - Runs router with error handling

### Request Flow

```
HTTP Request
    ↓
public/index.php
    ↓
server.php (Bootstrap)
    ↓
Application::__construct()
    ↓
Kernel::handle(Request)
    ↓
Global Middleware Stack
    ↓
Application::run()
    ↓
Router (Load Routes)
    ↓
Route Matching
    ↓
Controller Method
    ↓
Response
```

---

## Dependency Injection Container

The framework uses a powerful dependency injection container for managing class dependencies and performing dependency injection.

### Container Implementation

**File:** `app/Core/Container.php`

The Container class provides:
- **Binding** - Register class bindings
- **Singleton** - Register singleton instances
- **Resolution** - Automatically resolve dependencies
- **Reflection** - Use PHP reflection to analyze constructors

### Binding Services

```php
// Simple binding
$app->bind(UserRepository::class, EloquentUserRepository::class);

// Singleton binding
$app->singleton(Database::class, function($app) {
    return new Database(config('database'));
});

// Bind with closure
$app->bind(Mailer::class, function($app) {
    return new Mailer(config('mail'));
});
```

### Resolving from Container

```php
// Resolve a class
$repository = $app->make(UserRepository::class);

// Using app() helper
$repository = app()->make(UserRepository::class);
```

### Automatic Dependency Injection

The container automatically resolves constructor dependencies:

```php
class UserController extends Controller
{
    protected $users;
    
    // Dependencies automatically injected
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }
}
```

### How It Works

1. **Reflection** - Container uses PHP's ReflectionClass to analyze constructors
2. **Dependency Resolution** - Recursively resolves all constructor parameters
3. **Instantiation** - Creates instance with resolved dependencies
4. **Caching** - Singletons are cached for reuse

---

## Configuration System

**File:** `app/Core/OxygenConfig.php`

The configuration system provides centralized access to all configuration values with support for dot notation.

### Configuration Files

Configuration files are located in `config/` directory:

- `app.php` - Application settings
- `database.php` - Database connections
- `errors.php` - Error handling
- `api.php` - API configuration
- `cors.php` - CORS settings
- `jwt.php` - JWT authentication

### Loading Configuration

Configuration is automatically loaded during application bootstrap:

```php
OxygenConfig::init($this->basePath . '/config');
```

This loads all `.php` files from the config directory.

### Accessing Configuration

```php
// Using dot notation
$appName = OxygenConfig::get('app.APP_NAME');

// With default value
$debug = OxygenConfig::get('app.APP_DEBUG', false);

// Nested values
$dbHost = OxygenConfig::get('database.connections.mysql.host');

// Using helper function
$appName = config('app.APP_NAME');
```

### Setting Configuration at Runtime

```php
// Set a value (runtime only, doesn't modify files)
OxygenConfig::set('app.timezone', 'UTC');
```

### Checking Configuration Exists

```php
if (OxygenConfig::has('database.connections.mysql')) {
    // Configuration exists
}
```

### Getting All Configuration

```php
// Get all config
$allConfig = OxygenConfig::all();

// Get specific file
$appConfig = OxygenConfig::file('app');
```

### Environment Variables

Access environment variables from `.env` file:

```php
// Using env() helper
$appUrl = env('APP_URL', 'http://localhost');
$debug = env('APP_DEBUG', false);
```

---

## Routing

The framework uses Bramus Router for HTTP routing with a clean, expressive syntax.

### Route Files

- `routes/web.php` - Web application routes
- `routes/api.php` - API routes

### Basic Routing

```php
use Oxygen\Core\Route;
use Bramus\Router\Router;

$router = app()->make(Router::class);

// GET route
Route::get($router, '/', 'HomeController@index');

// POST route
Route::post($router, '/users', 'UserController@store');

// PUT route
Route::put($router, '/users/(\d+)', 'UserController@update');

// DELETE route
Route::delete($router, '/users/(\d+)', 'UserController@destroy');

// PATCH route
Route::patch($router, '/users/(\d+)', 'UserController@update');
```

### Route Parameters

```php
// Single parameter
Route::get($router, '/users/(\d+)', 'UserController@show');

// Multiple parameters
Route::get($router, '/posts/(\d+)/comments/(\d+)', 'CommentController@show');

// Optional parameters
Route::get($router, '/search/([^/]*)?', 'SearchController@index');
```

### Closure Routes

```php
Route::get($router, '/about', function() {
    echo view('about');
});

Route::get($router, '/contact', function() {
    $view = app()->make(\Oxygen\Core\View::class);
    echo $view->render('contact');
});
```

### Resource Routes

```php
// Creates all CRUD routes
Route::resource($router, '/posts', 'PostController');

// Generates:
// GET    /posts              -> index()
// GET    /posts/create       -> create()
// POST   /posts              -> store()
// GET    /posts/(\d+)        -> show($id)
// GET    /posts/(\d+)/edit   -> edit($id)
// PUT    /posts/(\d+)        -> update($id)
// DELETE /posts/(\d+)        -> destroy($id)
```

### Route Groups

```php
Route::group($router, ['prefix' => '/admin', 'middleware' => 'auth'], function($router) {
    Route::get($router, '/dashboard', 'Admin\DashboardController@index');
    Route::get($router, '/users', 'Admin\UserController@index');
});
```

### Middleware on Routes

```php
// Before hook for middleware
$router->before('GET|POST', '/admin/.*', function() {
    $middleware = new \Oxygen\Http\Middleware\OxygenAuthMiddleware();
    $middleware->handle(\Oxygen\Core\Request::capture(), function($req) {});
});
```

### 404 Handling

The framework automatically handles 404 errors:

```php
// Registered in Application::run()
$router->set404(function() {
    \Oxygen\Core\Error\ErrorHandler::handle(404, 'Page not found');
});
```

### Controller Resolution

The Route helper automatically resolves controller namespaces:

```php
// Short syntax
Route::get($router, '/users', 'UserController@index');

// Resolves to
'Oxygen\Controllers\UserController@index'

// Namespaced controllers
Route::get($router, '/admin/users', 'Admin\UserController@index');

// Resolves to
'Oxygen\Controllers\Admin\UserController@index'
```

---

## Request and Response

### Request Class

**File:** `app/Core/Request.php`

The Request class provides an object-oriented interface to HTTP requests.

#### Capturing Requests

```php
$request = \Oxygen\Core\Request::capture();
```

#### Getting Input Data

```php
// GET parameter
$name = $request->get('name');

// POST parameter
$email = $request->post('email');

// GET or POST (POST takes priority)
$value = $request->input('field');

// With default value
$page = $request->input('page', 1);

// All input data
$data = $request->all();

// Specific fields only
$data = $request->only(['name', 'email']);
```

#### File Uploads

```php
// Get uploaded file
$file = $request->file('avatar');

// Check if file exists
if ($request->hasFile('avatar')) {
    // Handle upload
}
```

#### Request Information

```php
// HTTP method
$method = $request->method(); // GET, POST, PUT, DELETE

// Request URI
$uri = $request->uri();
```

#### Cleaning Input

```php
// Strip HTML tags from all input
$clean = $request->clean();
```

### Response Class

**File:** `app/Core/Response.php`

The Response class provides methods for sending HTTP responses.

#### Basic Response

```php
$response = new \Oxygen\Core\Response('Hello World', 200);
$response->send();
```

#### JSON Response

```php
$response = \Oxygen\Core\Response::json(['message' => 'Success'], 200);
$response->send();
```

#### API Responses

```php
// Success response
$response = \Oxygen\Core\Response::apiSuccess($data, 'Operation successful', 200);

// Error response
$response = \Oxygen\Core\Response::apiError('Validation failed', 400, $errors);

// Paginated response
$response = \Oxygen\Core\Response::apiPaginated($items, $total, $page, $perPage);
```

#### Redirects

```php
// Redirect to URL
\Oxygen\Core\Response::redirect('/dashboard');

// Using helper
redirect('/dashboard');
back(); // Redirect to previous page
```

#### Setting Headers

```php
$response = new \Oxygen\Core\Response('Content');
$response->header('Content-Type', 'application/json');
$response->header('X-Custom-Header', 'value');
$response->send();
```

---

## Controllers

Controllers handle HTTP requests and contain your application logic.

### Base Controller

**File:** `app/Core/Controller.php`

All controllers extend the base Controller class.

### Creating Controllers

```bash
php oxygen make:controller UserController
```

### Controller Structure

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
    
    public function store()
    {
        $data = request()->only(['name', 'email', 'password']);
        
        // Validate
        $validator = \Oxygen\Core\Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);
        
        if ($validator->fails()) {
            Flash::set('errors', $validator->errors());
            back();
        }
        
        // Create user
        User::create($data);
        
        Flash::set('success', 'User created successfully');
        redirect('/users');
    }
}
```

### Resource Controllers

Resource controllers handle CRUD operations:

```php
class PostController extends Controller
{
    public function index()        // GET /posts
    public function create()       // GET /posts/create
    public function store()        // POST /posts
    public function show($id)      // GET /posts/{id}
    public function edit($id)      // GET /posts/{id}/edit
    public function update($id)    // PUT /posts/{id}
    public function destroy($id)   // DELETE /posts/{id}
}
```

---

## Models and Database

The framework uses an Active Record pattern for database interaction with Nette Database as the underlying driver.

### Base Model

**File:** `app/Core/Model.php`

### Creating Models

```bash
php oxygen make:model Post
php oxygen make:model Post --migration
```

### Model Structure

```php
<?php

namespace Oxygen\Models;

use Oxygen\Core\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $primaryKey = 'id';
    protected $fillable = ['title', 'content', 'user_id', 'status'];
    protected $hidden = ['deleted_at'];
    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean'
    ];
    protected $dates = ['created_at', 'updated_at', 'published_at'];
}
```

### Model Properties

- **$table** - Database table name (auto-guessed from class name)
- **$primaryKey** - Primary key column (default: 'id')
- **$fillable** - Mass-assignable attributes
- **$hidden** - Attributes hidden from array/JSON output
- **$casts** - Attribute type casting
- **$dates** - Attributes treated as dates

### CRUD Operations

```php
// Create
$post = Post::create([
    'title' => 'My Post',
    'content' => 'Post content',
    'user_id' => 1
]);

// Read all
$posts = Post::all();

// Find by ID
$post = Post::find(1);

// Update
Post::update(1, ['title' => 'Updated Title']);

// Delete
Post::delete(1);
```

### Query Building

```php
// Where clause
$posts = Post::where('status', '=', 'published')->get();

// Multiple where
$posts = Post::where('status', '=', 'published')
             ->where('user_id', '=', 1)
             ->get();

// Where In
$posts = Post::whereIn('status', ['published', 'draft'])->get();

// Pagination
$posts = Post::paginate(15);

// With relationships
$posts = Post::with('user')->get();
```

### Relationships

The framework supports relationships through the HasRelationships trait:

```php
class Post extends Model
{
    use \Oxygen\Core\Traits\HasRelationships;
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class User extends Model
{
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

// Usage
$post = Post::with('user')->find(1);
$user = $post->user;

$user = User::with('posts')->find(1);
$posts = $user->posts;
```

### Database Connection

**File:** `config/database.php`

```php
return [
    'default' => env('DB_CONNECTION', 'mysql'),
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'oxygen'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'dsn' => 'mysql:host=...'
        ]
    ]
];
```

### Migrations

```bash
# Create migration
php oxygen make:migration create_posts_table

# Run migrations
php oxygen migrate

# Rollback
php oxygen migrate:rollback
```

Migration structure:

```php
<?php

use Oxygen\Core\Database\Migration;

class CreatePostsTable extends Migration
{
    public function up()
    {
        $this->schema->create('posts', function($table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->integer('user_id');
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down()
    {
        $this->schema->dropIfExists('posts');
    }
}
```

---

## Views and Templating

The framework uses Twig as its templating engine.

### View Class

**File:** `app/Core/View.php`

### Rendering Views

```php
// In controller
echo view('posts/index', ['posts' => $posts]);

// Using View class
$view = app()->make(\Oxygen\Core\View::class);
echo $view->render('posts/index', ['posts' => $posts]);
```

### Template Locations

Templates are located in `resources/views/`:

```
resources/views/
├── layouts/
│   └── app.twig
├── components/
│   └── header.twig
├── posts/
│   ├── index.twig.html
│   ├── show.twig.html
│   └── create.twig.html
└── errors/
    ├── 404.twig.html
    └── 500.twig.html
```

### Template Syntax

```twig
{# Extends layout #}
{% extends "layouts/app.twig" %}

{# Define block #}
{% block title %}Posts{% endblock %}

{% block content %}
    <h1>Posts</h1>
    
    {# Loop #}
    {% for post in posts %}
        <article>
            <h2>{{ post.title }}</h2>
            <p>{{ post.content }}</p>
        </article>
    {% endfor %}
    
    {# Conditional #}
    {% if posts|length > 0 %}
        <p>Found {{ posts|length }} posts</p>
    {% else %}
        <p>No posts found</p>
    {% endif %}
{% endblock %}
```

### Global Variables

Available in all templates:

- `APP_URL` - Application URL
- `APP_NAME` - Application name
- `csrf_token` - CSRF token value
- `csrf_field` - CSRF hidden input field
- `auth.check` - Boolean, user authenticated
- `auth.user` - Current user object
- `is_rtl` - Boolean, RTL mode
- `text_direction` - Text direction (ltr/rtl)
- `current_locale` - Current locale code

### Global Functions

```twig
{# Authentication #}
{% if auth_check() %}
    {{ auth_user().name }}
{% endif %}

{# URLs and Assets #}
{{ url('/posts') }}
{{ asset('css/app.css') }}
{{ storage('uploads/image.jpg') }}
{{ storage_url('uploads/image.jpg') }}

{# Theme assets #}
{{ theme_asset('css/style.css') }}

{# Flash messages #}
{{ flash_display() }}

{# Localization #}
{{ __('welcome.message') }}
{{ __('welcome.greeting', {name: user.name}) }}

{# RTL support #}
{{ rtl_class('text-left', 'text-right') }}
{{ direction() }}
{{ locale() }}

{# JSON decode #}
{% set data = json_decode(post.metadata) %}

{# Asset helpers #}
{{ oxygen_css() }}
{{ oxygen_js() }}
```

### Template Inheritance

**Layout:** `resources/views/layouts/app.twig`

```twig
<!DOCTYPE html>
<html>
<head>
    <title>{% block title %}{% endblock %} - {{ APP_NAME }}</title>
    {{ oxygen_css() }}
</head>
<body>
    <header>
        {% include 'components/header.twig' %}
    </header>
    
    <main>
        {% block content %}{% endblock %}
    </main>
    
    <footer>
        {% include 'components/footer.twig' %}
    </footer>
    
    {{ oxygen_js() }}
</body>
</html>
```

**Page:** `resources/views/posts/index.twig.html`

```twig
{% extends "layouts/app.twig" %}

{% block title %}Posts{% endblock %}

{% block content %}
    <h1>All Posts</h1>
    {% for post in posts %}
        <article>{{ post.title }}</article>
    {% endfor %}
{% endblock %}
```

---

## Middleware

Middleware provides a convenient mechanism for filtering HTTP requests.

### Middleware System

**Files:**
- `app/Core/Middleware/Middleware.php` - Base middleware interface
- `app/Core/Middleware/MiddlewareStack.php` - Middleware stack implementation
- `app/Core/Kernel.php` - HTTP kernel with middleware

### Global Middleware

Defined in `app/Core/Kernel.php`:

```php
protected $middleware = [
    \Oxygen\Http\Middleware\OxygenCsrfMiddleware::class,
    \Oxygen\Http\Middleware\OxygenLocaleMiddleware::class,
];
```

Global middleware runs on every request.

### Route Middleware

Defined in `app/Core/Kernel.php`:

```php
protected $routeMiddleware = [
    'auth' => \Oxygen\Http\Middleware\OxygenAuthMiddleware::class,
    'guest' => \Oxygen\Http\Middleware\OxygenGuestMiddleware::class,
    'csrf' => \Oxygen\Http\Middleware\OxygenCsrfMiddleware::class,
    'cors' => \Oxygen\Http\Middleware\OxygenCorsMiddleware::class,
    'api' => \Oxygen\Http\Middleware\OxygenApiMiddleware::class,
];
```

### Creating Middleware

```bash
php oxygen make:middleware CheckAge
```

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

### Applying Middleware to Routes

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
```

### Built-in Middleware

**OxygenAuthMiddleware** - Requires authentication

```php
// Checks if user is logged in
// Redirects to /login if not authenticated
```

**OxygenCsrfMiddleware** - CSRF protection

```php
// Validates CSRF tokens on POST requests
// Automatically enabled globally
```

**OxygenCorsMiddleware** - CORS headers

```php
// Adds CORS headers for API requests
// Configurable allowed origins and methods
```

**OxygenLocaleMiddleware** - Localization

```php
// Sets application locale from session/request
```

**OxygenRateLimitMiddleware** - Rate limiting

```php
// Limits requests per time window
// Uses token bucket algorithm
```

---

## Authentication

**File:** `app/Core/Auth.php`

The Auth class provides session-based authentication.

### Authentication Methods

```php
// Check if user is authenticated
if (auth()->check()) {
    // User is logged in
}

// Get current user
$user = auth()->user();

// Get user ID
$userId = auth()->id();

// Login a user
auth()->login($user);

// Logout
auth()->logout();

// Attempt login
if (auth()->attempt($email, $password)) {
    // Login successful
}
```

### Login Example

```php
public function authenticate()
{
    $email = request()->post('email');
    $password = request()->post('password');
    
    if (auth()->attempt($email, $password)) {
        redirect('/dashboard');
    }
    
    Flash::set('error', 'Invalid credentials');
    back();
}
```

### Protecting Routes

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

### In Templates

```twig
{% if auth.check %}
    <p>Welcome, {{ auth.user.name }}</p>
    <a href="/logout">Logout</a>
{% else %}
    <a href="/login">Login</a>
{% endif %}
```

---

## Session Management

**File:** `app/Core/OxygenSession.php`

The session system provides a clean interface for working with PHP sessions.

### Session Methods

```php
// Store value
\Oxygen\Core\OxygenSession::put('user_id', 123);

// Get value
$userId = \Oxygen\Core\OxygenSession::get('user_id');

// Get with default
$page = \Oxygen\Core\OxygenSession::get('page', 1);

// Check if exists
if (\Oxygen\Core\OxygenSession::has('user_id')) {
    // Session exists
}

// Remove value
\Oxygen\Core\OxygenSession::forget('user_id');

// Get and remove
$value = \Oxygen\Core\OxygenSession::pull('temp_data');

// Get all session data
$all = \Oxygen\Core\OxygenSession::all();

// Destroy session
\Oxygen\Core\OxygenSession::destroy();

// Regenerate session ID
\Oxygen\Core\OxygenSession::regenerate();

// Get session ID
$id = \Oxygen\Core\OxygenSession::id();
```

### Flash Messages

Flash data is available only for the next request:

```php
// Set flash message
\Oxygen\Core\OxygenSession::flash('success', 'Profile updated!');

// Get flash data
$flash = \Oxygen\Core\OxygenSession::getFlash();

// Get specific flash message
$message = \Oxygen\Core\OxygenSession::getFlashMessage('success');
```

### Using Flash Helper

```php
use Oxygen\Core\Flash;

// Set flash
Flash::set('success', 'Operation successful');
Flash::set('error', 'Operation failed');

// Display in template
{{ flash_display() }}
```


---

## Validation

**File:** `app/Core/Validator.php`

The Validator class provides input validation with common rules.

### Basic Validation

```php
use Oxygen\Core\Validator;

$data = request()->all();

$validator = Validator::make($data, [
    'name' => 'required|string|max:255',
    'email' => 'required|email',
    'age' => 'required|integer|min:18',
    'website' => 'url',
    'status' => 'in:active,inactive'
]);

if ($validator->fails()) {
    $errors = $validator->errors();
    // Handle errors
}

$validated = $validator->validated();
```

### Validation Rules

- **required** - Field must be present and not empty
- **string** - Must be a string
- **integer** - Must be an integer
- **numeric** - Must be numeric
- **email** - Must be valid email
- **url** - Must be valid URL
- **min:value** - Minimum value/length
- **max:value** - Maximum value/length
- **in:val1,val2** - Must be one of specified values
- **boolean** - Must be boolean
- **date** - Must be valid date
- **regex:pattern** - Must match regex pattern

### Custom Error Messages

```php
$validator = Validator::make($data, $rules, [
    'name.required' => 'Please enter your name',
    'email.email' => 'Invalid email format',
    'age.min' => 'You must be at least 18 years old'
]);
```

### Using in Controllers

```php
public function store()
{
    $validator = Validator::make(request()->all(), [
        'title' => 'required|string|max:255',
        'content' => 'required',
        'status' => 'in:draft,published'
    ]);
    
    if ($validator->fails()) {
        Flash::set('errors', $validator->errors());
        Flash::set('old', request()->all());
        back();
    }
    
    $data = $validator->validated();
    Post::create($data);
    
    Flash::set('success', 'Post created successfully');
    redirect('/posts');
}
```

---

## File Storage

**File:** `app/Core/Storage.php`

The Storage class provides a clean API for file uploads and management.

### Uploading Files

```php
use Oxygen\Core\Storage;

// Upload single file
$path = Storage::upload('avatar', 'profiles');

// Upload multiple files
$paths = Storage::upload(['photo1', 'photo2'], 'gallery');

// Upload with options
$path = Storage::upload('file', 'documents', [
    'disk' => 'local',
    'maxSize' => 10485760, // 10MB
    'allowedTypes' => ['jpg', 'png', 'pdf']
]);
```

### Type-Specific Uploads

```php
// Upload image
$path = Storage::uploadImage('photo', 'images');

// Upload video
$path = Storage::uploadVideo('video', 'videos');

// Upload document
$path = Storage::uploadDocument('file', 'documents');
```

### File Operations

```php
// Delete file
Storage::delete($path);

// Delete multiple files
Storage::delete([$path1, $path2]);

// Check if exists
if (Storage::exists($path)) {
    // File exists
}

// Get file size
$size = Storage::size($path);

// Get file URL
$url = Storage::url($path);

// Get full path
$fullPath = Storage::getFullPath($path);
```

### Storage Configuration

```php
// Set default disk
Storage::setDefaultDisk('s3');

// Get default disk
$disk = Storage::getDefaultDisk();
```

### In Controllers

```php
public function updateAvatar()
{
    if (request()->hasFile('avatar')) {
        // Delete old avatar
        if ($user->avatar) {
            Storage::delete($user->avatar);
        }
        
        // Upload new avatar
        $path = Storage::uploadImage('avatar', 'avatars');
        
        // Update user
        User::update($user->id, ['avatar' => $path]);
    }
    
    redirect('/profile');
}
```

---

## Error Handling

**File:** `app/Core/Error/ErrorHandler.php`

The framework provides comprehensive error handling for all HTTP errors and exceptions.

### Error Handler Features

- Handles all HTTP status codes (400, 403, 404, 405, 500, 503, etc.)
- Detects API vs web requests automatically
- Beautiful HTML error pages for web
- JSON error responses for APIs
- Detailed errors in development mode
- Generic errors in production mode
- Automatic error logging

### Triggering Errors

```php
use Oxygen\Core\Error\ErrorHandler;

// 404 Not Found
ErrorHandler::handle(404, 'Resource not found');

// 403 Forbidden
ErrorHandler::handle(403, 'Access denied');

// 500 Internal Server Error
ErrorHandler::handle(500, 'An error occurred');

// With exception
try {
    // Code
} catch (\Exception $e) {
    ErrorHandler::handleException($e);
}
```

### Error Pages

Error templates located in `resources/views/errors/`:

- `403.twig.html` - Forbidden
- `404.twig.html` - Not Found
- `405.twig.html` - Method Not Allowed
- `500.twig.html` - Internal Server Error
- `503.twig.html` - Service Unavailable

### Development vs Production

**Development Mode** (`DEV_MODE=true`):
- Detailed error pages with stack traces
- File and line numbers
- Exception details
- Whoops error handler (if installed)

**Production Mode** (`DEV_MODE=false`):
- Generic error messages
- No sensitive information
- Errors logged to files
- Clean, professional error pages

### API Error Responses

For API requests, errors return JSON:

```json
{
    "success": false,
    "error": {
        "code": 404,
        "message": "Resource not found"
    },
    "timestamp": "2024-12-03T18:00:00+00:00"
}
```

With debug mode enabled:

```json
{
    "success": false,
    "error": {
        "code": 500,
        "message": "Error message"
    },
    "debug": {
        "exception": "Exception",
        "file": "/path/to/file.php",
        "line": 42,
        "trace": [...]
    },
    "timestamp": "2024-12-03T18:00:00+00:00"
}
```

### Error Logging

All errors are automatically logged to `storage/logs/`:

- `error.log` - Error and exception logs
- `app.log` - Application logs

Log format:

```
[2024-12-03 18:00:00] ERROR: Error message
Context: {"status_code": 500, "url": "/path", "method": "GET"}
```

---

## CLI Commands

The framework includes a powerful CLI for scaffolding and management.

### Available Commands

```bash
# List all commands
php oxygen list

# Serve application
php oxygen serve

# Generate resources
php oxygen make:model User
php oxygen make:controller UserController
php oxygen make:middleware CheckAge
php oxygen make:migration create_users_table
php oxygen make:mvc Post  # Model + Controller + Views

# Database migrations
php oxygen migrate
php oxygen migrate:rollback

# Queue management
php oxygen queue:work

# Scheduler
php oxygen schedule:run

# Testing
php oxygen test:all
php oxygen test:generate

# Maintenance
php oxygen cleanup:logs
```

### Creating Custom Commands

**File:** `app/Console/Commands/CustomCommand.php`

```php
<?php

namespace Oxygen\Console\Commands;

use Oxygen\Console\Command;

class CustomCommand extends Command
{
    protected $signature = 'custom:command {argument} {--option=}';
    protected $description = 'Custom command description';
    
    public function handle()
    {
        $argument = $this->argument('argument');
        $option = $this->option('option');
        
        $this->info('Command executed');
        $this->error('Error message');
        $this->success('Success message');
    }
}
```

Register in `app/Console/OxygenKernel.php`:

```php
protected $commands = [
    \Oxygen\Console\Commands\CustomCommand::class,
];
```

---

## Helper Functions

**File:** `app/helpers/helpers.php`

### Application Helpers

```php
// Get application instance
$app = app();

// Resolve from container
$view = app()->make(\Oxygen\Core\View::class);
```

### View Helpers

```php
// Render view
echo view('posts/index', ['posts' => $posts]);
```

### Routing Helpers

```php
// Redirect
redirect('/dashboard');

// Redirect back
back();
```

### Authentication Helpers

```php
// Get auth instance
$auth = auth();

// Check authentication
if (auth()->check()) {
    $user = auth()->user();
}
```

### Session Helpers

```php
// Get session value
$value = session('key', 'default');

// Get old input
$email = old('email');
```

### Configuration Helpers

```php
// Get config value
$appName = config('app.APP_NAME');

// Get environment variable
$debug = env('APP_DEBUG', false);
```

### Debugging Helpers

```php
// Dump and die
dd($variable);

// Dump without dying
dump($variable);
```

### Event Helpers

```php
// Dispatch event
event('user.registered', ['user' => $user]);
```

### Logging Helpers

```php
// Log message
logger('error', 'Error message', ['context' => 'data']);

// Get logger instance
$logger = logger();
```

### Cache Helpers

```php
// Get cached value
$posts = cache('posts');

// Set cache value
cache('posts', $posts, 3600);
```

### Localization Helpers

```php
// Translate
echo __('welcome.message');

// With replacements
echo __('welcome.greeting', ['name' => 'John']);
```

### Storage Helpers

```php
// Get storage path
$path = storage_path('logs/app.log');

// Get public path
$path = public_path('css/app.css');

// Upload file
$path = upload_file($_FILES['avatar'], 'avatars');
```

---

## Security

### CSRF Protection

**File:** `app/Core/CSRF.php`

CSRF protection is enabled globally via `OxygenCsrfMiddleware`.

```php
// Generate token
$token = $csrf->token();

// Generate hidden field
echo $csrf->field();

// Verify token
if ($csrf->verify($token)) {
    // Valid
}
```

In forms:

```twig
<form method="POST">
    {{ csrf_field|raw }}
    <!-- form fields -->
</form>
```

### XSS Protection

- All user input is escaped in Twig templates by default
- Use `|raw` filter only for trusted content
- Clean input with `request()->clean()`

### SQL Injection Protection

- Use parameterized queries (Nette Database handles this)
- Never concatenate user input into queries
- Use Model methods which are safe by default

### Password Hashing

```php
// Hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Verify password
if (password_verify($password, $hash)) {
    // Correct password
}
```

### Session Security

```php
// Regenerate session ID after login
\Oxygen\Core\OxygenSession::regenerate();

// Destroy session on logout
\Oxygen\Core\OxygenSession::destroy();
```

---

## Localization

**File:** `app/Core/Lang.php`

The framework supports multi-language applications with RTL support.

### Language Files

Located in `resources/lang/{locale}/`:

```
resources/lang/
├── en/
│   ├── welcome.php
│   └── messages.php
├── fr/
│   ├── welcome.php
│   └── messages.php
└── ar/
    ├── welcome.php
    └── messages.php
```

### Language File Structure

`resources/lang/en/welcome.php`:

```php
<?php

return [
    'message' => 'Welcome to OxygenFramework',
    'greeting' => 'Hello, :name!',
];
```

### Using Translations

```php
// Get translation
$message = __('welcome.message');

// With replacements
$greeting = __('welcome.greeting', ['name' => 'John']);

// Specific locale
$message = __('welcome.message', [], 'fr');
```

### In Templates

```twig
{{ __('welcome.message') }}
{{ __('welcome.greeting', {name: user.name}) }}
```

### Setting Locale

```php
use Oxygen\Core\Lang;

// Set locale
Lang::setLocale('fr');

// Get current locale
$locale = Lang::getLocale();

// Check if RTL
if (Lang::isRTL()) {
    // RTL language
}

// Get text direction
$direction = Lang::getDirection(); // 'ltr' or 'rtl'
```

### RTL Support

```twig
<html dir="{{ text_direction }}">
    <body class="{{ rtl_class('ltr-class', 'rtl-class') }}">
        <!-- content -->
    </body>
</html>
```

---

## Best Practices

### Code Organization

1. **Controllers** - Keep controllers thin, move logic to services
2. **Models** - Use models for database interaction only
3. **Services** - Create service classes for business logic
4. **Validation** - Validate all user input
5. **Security** - Never trust user input

### Performance

1. **Database** - Use eager loading to avoid N+1 queries
2. **Caching** - Cache expensive operations
3. **Assets** - Minify CSS and JavaScript
4. **Queries** - Optimize database queries
5. **Sessions** - Clean up old session data

### Security

1. **CSRF** - Always use CSRF protection on forms
2. **XSS** - Escape all output
3. **SQL Injection** - Use parameterized queries
4. **Passwords** - Hash passwords with `password_hash()`
5. **Sessions** - Regenerate session ID after login

### Error Handling

1. **Production** - Never show detailed errors in production
2. **Logging** - Log all errors for debugging
3. **User-Friendly** - Show friendly error messages to users
4. **Monitoring** - Monitor error logs regularly

### Testing

1. **Unit Tests** - Test individual components
2. **Integration Tests** - Test component interactions
3. **Feature Tests** - Test complete features
4. **Coverage** - Aim for high test coverage

---

## Conclusion

OxygenFramework provides a solid foundation for building modern PHP applications. Its clean architecture, powerful features, and developer-friendly design make it an excellent choice for projects of any size.

For support and contributions, visit the GitHub repository or contact the author.

**Author:** REDWAN AOUNI  
**Email:** aouniradouan@gmail.com  
**Location:** Algeria

**Made with care in Algeria**
