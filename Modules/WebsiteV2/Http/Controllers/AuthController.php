<?php

namespace Modules\WebsiteV2\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AuthController
{
    public function login()
    {
        return view('website-v2::auth.login');
    }

    public function register()
    {
        return view('website-v2::auth.register');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        return redirect()->route('website-v2.home');
    }
}
