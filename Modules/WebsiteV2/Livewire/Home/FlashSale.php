<?php

namespace Modules\WebsiteV2\Livewire\Home;

use Livewire\Component;
use Modules\WebsiteV2\Services\FlashSaleService;

class FlashSale extends Component
{
    public bool $lazy = false;

    public function render()
    {
        return view('website-v2::livewire.home.flash-sale', [
            'flashSales' => app(FlashSaleService::class)->active(),
        ]);
    }
}
