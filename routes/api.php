<?php

use Oxygen\Core\Application;
use Bramus\Router\Router;
use Oxygen\Core\GraphQL\OxygenGraphQL;
use Oxygen\Core\Docs\OxygenDocs;
use Oxygen\Core\AI\OxygenAI;
use Oxygen\Core\Auth\OxygenJWT;
use Oxygen\Core\Response;
use Oxygen\Core\Request;
use Oxygen\Http\Middleware\OxygenCorsMiddleware;
use Oxygen\Http\Middleware\OxygenJwtMiddleware;
use Oxygen\Http\Middleware\OxygenRateLimitMiddleware;
use Oxygen\Core\API\OxygenAPI;


// Models Must be imported here
use Oxygen\Models\User;

// $router = Application::getInstance()->make(Router::class);

// Apply CORS middleware to all API routes
$corsMiddleware = new OxygenCorsMiddleware();
$corsMiddleware->handle(Request::capture());

// Apply rate limiting to all API routes
$rateLimitMiddleware = new OxygenRateLimitMiddleware();
$rateLimitMiddleware->handle(Request::capture());

// ============================================================================
// Authentication Endpoints (Public - No JWT Required)
// ============================================================================

// RESTful Resources
// OxygenAPI::resource('users', User::class);



// POST /api/auth/register - Register new user
$router->post('/api/auth/register', function () {
    $controller = new \Oxygen\Controllers\Auth\AuthController();
    $controller->register();
});

// POST /api/auth/login - Login and get JWT token
$router->post('/api/auth/login', function () {
    $controller = new \Oxygen\Controllers\Auth\AuthController();
    $controller->login();
});

// POST /api/auth/refresh - Refresh access token
$router->post('/api/auth/refresh', function () {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $refreshToken = $data['refresh_token'] ?? null;

    if (!$refreshToken) {
        Response::apiError('Refresh token is required', 400)->send();
        exit;
    }

    $tokens = OxygenJWT::refresh($refreshToken);

    if (!$tokens) {
        Response::apiError('Invalid or expired refresh token', 401)->send();
        exit;
    }

    Response::apiSuccess($tokens, 'Token refreshed successfully')->send();
});

// ============================================================================
// Protected Endpoints (Require JWT Authentication)
// ============================================================================

// GET /api/auth/me - Get authenticated user
$router->get('/api/auth/me', function () {
    $jwtMiddleware = new OxygenJwtMiddleware();
    $jwtMiddleware->handle(Request::capture());

    $user = $_SERVER['JWT_USER'] ?? null;

    if (!$user) {
        Response::apiError('Unauthorized', 401)->send();
        exit;
    }

    Response::apiSuccess($user, 'User retrieved successfully')->send();
});

// POST /api/auth/logout - Logout and blacklist token
$router->post('/api/auth/logout', function () {
    $jwtMiddleware = new OxygenJwtMiddleware();
    $jwtMiddleware->handle(Request::capture());

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
    $token = OxygenJWT::extractFromHeader($authHeader);

    if ($token) {
        OxygenJWT::blacklist($token);
    }

    Response::apiSuccess(null, 'Logged out successfully')->send();
});

// ============================================================================
// GraphQL Endpoints
// ============================================================================

// POST /graphql - GraphQL endpoint
$router->post('/graphql', function () {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $query = $data['query'] ?? '';
    $variables = $data['variables'] ?? [];

    $result = OxygenGraphQL::execute($query, $variables);

    header('Content-Type: application/json');
    echo json_encode($result);
});

// GET /graphql/schema - GraphQL schema
$router->get('/graphql/schema', function () {
    header('Content-Type: text/plain');
    echo OxygenGraphQL::schema();
});

// ============================================================================
// API Documentation
// ============================================================================

// GET /api/docs - API documentation
$router->get('/api/docs', function () {
    echo OxygenDocs::generate();
});

// ============================================================================
// AI Endpoints (Protected)
// ============================================================================

// POST /api/ai/sentiment - Sentiment analysis
$router->post('/api/ai/sentiment', function () {
    $jwtMiddleware = new OxygenJwtMiddleware();
    $jwtMiddleware->handle(Request::capture());

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $result = OxygenAI::sentiment($data['text'] ?? '');

    Response::apiSuccess($result, 'Sentiment analysis completed')->send();
});

// POST /api/ai/keywords - Keyword extraction
$router->post('/api/ai/keywords', function () {
    $jwtMiddleware = new OxygenJwtMiddleware();
    $jwtMiddleware->handle(Request::capture());

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $result = OxygenAI::keywords($data['text'] ?? '', $data['limit'] ?? 5);

    Response::apiSuccess(['keywords' => $result], 'Keywords extracted successfully')->send();
});

// POST /api/ai/summarize - Text summarization
$router->post('/api/ai/summarize', function () {
    $jwtMiddleware = new OxygenJwtMiddleware();
    $jwtMiddleware->handle(Request::capture());

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $result = OxygenAI::summarize($data['text'] ?? '', $data['sentences'] ?? 3);

    Response::apiSuccess(['summary' => $result], 'Text summarized successfully')->send();
});

// POST /api/ai/language - Language detection
$router->post('/api/ai/language', function () {
    $jwtMiddleware = new OxygenJwtMiddleware();
    $jwtMiddleware->handle(Request::capture());

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    $result = OxygenAI::detectLanguage($data['text'] ?? '');

    Response::apiSuccess($result, 'Language detected successfully')->send();
});

// ============================================================================
// CRM API Resources (Basic scaffold)
// ============================================================================
use Oxygen\Core\Route;

// Protect CRM API routes with JWT + Role middleware
$router->before('GET|POST|PUT|PATCH|DELETE', '/api/crm/.*', function () {
    $jwt = new \Oxygen\Http\Middleware\OxygenJwtMiddleware();
    $jwt->handle(\Oxygen\Core\Request::capture());

    $role = new \Oxygen\Http\Middleware\RoleMiddleware();
    $role->handle(\Oxygen\Core\Request::capture());
});

Route::resource($router, '/api/crm/accounts', 'CRM\\AccountController');
Route::resource($router, '/api/crm/contacts', 'CRM\\ContactController');
Route::resource($router, '/api/crm/leads', 'CRM\\LeadController');
Route::resource($router, '/api/crm/opportunities', 'CRM\\OpportunityController');
Route::resource($router, '/api/crm/tasks', 'CRM\\TaskController');
Route::resource($router, '/api/crm/activities', 'CRM\\ActivityController');

// Admin role management endpoints
Route::post($router, '/api/admin/roles/assign', 'Admin\\RoleController@assign');
Route::post($router, '/api/admin/roles/remove', 'Admin\\RoleController@remove');

