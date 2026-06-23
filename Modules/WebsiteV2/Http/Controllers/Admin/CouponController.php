<?php

namespace Modules\WebsiteV2\Http\Controllers\Admin;

class CouponController
{
    public function index()
    {
        return view('website-v2::admin.coupons');
    }

    public function create()
    {
        return view('website-v2::admin.coupon-form', ['couponId' => null]);
    }

    public function edit(int $id)
    {
        return view('website-v2::admin.coupon-form', ['couponId' => $id]);
    }
}
