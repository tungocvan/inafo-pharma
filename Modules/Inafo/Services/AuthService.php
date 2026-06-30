<?php

namespace Modules\Inafo\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(string $phone, string $password, bool $remember = false): void
    {
        $phone = $this->normalizePhone($phone);
        $field = Schema::hasColumn('users', 'phone') ? 'phone' : 'email';

        if (! Auth::attempt([$field => $phone, 'password' => $password], $remember)) {
            throw ValidationException::withMessages([
                'phone' => 'Thong tin dang nhap khong chinh xac.',
            ]);
        }

        session()->regenerate();

        $user = Auth::user();

        if ($user && Schema::hasColumn('users', 'last_login_at')) {
            $user->forceFill(['last_login_at' => now()])->save();
        }
    }

    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $phone = $this->normalizePhone((string) $data['phone']);
            $payload = [
                'name' => $data['name'] ?: $phone,
                'password' => Hash::make((string) $data['password']),
            ];

            if (Schema::hasColumn('users', 'phone')) {
                $payload['phone'] = $phone;
            }

            if (Schema::hasColumn('users', 'email')) {
                $payload['email'] = $data['email'] ?? $phone . '@inafo.local';
            }

            if (Schema::hasColumn('users', 'is_active')) {
                $payload['is_active'] = true;
            }

            $user = User::query()->create($payload);
            Auth::login($user);
            session()->regenerate();

            return $user;
        });
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    public function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?: $phone;
    }
}
