<?php

namespace Modules\WebsiteV2\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class RegisterForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    protected array $rules = [
        'name' => 'required|min:3|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6|confirmed',
    ];

    protected array $messages = [
        'email.unique' => 'This email has already been registered.',
        'password.confirmed' => 'The password confirmation does not match.',
        'password.min' => 'The password must be at least 6 characters.',
    ];

    public function register()
    {
        $this->validate();

        $user = User::query()->create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        Auth::login($user);

        return redirect()->route('website-v2.home');
    }

    public function render()
    {
        return view('website-v2::livewire.auth.register-form');
    }
}
