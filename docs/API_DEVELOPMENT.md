# API Development

This guide covers building RESTful APIs with OxygenFramework, including API routes, responses, authentication, and best practices.

## Table of Contents

- [Introduction](#introduction)
- [API Routes](#api-routes)
- [API Responses](#api-responses)
- [Error Handling](#error-handling)
- [Authentication](#authentication)
- [CORS](#cors)
- [Best Practices](#best-practices)

---

## Introduction

OxygenFramework provides tools for building RESTful APIs with standardized responses, automatic error handling, and CORS support.

---

## API Routes

### API Route File

**File:** `routes/api.php`

```php
<?php

use Oxygen\Core\Route;
use Bramus\Router\Router;

$router = app()->make(Router::class);

// API routes automatically prefixed with /api
Route::get($router, '/api/users', 'Api\UserController@index');
Route::get($router, '/api/users/(\d+)', 'Api\UserController@show');
Route::post($router, '/api/users', 'Api\UserController@store');
Route::put($router, '/api/users/(\d+)', 'Api\UserController@update');
Route::delete($router, '/api/users/(\d+)', 'Api\UserController@destroy');
```

### Resource Routes

```php
Route::resource($router, '/api/posts', 'Api\PostController');
```

---

## API Responses

### Success Response

```php
use Oxygen\Core\Response;

public function index()
{
    $users = User::all();
    
    $response = Response::apiSuccess($users, 'Users retrieved successfully');
    $response->send();
}
```

**Output:**

```json
{
    "success": true,
    "message": "Users retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        }
    ],
    "timestamp": "2024-12-03T18:00:00+00:00"
}
```

### Error Response

```php
public function store()
{
    $validator = Validator::make(request()->all(), [
        'name' => 'required',
        'email' => 'required|email'
    ]);
    
    if ($validator->fails()) {
        $response = Response::apiError(
            'Validation failed',
            400,
            $validator->errors()
        );
        $response->send();
        return;
    }
    
    // Create user...
}
```

**Output:**

```json
{
    "success": false,
    "error": {
        "code": 400,
        "message": "Validation failed",
        "details": {
            "name": ["Name is required"],
            "email": ["Email must be valid"]
        }
    },
    "timestamp": "2024-12-03T18:00:00+00:00"
}
```

### Paginated Response

```php
public function index()
{
    $page = request()->input('page', 1);
    $perPage = 15;
    
    $users = User::paginate($perPage);
    
    $response = Response::apiPaginated(
        $users->items,
        $users->total,
        $users->currentPage,
        $users->perPage
    );
    $response->send();
}
```

**Output:**

```json
{
    "success": true,
    "data": [...],
    "pagination": {
        "total": 100,
        "per_page": 15,
        "current_page": 1,
        "last_page": 7,
        "from": 1,
        "to": 15
    },
    "timestamp": "2024-12-03T18:00:00+00:00"
}
```

---

## Error Handling

### Automatic Error Handling

The framework automatically detects API requests and returns JSON errors:

```php
// 404 Not Found
{
    "success": false,
    "error": {
        "code": 404,
        "message": "Resource not found"
    },
    "timestamp": "2024-12-03T18:00:00+00:00"
}

// 500 Internal Server Error
{
    "success": false,
    "error": {
        "code": 500,
        "message": "An error occurred"
    },
    "timestamp": "2024-12-03T18:00:00+00:00"
}
```

### Manual Error Handling

```php
use Oxygen\Core\Error\ErrorHandler;

public function show($id)
{
    $user = User::find($id);
    
    if (!$user) {
        ErrorHandler::handle(404, 'User not found');
        return;
    }
    
    $response = Response::apiSuccess($user);
    $response->send();
}
```

---

## Authentication

### JWT Authentication

Apply JWT middleware to API routes:

```php
Route::group($router, [
    'prefix' => '/api',
    'middleware' => 'jwt'
], function($router) {
    Route::get($router, '/api/profile', 'Api\ProfileController@index');
    Route::resource($router, '/api/posts', 'Api\PostController');
});
```

### Checking Authentication

```php
public function index()
{
    if (!auth()->check()) {
        $response = Response::apiError('Unauthorized', 401);
        $response->send();
        return;
    }
    
    $user = auth()->user();
    $response = Response::apiSuccess($user);
    $response->send();
}
```

---

## CORS

### Enable CORS

Apply CORS middleware to API routes:

```php
Route::group($router, [
    'prefix' => '/api',
    'middleware' => 'cors'
], function($router) {
    // API routes
});
```

### CORS Configuration

The CORS middleware automatically adds headers:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

---

## Best Practices

### 1. Use Consistent Response Format

```php
// Good - standardized
Response::apiSuccess($data, $message);
Response::apiError($message, $code, $details);

// Avoid - custom formats
echo json_encode(['result' => $data]);
```

### 2. Validate Input

```php
public function store()
{
    $validator = Validator::make(request()->all(), [
        'title' => 'required|string|max:255',
        'content' => 'required'
    ]);
    
    if ($validator->fails()) {
        $response = Response::apiError('Validation failed', 400, $validator->errors());
        $response->send();
        return;
    }
    
    // Process...
}
```

### 3. Use Proper HTTP Status Codes

```php
// 200 - OK (success)
Response::apiSuccess($data, 'Success', 200);

// 201 - Created
Response::apiSuccess($user, 'User created', 201);

// 400 - Bad Request
Response::apiError('Invalid input', 400);

// 401 - Unauthorized
Response::apiError('Authentication required', 401);

// 403 - Forbidden
Response::apiError('Access denied', 403);

// 404 - Not Found
Response::apiError('Resource not found', 404);

// 500 - Internal Server Error
Response::apiError('Server error', 500);
```

### 4. Version Your API

```php
Route::group($router, ['prefix' => '/api/v1'], function($router) {
    Route::resource($router, '/api/v1/users', 'Api\V1\UserController');
});

Route::group($router, ['prefix' => '/api/v2'], function($router) {
    Route::resource($router, '/api/v2/users', 'Api\V2\UserController');
});
```

### 5. Document Your API

Create API documentation with endpoints, parameters, and responses.

---

## Complete Example

### API Controller

```php
<?php

namespace Oxygen\Controllers\Api;

use Oxygen\Core\Controller;
use Oxygen\Core\Response;
use Oxygen\Core\Validator;
use Oxygen\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')->paginate(15);
        
        $response = Response::apiPaginated(
            $posts->items,
            $posts->total,
            $posts->currentPage,
            $posts->perPage
        );
        $response->send();
    }
    
    public function show($id)
    {
        $post = Post::with('user')->find($id);
        
        if (!$post) {
            $response = Response::apiError('Post not found', 404);
            $response->send();
            return;
        }
        
        $response = Response::apiSuccess($post);
        $response->send();
    }
    
    public function store()
    {
        $validator = Validator::make(request()->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required'
        ]);
        
        if ($validator->fails()) {
            $response = Response::apiError('Validation failed', 400, $validator->errors());
            $response->send();
            return;
        }
        
        $data = $validator->validated();
        $data['user_id'] = auth()->id();
        
        $post = Post::create($data);
        
        $response = Response::apiSuccess($post, 'Post created successfully', 201);
        $response->send();
    }
    
    public function update($id)
    {
        $post = Post::find($id);
        
        if (!$post) {
            $response = Response::apiError('Post not found', 404);
            $response->send();
            return;
        }
        
        $validator = Validator::make(request()->all(), [
            'title' => 'string|max:255',
            'content' => 'string'
        ]);
        
        if ($validator->fails()) {
            $response = Response::apiError('Validation failed', 400, $validator->errors());
            $response->send();
            return;
        }
        
        Post::update($id, $validator->validated());
        
        $response = Response::apiSuccess($post, 'Post updated successfully');
        $response->send();
    }
    
    public function destroy($id)
    {
        $post = Post::find($id);
        
        if (!$post) {
            $response = Response::apiError('Post not found', 404);
            $response->send();
            return;
        }
        
        Post::delete($id);
        
        $response = Response::apiSuccess(null, 'Post deleted successfully');
        $response->send();
    }
}
```

---

## See Also

- [Routing](ROUTING.md)
- [Authentication](AUTHENTICATION.md)
- [Error Handling](ERROR_HANDLING.md)
