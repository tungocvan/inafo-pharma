<?php

namespace Modules\WebsiteV2\Http\Controllers\Admin;

use Modules\WebsiteV2\Services\FlashSaleService;

class FlashSaleController
{
    public function index(FlashSaleService $flashSales)
    {
        return view('website-v2::admin.flash-sales', ['flashSales' => $flashSales->getAll()]);
    }
}
