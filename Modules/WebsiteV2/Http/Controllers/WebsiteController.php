<?php

namespace Modules\WebsiteV2\Http\Controllers;

use Modules\WebsiteV2\Services\SettingsService;

class WebsiteController
{
    public function home(SettingsService $settings)
    {
        return view('website-v2::pages.home.index', [
            'siteName' => $settings->get('site_name', 'INAFO Pharma V2'),
        ]);
    }

    public function help()
    {
        return view('website-v2::pages.help.index');
    }
}
