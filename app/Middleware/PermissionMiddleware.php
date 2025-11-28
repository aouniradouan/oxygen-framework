<?php

namespace Oxygen\Middleware;

use Oxygen\Core\Auth;
use Oxygen\Core\Flash;

/**
 * PermissionMiddleware
 * 
 * Checks if the authenticated user has the required permission
 */
class PermissionMiddleware
{
    protected $auth;

    public function __construct()
    {
        $this->auth = app()->make(Auth::class);
    }

    /**
     * Handle the middleware
     * 
     * @param string $permission Required permission slug
     * @return bool
     */
    public function handle($permission)
    {
        // Check if user is authenticated
        if (!$this->auth->check()) {
            Flash::error('Please login to access this page.');
            redirect('/login');
            return false;
        }

        // Check if user has the required permission
        if (!$this->auth->can($permission)) {
            Flash::error('You do not have permission to perform this action.');
            redirect('/dashboard');
            return false;
        }

        return true;
    }
}
