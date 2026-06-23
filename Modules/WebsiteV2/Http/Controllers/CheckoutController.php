<?php

namespace Modules\WebsiteV2\Http\Controllers;

use Modules\WebsiteV2\Services\CheckoutService;
use Modules\WebsiteV2\Models\Order;

class CheckoutController
{
    public function index(CheckoutService $checkout)
    {
        return view('website-v2::checkout.index');
    }

    public function success()
    {
        $code = request('code', session('order_code'));

        return view('website-v2::checkout.success', [
            'code' => $code,
            'order' => $code ? Order::query()->where('order_code', $code)->first() : null,
        ]);
    }

    public function momoCallback()
    {
        return redirect()->route('website-v2.checkout.success', request()->query());
    }
}
