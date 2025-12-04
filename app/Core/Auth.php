<?php

namespace Oxygen\Core;

use Oxygen\Models\User;
use Oxygen\Core\OxygenSession;

/**
 * Auth - Authentication Manager
 * 
 * Handles user authentication, login, logout, and session management.
 * 
 * @package    Oxygen\Core
 * @author     Redwan Aouni <aouniradouan@gmail.com>
 * @copyright  2024 - OxygenFramework
 * @version    2.0.0
 */
class Auth
{
    /**
     * Attempt to authenticate a user
     * 
     * @param string $email User email
     * @param string $password User password
     * @return bool
     */
    public static function attempt($email, $password)
    {
        $user = User::where('email', '=', $email);

        if ($user->isEmpty()) {
            return false;
        }

        $user = $user[0]; // Get first result

        // Verify password (assuming password_hash was used)
        if (password_verify($password, $user->password)) {
            self::login($user);
            return true;
        }

        return false;
    }

    /**
     * Log in a user
     * 
     * @param array|object $user User data
     * @return void
     */
    public static function login($user)
    {
        // Handle both array and object
        $userId = is_array($user) ? $user['id'] : $user->id;
        $userData = is_array($user) ? $user : $user->toArray();

        // Remove sensitive data
        unset($userData['password']);
        unset($userData['remember_token']);

        OxygenSession::put('user_id', $userId);
        OxygenSession::put('user', $userData);
        OxygenSession::regenerate(); // Regenerate session ID for security
    }

    /**
     * Log out the current user
     * 
     * @return void
     */
    public static function logout()
    {
        OxygenSession::forget('user_id');
        OxygenSession::forget('user');
        OxygenSession::regenerate();
    }

    /**
     * Check if a user is authenticated
     * 
     * @return bool
     */
    public static function check()
    {
        return OxygenSession::has('user_id');
    }

    /**
     * Get the currently authenticated user
     * 
     * @return array|null
     */
    public static function user()
    {
        if (self::check()) {
            // Fetch fresh user data from database
            $user = User::find(OxygenSession::get('user_id'));
            return $user ? $user->toArray() : null;
        }
        return null;
    }

    /**
     * Get the current user's ID
     * 
     * @return int|null
     */
    public static function id()
    {
        return OxygenSession::get('user_id');
    }

    /**
     * Register a new user
     * 
     * @param array $data User data (name, email, password)
     * @param int $roleId Role ID (default: 2 for User role)
     * @return array|false User data or false on failure
     */
    public static function register($data, $roleId = 2)
    {
        // Check if email already exists
        $existing = User::where('email', '=', $data['email']);
        if ($existing->count() > 0) {
            return false;
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        // Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'role_id' => $roleId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($user) {
            // Login with the user object directly
            self::login($user);
            return $user->toArray();
        }

        return false;
    }

    /**
     * Check if current user has a specific role
     * 
     * @param string $roleSlug
     * @return bool
     */
    public static function hasRole($roleSlug)
    {
        if (!self::check()) {
            return false;
        }

        // Get fresh user model from database to ensure relationships work
        $userId = self::id();
        $userModel = User::find($userId);

        if (!$userModel) {
            return false;
        }

        return $userModel->hasRole($roleSlug);
    }

    /**
     * Check if current user has a specific permission
     * 
     * @param string $permissionSlug
     * @return bool
     */
    public static function can($permissionSlug)
    {
        if (!self::check()) {
            return false;
        }

        // Get fresh user model from database to ensure relationships work
        $userId = self::id();
        $userModel = User::find($userId);

        if (!$userModel) {
            return false;
        }

        return $userModel->can($permissionSlug);
    }

    /**
     * Check if current user is admin
     * 
     * @return bool
     */
    public static function isAdmin()
    {
        return self::hasRole('admin');
    }

    /**
     * Get the current authenticated user model
     * 
     * @return User|null
     */
    public static function userModel()
    {
        if (!self::check()) {
            return null;
        }

        return User::find(self::id());
    }
}
