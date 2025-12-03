# Error Handling

OxygenFramework provides comprehensive error handling for both development and production environments.

## Table of Contents

- [Overview](#overview)
- [Error Pages](#error-pages)
- [Development vs Production](#development-vs-production)
- [Custom Error Pages](#custom-error-pages)
- [API Error Responses](#api-error-responses)
- [Logging](#logging)
- [Handling Exceptions](#handling-exceptions)

---

## Overview

OxygenFramework automatically handles all HTTP errors and PHP exceptions, providing:

- **Beautiful error pages** for web requests
- **JSON error responses** for API requests
- **Detailed debugging** in development mode
- **Secure error messages** in production mode
- **Automatic logging** of all errors

### Supported Error Codes

- **400** - Bad Request
- **401** - Unauthorized
- **403** - Forbidden
- **404** - Not Found
- **405** - Method Not Allowed
- **500** - Internal Server Error
- **503** - Service Unavailable

---

## Error Pages

When an error occurs in a web request, OxygenFramework displays a beautiful, user-friendly error page.

### 404 - Page Not Found

Automatically shown when a route doesn't exist:

```php
// This route doesn't exist
// Visiting /nonexistent will show 404 page
```

### 500 - Internal Server Error

Shown when an uncaught exception occurs:

```php
public function index()
{
    throw new \Exception('Something went wrong!');
    // Shows 500 error page
}
```

### 403 - Forbidden

Manually trigger a 403 error:

```php
use Oxygen\Core\Error\ErrorHandler;

public function admin()
{
    if (!auth()->check()) {
        ErrorHandler::handle(403, 'Access denied');
    }
}
```

---

## Development vs Production

### Development Mode

In development mode (`DEV_MODE=true` in `.env`):

- **Detailed error pages** with stack traces
- **File and line numbers** where errors occurred
- **Full exception details**
- **Whoops error handler** (if installed)

Configure in `.env`:

```env
APP_DEBUG=true
DEV_MODE=true
```

Or in `config/errors.php`:

```php
return [
    'dev_mode' => env('DEV_MODE', false),
];
```

### Production Mode

In production mode (`DEV_MODE=false`):

- **Generic error messages** (no sensitive information)
- **Clean, professional error pages**
- **Errors logged** to files
- **No stack traces** shown to users

Configure in `.env`:

```env
APP_DEBUG=false
DEV_MODE=false
```

---

## Custom Error Pages

Error pages are located in `resources/views/errors/`.

### Available Templates

- `403.twig.html` - Forbidden
- `404.twig.html` - Not Found
- `405.twig.html` - Method Not Allowed
- `500.twig.html` - Internal Server Error
- `503.twig.html` - Service Unavailable

### Customizing Error Pages

Edit any error template in `resources/views/errors/`:

```twig
<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
</head>
<body>
    <h1>Oops! Page Not Found</h1>
    <p>{{ message }}</p>
    <a href="/">Go Home</a>
</body>
</html>
```

Available variables:
- `{{ statusCode }}` - HTTP status code
- `{{ message }}` - Error message
- `{{ debug }}` - Boolean, true if in debug mode
- `{{ exception }}` - Exception object (debug mode only)
- `{{ file }}` - File where error occurred (debug mode only)
- `{{ line }}` - Line number (debug mode only)
- `{{ trace }}` - Stack trace (debug mode only)

### Creating New Error Pages

Create a new template for any HTTP status code:

```bash
# Create 429.twig.html for "Too Many Requests"
```

```twig
<!DOCTYPE html>
<html>
<head>
    <title>429 - Too Many Requests</title>
</head>
<body>
    <h1>Slow Down!</h1>
    <p>You're making too many requests. Please try again later.</p>
</body>
</html>
```

---

## API Error Responses

For API requests (routes starting with `/api` or requests with `Accept: application/json`), errors are returned as JSON.

### JSON Error Format

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

### Debug Mode JSON

In development mode, additional debug information is included:

```json
{
    "success": false,
    "error": {
        "code": 500,
        "message": "Call to undefined method"
    },
    "debug": {
        "exception": "Error",
        "file": "/path/to/file.php",
        "line": 42,
        "trace": [...]
    },
    "timestamp": "2024-12-03T18:00:00+00:00"
}
```

### Triggering API Errors

```php
use Oxygen\Core\Error\ErrorHandler;

public function apiEndpoint()
{
    if (!$this->isValid()) {
        ErrorHandler::handle(400, 'Invalid request data');
    }
}
```

Or use Response helpers:

```php
use Oxygen\Core\Response;

public function apiEndpoint()
{
    if (!$this->isValid()) {
        return Response::apiError('Invalid request data', 400);
    }
    
    return Response::apiSuccess($data);
}
```

---

## Logging

All errors are automatically logged to `storage/logs/`.

### Log Files

- `storage/logs/error.log` - Error and exception logs
- `storage/logs/app.log` - Application logs

### Log Levels

Errors are logged with appropriate severity:

- **500+ errors** → `ERROR` level
- **400-499 errors** → `WARNING` level
- **Other** → `INFO` level

### Manual Logging

```php
// Using the logger helper
logger('error', 'Something went wrong', ['user_id' => 123]);

// Using the Logger class
use Oxygen\Core\Log\Logger;

$logger = app()->getLogger();
$logger->error('Error message', ['context' => 'data']);
$logger->warning('Warning message');
$logger->info('Info message');
```

### Log Format

```
[2024-12-03 18:00:00] ERROR: Call to undefined method
Context: {"file": "/path/to/file.php", "line": 42}
```

---

## Handling Exceptions

### Try-Catch Blocks

Handle specific exceptions in your code:

```php
public function processPayment()
{
    try {
        $this->chargeCard();
    } catch (\Exception $e) {
        logger('error', 'Payment failed: ' . $e->getMessage());
        Flash::set('error', 'Payment processing failed');
        redirect('/checkout');
    }
}
```

### Custom Exception Handling

Create custom exception handlers:

```php
public function handle()
{
    try {
        // Your code
    } catch (\PDOException $e) {
        // Database error
        ErrorHandler::handle(500, 'Database error occurred');
    } catch (\InvalidArgumentException $e) {
        // Validation error
        ErrorHandler::handle(400, $e->getMessage());
    } catch (\Exception $e) {
        // Generic error
        ErrorHandler::handle(500, 'An error occurred');
    }
}
```

### Throwing HTTP Exceptions

```php
use Oxygen\Core\Error\ErrorHandler;

public function show($id)
{
    $post = Post::find($id);
    
    if (!$post) {
        ErrorHandler::handle(404, 'Post not found');
    }
    
    echo view('posts/show', ['post' => $post]);
}
```

---

## Best Practices

### 1. Use Appropriate Status Codes

```php
// 400 - Bad Request (invalid input)
ErrorHandler::handle(400, 'Invalid email format');

// 401 - Unauthorized (not logged in)
ErrorHandler::handle(401, 'Please log in');

// 403 - Forbidden (logged in but no permission)
ErrorHandler::handle(403, 'Access denied');

// 404 - Not Found (resource doesn't exist)
ErrorHandler::handle(404, 'User not found');

// 500 - Internal Server Error (unexpected error)
ErrorHandler::handle(500, 'An unexpected error occurred');
```

### 2. Provide Helpful Messages

```php
// Bad
ErrorHandler::handle(404, 'Not found');

// Good
ErrorHandler::handle(404, 'The requested article could not be found');
```

### 3. Log Important Errors

```php
try {
    $this->criticalOperation();
} catch (\Exception $e) {
    logger('error', 'Critical operation failed', [
        'user_id' => auth()->user()->id,
        'operation' => 'payment',
        'error' => $e->getMessage()
    ]);
    ErrorHandler::handle(500, 'Operation failed');
}
```

### 4. Never Expose Sensitive Information

```php
// Bad (in production)
throw new \Exception('Database password is incorrect');

// Good
logger('error', 'Database connection failed');
ErrorHandler::handle(500, 'A system error occurred');
```

### 5. Test Error Pages

Manually test your error pages:

```php
// Test 404
Route::get($router, '/test-404', function() {
    ErrorHandler::handle(404);
});

// Test 500
Route::get($router, '/test-500', function() {
    throw new \Exception('Test exception');
});
```

---

## Configuration

### Error Configuration File

`config/errors.php`:

```php
<?php

return [
    // Enable development mode (detailed errors)
    'dev_mode' => env('DEV_MODE', false),
    
    // Error reporting level
    'error_reporting' => E_ALL,
    
    // Display errors (should be false in production)
    'display_errors' => env('APP_DEBUG', false),
];
```

### Environment Variables

`.env`:

```env
# Development
APP_DEBUG=true
DEV_MODE=true

# Production
APP_DEBUG=false
DEV_MODE=false
```

---

## Troubleshooting

### Blank White Page

If you see a blank page instead of an error:

1. Check `DEV_MODE` is `true` in `.env`
2. Check PHP error logs
3. Ensure `storage/logs/` is writable
4. Check Apache/Nginx error logs

### Error Pages Not Showing

1. Verify templates exist in `resources/views/errors/`
2. Check file permissions
3. Clear any caching
4. Check Twig template syntax

### Logs Not Writing

1. Ensure `storage/logs/` directory exists
2. Check directory permissions (should be writable)
3. Check disk space

---

**Need help? Check the [main documentation](README.md) or [open an issue](https://github.com/redwan-aouni/oxygen-framework/issues).**
