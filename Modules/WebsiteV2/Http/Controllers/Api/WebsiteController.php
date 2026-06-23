<?php

namespace Modules\WebsiteV2\Http\Controllers\Api;

use Modules\WebsiteV2\Services\SettingsService;

class WebsiteController
{
    public function index(SettingsService $settings): array
    {
        return [
            'module' => 'WebsiteV2',
            'brand' => $settings->get('header.brand_name', 'INAFO Pharma V2'),
        ];
    }
}
