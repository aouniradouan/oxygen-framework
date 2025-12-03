<?php

namespace Oxygen\Core\Error;

use Oxygen\Core\Application;
use Oxygen\Core\View;
use Oxygen\Core\Response;
use Oxygen\Core\OxygenConfig;
use Oxygen\Core\Log\Logger;

/**
 * ErrorHandler - Comprehensive HTTP Error Handler
 * 
 * Handles all HTTP errors (400, 403, 404, 405, 500, 503, etc.)
 * Provides beautiful error pages for web requests and JSON responses for API requests.
 * 
 * @package    Oxygen\Core\Error
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
class ErrorHandler
{
    /**
     * HTTP status code messages
     */
    protected static $statusMessages = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        408 => 'Request Timeout',
        429 => 'Too Many Requests',
        500 => 'Internal Server Error',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
    ];

    /**
     * Handle an HTTP error
     * 
     * @param int $statusCode HTTP status code
     * @param string|null $message Custom error message
     * @param \Throwable|null $exception Optional exception
     * @return void
     */
    public static function handle($statusCode = 500, $message = null, $exception = null)
    {
        // Set HTTP response code
        http_response_code($statusCode);

        // Get default message if not provided
        if (!$message) {
            $message = self::$statusMessages[$statusCode] ?? 'An error occurred';
        }

        // Log the error
        self::logError($statusCode, $message, $exception);

        // Check if this is an API request
        if (self::isApiRequest()) {
            self::renderApiError($statusCode, $message, $exception);
        } else {
            self::renderErrorPage($statusCode, $message, $exception);
        }

        exit;
    }

    /**
     * Handle an exception
     * 
     * @param \Throwable $exception
     * @return void
     */
    public static function handleException($exception)
    {
        $statusCode = method_exists($exception, 'getStatusCode')
            ? $exception->getStatusCode()
            : 500;

        self::handle($statusCode, $exception->getMessage(), $exception);
    }

    /**
     * Handle fatal errors (shutdown handler)
     * 
     * @return void
     */
    public static function handleShutdown()
    {
        $error = error_get_last();

        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::handle(500, $error['message']);
        }
    }

    /**
     * Check if the current request is an API request
     * 
     * @return bool
     */
    protected static function isApiRequest()
    {
        // Check if request URI starts with /api
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api') === 0) {
            return true;
        }

        // Check Accept header for JSON
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            return true;
        }

        // Check Content-Type header
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Render HTML error page
     * 
     * @param int $statusCode
     * @param string $message
     * @param \Throwable|null $exception
     * @return void
     */
    protected static function renderErrorPage($statusCode, $message, $exception = null)
    {
        $debug = OxygenConfig::get('errors.dev_mode', false);

        // Try to use Twig template if available
        try {
            $app = Application::getInstance();
            $view = $app->make(View::class);

            $data = [
                'statusCode' => $statusCode,
                'message' => $message,
                'debug' => $debug,
            ];

            // Add exception details in debug mode
            if ($debug && $exception) {
                $data['exception'] = $exception;
                $data['file'] = $exception->getFile();
                $data['line'] = $exception->getLine();
                $data['trace'] = $exception->getTraceAsString();
            }

            // Try to render error template
            $template = "errors/{$statusCode}.twig.html";
            echo $view->render($template, $data);
            return;

        } catch (\Exception $e) {
            // If Twig fails, fall back to standalone HTML
            self::renderStandaloneErrorPage($statusCode, $message, $exception);
        }
    }

    /**
     * Render standalone HTML error page (no dependencies)
     * 
     * @param int $statusCode
     * @param string $message
     * @param \Throwable|null $exception
     * @return void
     */
    protected static function renderStandaloneErrorPage($statusCode, $message, $exception = null)
    {
        $debug = OxygenConfig::get('errors.dev_mode', false);
        $title = self::$statusMessages[$statusCode] ?? 'Error';

        $debugInfo = '';
        if ($debug && $exception) {
            $file = $exception->getFile();
            $line = $exception->getLine();
            $trace = htmlspecialchars($exception->getTraceAsString());

            $debugInfo = <<<HTML
            <div class="debug-info">
                <h3>Debug Information</h3>
                <p><strong>File:</strong> {$file}</p>
                <p><strong>Line:</strong> {$line}</p>
                <div class="trace">
                    <strong>Stack Trace:</strong>
                    <pre>{$trace}</pre>
                </div>
            </div>
HTML;
        }

        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$statusCode} - {$title}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        .error-code {
            font-size: 120px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 20px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        h1 {
            font-size: 32px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        p {
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: #fff;
            color: #667eea;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .debug-info {
            margin-top: 30px;
            padding: 20px;
            background: rgba(0,0,0,0.2);
            border-radius: 10px;
            text-align: left;
            font-size: 14px;
        }
        .debug-info h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }
        .debug-info p {
            margin-bottom: 10px;
            font-size: 14px;
        }
        .trace {
            margin-top: 15px;
        }
        .trace pre {
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
            line-height: 1.5;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">{$statusCode}</div>
        <h1>{$title}</h1>
        <p>{$message}</p>
        <a href="/" class="btn">Go Home</a>
        {$debugInfo}
        <div class="footer">
            <strong>OxygenFramework 2.0</strong>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Render JSON error response for API requests
     * 
     * @param int $statusCode
     * @param string $message
     * @param \Throwable|null $exception
     * @return void
     */
    protected static function renderApiError($statusCode, $message, $exception = null)
    {
        $debug = OxygenConfig::get('errors.dev_mode', false);

        $response = [
            'success' => false,
            'error' => [
                'code' => $statusCode,
                'message' => $message,
            ],
            'timestamp' => date('c'),
        ];

        // Add debug information in development mode
        if ($debug && $exception) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => explode("\n", $exception->getTraceAsString()),
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    /**
     * Log error to file
     * 
     * @param int $statusCode
     * @param string $message
     * @param \Throwable|null $exception
     * @return void
     */
    protected static function logError($statusCode, $message, $exception = null)
    {
        try {
            $app = Application::getInstance();
            $logger = $app->getLogger();

            $context = [
                'status_code' => $statusCode,
                'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            ];

            if ($exception) {
                $context['file'] = $exception->getFile();
                $context['line'] = $exception->getLine();
            }

            // Log based on severity
            if ($statusCode >= 500) {
                $logger->error($message, $context);
            } elseif ($statusCode >= 400) {
                $logger->warning($message, $context);
            } else {
                $logger->info($message, $context);
            }
        } catch (\Exception $e) {
            // Silently fail if logging fails
            error_log("Error logging failed: " . $e->getMessage());
        }
    }

    /**
     * Register error handlers
     * 
     * @return void
     */
    public static function register()
    {
        // Set exception handler
        set_exception_handler([self::class, 'handleException']);

        // Set shutdown handler for fatal errors
        register_shutdown_function([self::class, 'handleShutdown']);

        // Set error handler for non-fatal errors
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }
}
