<?php

namespace Modules\WebsiteV2\Livewire\Home;

use Livewire\Component;
use Modules\WebsiteV2\Services\ProductService;

class NewArrivals extends Component
{
    public bool $lazy = false;

    public function render()
    {
        return view('website-v2::livewire.home.new-arrivals', [
            'products' => app(ProductService::class)->newArrivals(),
        ]);
    }
}
