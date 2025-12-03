# Helper Functions Reference

OxygenFramework provides a collection of helpful global functions to make development easier and more expressive.

## Table of Contents

- [Application Helpers](#application-helpers)
- [View Helpers](#view-helpers)
- [Routing Helpers](#routing-helpers)
- [Authentication Helpers](#authentication-helpers)
- [Session Helpers](#session-helpers)
- [Configuration Helpers](#configuration-helpers)
- [Debugging Helpers](#debugging-helpers)
- [Event Helpers](#event-helpers)
- [Logging Helpers](#logging-helpers)
- [Cache Helpers](#cache-helpers)
- [Localization Helpers](#localization-helpers)
- [Storage Helpers](#storage-helpers)

---

## Application Helpers

### `app()`

Get the application container instance.

```php
$app = app();

// Resolve from container
$view = app()->make(\Oxygen\Core\View::class);
```

**Returns:** `\Oxygen\Core\Application`

---

## View Helpers

### `view($template, $data = [])`

Render a Twig template.

```php
// Render a view
echo view('welcome');

// With data
echo view('posts/index', ['posts' => $posts]);

// Nested templates
echo view('admin/dashboard', ['user' => $user]);
```

**Parameters:**
- `$template` (string) - Template path (without .twig extension)
- `$data` (array) - Data to pass to template

**Returns:** `string` - Rendered HTML

**Example:**

```php
public function index()
{
    $posts = Post::all();
    echo view('posts/index', compact('posts'));
}
```

---

## Routing Helpers

### `redirect($url, $statusCode = 302)`

Redirect to a URL.

```php
// Simple redirect
redirect('/dashboard');

// With status code
redirect('/login', 301);

// Absolute URL
redirect('https://example.com');
```

**Parameters:**
- `$url` (string) - URL to redirect to
- `$statusCode` (int) - HTTP status code (default: 302)

**Returns:** `void` (exits script)

### `back()`

Redirect back to the previous page.

```php
// Redirect to previous page
back();

// Common use case
if (!$isValid) {
    Flash::set('error', 'Invalid input');
    back();
}
```

**Returns:** `void` (exits script)

---

## Authentication Helpers

### `auth()`

Get the authentication instance.

```php
// Check if user is logged in
if (auth()->check()) {
    // User is authenticated
}

// Get current user
$user = auth()->user();

// Login a user
auth()->login($user);

// Logout
auth()->logout();
```

**Returns:** `\Oxygen\Core\Auth`

**Methods:**
- `check()` - Check if user is authenticated
- `user()` - Get current user
- `id()` - Get current user ID
- `login($user)` - Log in a user
- `logout()` - Log out current user

**Example:**

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

---

## Session Helpers

### `session($key = null, $default = null)`

Get or set session values.

```php
// Get session value
$value = session('user_id');

// With default
$value = session('user_id', 0);

// Get all session data
$all = \Oxygen\Core\OxygenSession::all();

// Set session value
\Oxygen\Core\OxygenSession::set('user_id', 123);

// Remove session value
\Oxygen\Core\OxygenSession::forget('user_id');
```

**Parameters:**
- `$key` (string|null) - Session key
- `$default` (mixed) - Default value if key doesn't exist

**Returns:** `mixed`

### `old($key, $default = null)`

Get old input value (from previous request).

```php
// In a form after validation error
<input type="text" name="email" value="{{ old('email') }}">

// With default
$email = old('email', 'default@example.com');
```

**Parameters:**
- `$key` (string) - Input field name
- `$default` (mixed) - Default value

**Returns:** `mixed`

---

## Configuration Helpers

### `config($key, $default = null)`

Get configuration value.

```php
// Get config value
$appName = config('app.APP_NAME');

// With default
$debug = config('app.APP_DEBUG', false);

// Nested config
$dbHost = config('database.connections.mysql.host');
```

**Parameters:**
- `$key` (string) - Config key (dot notation)
- `$default` (mixed) - Default value

**Returns:** `mixed`

### `env($key, $default = null)`

Get environment variable.

```php
// Get environment variable
$appUrl = env('APP_URL');

// With default
$debug = env('APP_DEBUG', false);

// Database config
$dbHost = env('DB_HOST', '127.0.0.1');
```

**Parameters:**
- `$key` (string) - Environment variable name
- `$default` (mixed) - Default value

**Returns:** `mixed`

---

## Debugging Helpers

### `dd(...$vars)`

Dump and die - output variables and stop execution.

```php
// Dump single variable
dd($user);

// Dump multiple variables
dd($user, $posts, $comments);

// Debug query result
$posts = Post::all();
dd($posts);
```

**Parameters:**
- `...$vars` (mixed) - Variables to dump

**Returns:** `void` (exits script)

### `dump(...$vars)`

Dump variables without stopping execution.

```php
// Dump and continue
dump($user);
dump($posts);

// Continue execution
echo "Still running...";
```

**Parameters:**
- `...$vars` (mixed) - Variables to dump

**Returns:** `void`

---

## Event Helpers

### `event($event, $payload = [])`

Dispatch an event.

```php
// Dispatch event
event('user.registered', ['user' => $user]);

// Dispatch event object
event(new UserRegistered($user));

// With payload
event('order.placed', [
    'order_id' => $order->id,
    'total' => $order->total
]);
```

**Parameters:**
- `$event` (string|object) - Event name or event object
- `$payload` (mixed) - Event payload

**Returns:** `mixed`

---

## Logging Helpers

### `logger($level = null, $message = null, $context = [])`

Log a message.

```php
// Get logger instance
$logger = logger();

// Log error
logger('error', 'Payment failed', ['user_id' => 123]);

// Log warning
logger('warning', 'Low stock', ['product_id' => 456]);

// Log info
logger('info', 'User logged in', ['user_id' => 789]);

// Different log levels
logger('debug', 'Debug message');
logger('notice', 'Notice message');
logger('critical', 'Critical error');
```

**Parameters:**
- `$level` (string|null) - Log level (error, warning, info, debug, etc.)
- `$message` (string|null) - Log message
- `$context` (array) - Additional context data

**Returns:** `\Oxygen\Core\Log\Logger`

**Log Levels:**
- `emergency` - System is unusable
- `alert` - Action must be taken immediately
- `critical` - Critical conditions
- `error` - Error conditions
- `warning` - Warning conditions
- `notice` - Normal but significant
- `info` - Informational messages
- `debug` - Debug-level messages

---

## Cache Helpers

### `cache($key, $value = null, $ttl = 3600)`

Get or set cache value.

```php
// Get cached value
$posts = cache('posts');

// Set cache value (1 hour)
cache('posts', $posts, 3600);

// Set cache value (custom TTL)
cache('settings', $settings, 86400); // 24 hours

// Check if cached
if (!cache('posts')) {
    $posts = Post::all();
    cache('posts', $posts);
}
```

**Parameters:**
- `$key` (string) - Cache key
- `$value` (mixed|null) - Value to cache (null to get)
- `$ttl` (int) - Time to live in seconds (default: 3600)

**Returns:** `mixed`

---

## Localization Helpers

### `__($key, $replace = [], $locale = null)`

Translate a string.

```php
// Simple translation
echo __('welcome.message');

// With replacements
echo __('welcome.greeting', ['name' => 'John']);
// "Hello, John!"

// Specific locale
echo __('welcome.message', [], 'fr');

// In Twig templates
{{ __('welcome.message') }}
{{ __('welcome.greeting', {name: user.name}) }}
```

**Parameters:**
- `$key` (string) - Translation key (dot notation)
- `$replace` (array) - Values to replace in translation
- `$locale` (string|null) - Locale code (null for current locale)

**Returns:** `string`

**Translation Files:**

`resources/lang/en/welcome.php`:
```php
return [
    'message' => 'Welcome to OxygenFramework',
    'greeting' => 'Hello, :name!',
];
```

---

## Storage Helpers

### `storage_path($path = '')`

Get the storage directory path.

```php
// Storage directory
$storagePath = storage_path();

// Specific file
$logPath = storage_path('logs/app.log');

// Upload path
$uploadPath = storage_path('uploads/images');
```

**Parameters:**
- `$path` (string) - Path relative to storage directory

**Returns:** `string` - Absolute path

### `public_path($path = '')`

Get the public directory path.

```php
// Public directory
$publicPath = public_path();

// Specific file
$cssPath = public_path('css/app.css');
```

**Parameters:**
- `$path` (string) - Path relative to public directory

**Returns:** `string` - Absolute path

### `upload_file($file, $directory = 'uploads')`

Upload a file.

```php
// Upload file
$path = upload_file($_FILES['avatar'], 'avatars');

// Returns: 'avatars/filename.jpg'

// Full example
if (isset($_FILES['avatar'])) {
    $path = upload_file($_FILES['avatar'], 'avatars');
    User::update($userId, ['avatar' => $path]);
}
```

**Parameters:**
- `$file` (array) - File from $_FILES
- `$directory` (string) - Directory to upload to

**Returns:** `string|false` - Uploaded file path or false on failure

---

## Common Usage Patterns

### Form Handling

```php
public function store()
{
    $data = request()->only(['title', 'content']);
    
    if (!$this->validate($data)) {
        Flash::set('error', __('validation.failed'));
        back();
    }
    
    Post::create($data);
    Flash::set('success', __('post.created'));
    redirect('/posts');
}
```

### Authentication Check

```php
public function dashboard()
{
    if (!auth()->check()) {
        Flash::set('error', __('auth.required'));
        redirect('/login');
    }
    
    $user = auth()->user();
    echo view('dashboard', compact('user'));
}
```

### Caching Database Queries

```php
public function index()
{
    $posts = cache('all_posts');
    
    if (!$posts) {
        $posts = Post::all();
        cache('all_posts', $posts, 3600);
    }
    
    echo view('posts/index', compact('posts'));
}
```

### Error Logging

```php
public function processPayment()
{
    try {
        $this->chargeCard();
    } catch (\Exception $e) {
        logger('error', 'Payment failed', [
            'user_id' => auth()->id(),
            'amount' => $this->amount,
            'error' => $e->getMessage()
        ]);
        
        Flash::set('error', __('payment.failed'));
        back();
    }
}
```

---

## Creating Custom Helpers

Add your own helpers in `app/helpers/custom_helpers.php`:

```php
<?php

if (!function_exists('format_price')) {
    function format_price($amount, $currency = 'USD')
    {
        return $currency . ' ' . number_format($amount, 2);
    }
}

if (!function_exists('is_admin')) {
    function is_admin()
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }
}
```

Load in `server.php`:

```php
require_once __DIR__ . '/app/helpers/custom_helpers.php';
```

---

**See also:**
- [Getting Started](GETTING_STARTED.md)
- [Routing](ROUTING.md)
- [Database & Models](DATABASE_MODELS.md)
