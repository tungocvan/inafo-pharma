<?php

namespace Modules\WebsiteV2\Http\Controllers;

use Illuminate\Http\Request;

class PostController
{
    public function index(Request $request)
    {
        return view('website-v2::pages.blog.index', [
            'categorySlug' => $request->query('category'),
        ]);
    }

    public function detail(string $slug)
    {
        return view('website-v2::pages.blog.detail', compact('slug'));
    }
}
