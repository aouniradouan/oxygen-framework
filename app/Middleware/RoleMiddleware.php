<?php

namespace Oxygen\Middleware;

use Oxygen\Core\Auth;
use Oxygen\Core\Flash;

/**
 * RoleMiddleware
 * 
 * Checks if the authenticated user has the required role
 */
class RoleMiddleware
{
    protected $auth;

    public function __construct()
    {
        $this->auth = app()->make(Auth::class);
    }

    /**
     * Handle the middleware
     * 
     * @param string $role Required role slug
     * @return bool
     */
    public function handle($role)
    {
        // Check if user is authenticated
        if (!$this->auth->check()) {
            Flash::error('Please login to access this page.');
            redirect('/login');
            return false;
        }

        // Check if user has the required role
        if (!$this->auth->hasRole($role)) {
            Flash::error('You do not have permission to access this page.');
            redirect('/dashboard');
            return false;
        }

        return true;
    }
}
