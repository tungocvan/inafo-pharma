<?php

namespace Modules\WebsiteV2\Http\Controllers;

class ProductController
{
    public function index()
    {
        return view('website-v2::products.index');
    }

    public function show(string $slug)
    {
        return view('website-v2::products.show', compact('slug'));
    }
}
