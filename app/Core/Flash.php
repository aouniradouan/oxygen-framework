<?php

namespace Oxygen\Core;

/**
 * Session - Flash message and session management
 * 
 * Handles flash messages for user feedback.
 * 
 * @package    Oxygen\Core
 */
class Flash
{
    /**
     * Set a flash message
     */
    public static function set($key, $message, $type = 'info')
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $_SESSION['flash'][$key] = [
            'message' => $message,
            'type' => $type
        ];
    }

    /**
     * Set a success flash message
     */
    public static function success($message)
    {
        self::set('message', $message, 'success');
    }

    /**
     * Set an error flash message
     */
    public static function error($message)
    {
        self::set('message', $message, 'error');
    }

    /**
     * Set a warning flash message
     */
    public static function warning($message)
    {
        self::set('message', $message, 'warning');
    }

    /**
     * Set an info flash message
     */
    public static function info($message)
    {
        self::set('message', $message, 'info');
    }

    /**
     * Get a flash message
     */
    public static function get($key = 'message')
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        if (isset($_SESSION['flash'][$key])) {
            $flash = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $flash;
        }

        return null;
    }

    /**
     * Check if flash message exists
     */
    public static function has($key = 'message')
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        return isset($_SESSION['flash'][$key]);
    }

    /**
     * Get all flash messages
     */
    public static function all()
    {
        if (!isset($_SESSION)) {
            session_start();
        }

        $messages = $_SESSION['flash'] ?? [];
        $_SESSION['flash'] = [];
        return $messages;
    }

    /**
     * Generate HTML for flash messages
     */
    public static function display()
    {
        $flash = self::get();

        if (!$flash) {
            return '';
        }

        $colors = [
            'success' => 'bg-green-50 text-green-800 border-green-200',
            'error' => 'bg-red-50 text-red-800 border-red-200',
            'warning' => 'bg-yellow-50 text-yellow-800 border-yellow-200',
            'info' => 'bg-blue-50 text-blue-800 border-blue-200',
        ];

        $color = $colors[$flash['type']] ?? $colors['info'];

        return '<div class="rounded-md border p-4 mb-4 ' . $color . '">' .
            htmlspecialchars($flash['message']) .
            '</div>';
    }
}
