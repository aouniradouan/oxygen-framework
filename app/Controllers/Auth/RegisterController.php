<?php

namespace Oxygen\Controllers\Auth;

use Oxygen\Controllers\Controller;
use Oxygen\Core\Http\Request;
use Oxygen\Models\User;
use Oxygen\Core\Support\Hash;
use Oxygen\Core\Support\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return $this->view('auth/register');
    }

    public function register($request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        Auth::login($user);

        return $this->redirect('/dashboard');
    }
}