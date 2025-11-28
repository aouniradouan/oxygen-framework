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
    public function attempt($email, $password)
    {
        $user = User::where('email', '=', $email);

        if ($user->isEmpty()) {
            return false;
        }

        $user = $user[0]; // Get first result

        // Verify password (assuming password_hash was used)
        if (password_verify($password, $user->password)) {
            $this->login($user);
            return true;
        }

        return false;
    }

    /**
     * Log in a user
     * 
     * @param array $user User data
     * @return void
     */
    public function login($user)
    {
        // Handle both array and object
        $userId = is_array($user) ? $user['id'] : $user->id;
        $userData = is_array($user) ? $user : $user->toArray();

        OxygenSession::put('user_id', $userId);
        OxygenSession::put('user', $userData);
        OxygenSession::regenerate(); // Regenerate session ID for security
    }

    /**
     * Log out the current user
     * 
     * @return void
     */
    public function logout()
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
    public function check()
    {
        return OxygenSession::has('user_id');
    }

    /**
     * Get the currently authenticated user
     * 
     * @return array|null
     */
    public function user()
    {
        if ($this->check()) {
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
    public function id()
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
    public function register($data, $roleId = 2)
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
            $this->login($user);
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
    public function hasRole($roleSlug)
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        $userModel = new User();
        foreach ($user as $key => $value) {
            $userModel->$key = $value;
        }

        return $userModel->hasRole($roleSlug);
    }

    /**
     * Check if current user has a specific permission
     * 
     * @param string $permissionSlug
     * @return bool
     */
    public function can($permissionSlug)
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        $userModel = new User();
        foreach ($user as $key => $value) {
            $userModel->$key = $value;
        }

        return $userModel->can($permissionSlug);
    }

    /**
     * Check if current user is admin
     * 
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }
}
