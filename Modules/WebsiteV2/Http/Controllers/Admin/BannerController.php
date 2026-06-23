<?php

namespace Modules\WebsiteV2\Http\Controllers\Admin;

use Modules\WebsiteV2\Services\BannerService;

class BannerController
{
    public function index(BannerService $banners)
    {
        return view('website-v2::admin.banners', ['banners' => $banners->getAll()]);
    }
}
