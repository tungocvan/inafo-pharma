<?php

namespace Modules\WebsiteV2\Http\Controllers\Admin;

use Modules\WebsiteV2\Services\FooterService;

class FooterController
{
    public function index(FooterService $footer)
    {
        return view('website-v2::admin.footer', [
            'columns' => $footer->getColumnsForFrontend(),
            'socialLinks' => $footer->getSocialLinks(),
        ]);
    }
}
