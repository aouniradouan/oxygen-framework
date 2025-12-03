<?php

namespace Oxygen\Controllers\Auth;

use Oxygen\Core\Controller;
use Oxygen\Core\Support\Auth;

class AuthController extends Controller
{
    public function logout()
    {
        Auth::logout();
        return $this->redirect('/');
    }
}