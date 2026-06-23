<?php

namespace Modules\WebsiteV2\Http\Controllers;

use Modules\WebsiteV2\Services\Account\ProfileService;

class AccountController
{
    public function index()
    {
        return view('website-v2::account.dashboard');
    }

    public function profile(ProfileService $profiles)
    {
        return view('website-v2::account.profile', ['profile' => $profiles->current()]);
    }

    public function affiliate()
    {
        return view('website-v2::account.affiliate');
    }

    public function orders()
    {
        return view('website-v2::account.orders.index');
    }

    public function orderDetail(string $code)
    {
        return view('website-v2::account.orders.show', ['code' => $code]);
    }

    public function wishlist()
    {
        return view('website-v2::account.wishlist');
    }
}
