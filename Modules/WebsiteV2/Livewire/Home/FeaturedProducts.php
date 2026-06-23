<?php

namespace Modules\WebsiteV2\Livewire\Home;

use Livewire\Component;
use Modules\WebsiteV2\Services\ProductService;

class FeaturedProducts extends Component
{
    public bool $lazy = false;

    public array $productIds = [];

    public function render()
    {
        return view('website-v2::livewire.home.featured-products', [
            'products' => app(ProductService::class)->featured($this->productIds),
        ]);
    }
}
