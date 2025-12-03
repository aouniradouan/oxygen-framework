<?php

namespace Oxygen\Controllers\Auth;

use Oxygen\Core\Controller;
use Oxygen\Core\Http\Request;
use Oxygen\Core\Support\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return $this->view('auth/login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        
        if (Auth::attempt($credentials)) {
            return $this->redirect('/dashboard');
        }
        
        return $this->back()->withErrors(['email' => 'Invalid credentials']);
    }
}