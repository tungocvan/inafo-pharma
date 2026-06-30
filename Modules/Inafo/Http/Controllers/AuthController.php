<?php

namespace Modules\Inafo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Inafo\Services\AuthService;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('inafo::pages.auth.index', [
            'mode' => 'login',
            'siteName' => config('inafo.inafo.brand_name', 'INAFO Pharma'),
        ]);
    }

    public function register(): View
    {
        return view('inafo::pages.auth.index', [
            'mode' => 'register',
            'siteName' => config('inafo.inafo.brand_name', 'INAFO Pharma'),
        ]);
    }

    public function logout(AuthService $service): RedirectResponse
    {
        $service->logout();

        return redirect()->route(config('inafo.inafo.route_name', 'inafo') . '.home');
    }
}
