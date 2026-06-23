<?php

namespace Modules\WebsiteV2\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginForm extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            return redirect()->intended(route('website-v2.home'));
        }

        $this->addError('email', 'The provided credentials are incorrect.');
    }

    public function render()
    {
        return view('website-v2::livewire.auth.login-form');
    }
}
