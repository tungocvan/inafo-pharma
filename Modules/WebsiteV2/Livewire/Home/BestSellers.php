<?php

namespace Modules\WebsiteV2\Livewire\Home;

use Livewire\Component;
use Modules\WebsiteV2\Services\ProductService;

class BestSellers extends Component
{
    public bool $lazy = false;

    public function render()
    {
        return view('website-v2::livewire.home.best-sellers', [
            'products' => app(ProductService::class)->bestSellers(),
        ]);
    }
}
