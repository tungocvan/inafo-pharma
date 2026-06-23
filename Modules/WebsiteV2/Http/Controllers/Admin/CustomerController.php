<?php

namespace Modules\WebsiteV2\Http\Controllers\Admin;

class CustomerController
{
    public function index()
    {
        return view('website-v2::admin.customers');
    }

    public function create()
    {
        return view('website-v2::admin.customer-form');
    }

    public function show(int $id)
    {
        return view('website-v2::admin.customer-detail', ['customerId' => $id]);
    }
}
