<?php

namespace Oxygen\Controllers;

use Oxygen\Core\Controller;
use Oxygen\Core\Auth;
use Oxygen\Core\Flash;
use Oxygen\Core\Validation\OxygenValidator;

/**
 * AuthController
 * 
 * Handles all authentication operations: login, register, logout, password reset
 */
class AuthController extends Controller
{
    protected $auth;

    public function __construct()
    {
        parent::__construct();
        $this->auth = $this->app->make(Auth::class);
    }

    /**
     * Show login form
     */
    public function showLoginForm()
    {
        // Redirect if already logged in
        if ($this->auth->check()) {
            return redirect('/dashboard');
        }

        echo $this->view->render('auth/login.twig.html');
    }

    /**
     * Handle login request
     */
    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validate
        $validator = OxygenValidator::make([
            'email' => $email,
            'password' => $password
        ], [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            Flash::error('Please provide valid credentials.');
            return redirect('/login');
        }

        // Attempt login
        if ($this->auth->attempt($email, $password)) {
            Flash::success('Welcome back!');
            return redirect('/dashboard');
        }

        Flash::error('Invalid email or password.');
        return redirect('/login');
    }

    /**
     * Show registration form
     */
    public function showRegisterForm()
    {
        // Redirect if already logged in
        if ($this->auth->check()) {
            return redirect('/dashboard');
        }

        echo $this->view->render('auth/register.twig.html');
    }

    /**
     * Handle registration request
     */
    public function register()
    {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        // Validate
        $validator = OxygenValidator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation
        ], [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password'
        ]);

        if ($validator->fails()) {
            Flash::error('Please check your input and try again.');
            return redirect('/register');
        }

        // Register user
        $user = $this->auth->register([
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);

        if ($user) {
            Flash::success('Account created successfully! Welcome to OxygenFramework.');
            return redirect('/dashboard');
        }

        Flash::error('Email already exists. Please use a different email.');
        return redirect('/register');
    }

    /**
     * Handle logout request
     */
    public function logout()
    {
        $this->auth->logout();
        Flash::success('You have been logged out successfully.');
        return redirect('/login');
    }

    /**
     * Show password reset request form
     */
    public function showResetRequestForm()
    {
        echo $this->view->render('auth/reset-request.twig.html');
    }

    /**
     * Show password reset form
     */
    public function showResetForm()
    {
        $token = $_GET['token'] ?? '';

        echo $this->view->render('auth/reset-password.twig.html', [
            'token' => $token
        ]);
    }

    /**
     * Handle password reset request
     */
    public function sendResetLink()
    {
        $email = $_POST['email'] ?? '';

        // Validate email
        $validator = OxygenValidator::make([
            'email' => $email
        ], [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            Flash::error('Please provide a valid email address.');
            return redirect('/password/reset');
        }

        // TODO: Implement email sending logic
        Flash::success('If an account exists with this email, you will receive a password reset link.');
        return redirect('/login');
    }
}
