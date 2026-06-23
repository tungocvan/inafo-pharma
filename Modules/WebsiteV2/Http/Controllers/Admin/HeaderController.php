<?php

namespace Modules\WebsiteV2\Http\Controllers\Admin;

use Modules\WebsiteV2\Services\HeaderMenuService;

class HeaderController
{
    public function index(HeaderMenuService $menus)
    {
        return view('website-v2::admin.header', ['menus' => $menus->getMenuTreeByLocation('primary')]);
    }
}
