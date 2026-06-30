<?php

namespace Modules\Inafo\Livewire\Auth;

use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Modules\Inafo\Services\AuthService;

class AuthPage extends Component
{
    public string $mode = 'login';

    public string $phone = '';

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $province = '';

    public bool $agree_terms = false;

    public bool $remember = false;

    protected AuthService $service;

    public function boot(AuthService $service): void
    {
        $this->service = $service;
    }

    public function mount(string $mode = 'login'): void
    {
        $this->mode = in_array($mode, ['login', 'register'], true) ? $mode : 'login';
    }

    public function showLogin(): void
    {
        $this->mode = 'login';
        $this->resetValidation();
    }

    public function showRegister(): void
    {
        $this->mode = 'register';
        $this->resetValidation();
    }

    public function login()
    {
        $this->phone = $this->service->normalizePhone($this->phone);

        $this->validate($this->loginRules(), $this->messages());

        try {
            $this->service->login($this->phone, $this->password, $this->remember);
        } catch (ValidationException $exception) {
            throw $exception;
        }

        return redirect()->intended(route(config('inafo.inafo.route_name', 'inafo') . '.home'));
    }

    public function register()
    {
        $this->phone = $this->service->normalizePhone($this->phone);

        $this->validate($this->registerRules(), $this->messages());

        $this->service->register([
            'phone' => $this->phone,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'province' => $this->province,
        ]);

        return redirect()->route(config('inafo.inafo.route_name', 'inafo') . '.home');
    }

    public function render()
    {
        return view('inafo::livewire.auth.auth-page', [
            'provinces' => $this->provinces(),
        ]);
    }

    private function loginRules(): array
    {
        return [
            'phone' => ['required', 'string', 'min:8', 'max:20'],
            'password' => ['required', 'string'],
        ];
    }

    private function registerRules(): array
    {
        $phoneRule = ['required', 'string', 'min:8', 'max:20'];

        if (Schema::hasColumn('users', 'phone')) {
            $phoneRule[] = Rule::unique('users', 'phone');
        }

        return [
            'phone' => $phoneRule,
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'province' => ['required', 'string', 'max:120'],
            'agree_terms' => ['accepted'],
        ];
    }

    private function messages(): array
    {
        return [
            'phone.required' => 'Vui long nhap so dien thoai.',
            'phone.unique' => 'So dien thoai nay da duoc dang ky.',
            'password.required' => 'Vui long nhap mat khau.',
            'password.min' => 'Mat khau toi thieu 6 ky tu.',
            'password.confirmed' => 'Mat khau nhap lai khong khop.',
            'province.required' => 'Vui long chon tinh/thanh pho.',
            'agree_terms.accepted' => 'Ban can dong y dieu khoan su dung.',
        ];
    }

    private function provinces(): array
    {
        return [
            'Ho Chi Minh',
            'Ha Noi',
            'Da Nang',
            'Can Tho',
            'Hai Phong',
            'Binh Duong',
            'Dong Nai',
            'Khanh Hoa',
            'Lam Dong',
            'An Giang',
        ];
    }
}
