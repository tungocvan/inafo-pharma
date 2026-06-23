<?php

namespace Modules\WebsiteV2\Http\Controllers;

use Modules\WebsiteV2\Services\CartService;

class CartController
{
    public function index(CartService $cart)
    {
        return view('website-v2::cart.index');
    }
}
