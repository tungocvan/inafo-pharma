<?php

namespace Modules\WebsiteV2\Livewire\Home;

use Livewire\Component;
use Modules\WebsiteV2\Services\BannerService;

class PromoBanner extends Component
{
    public bool $lazy = false;

    public function render()
    {
        return view('website-v2::livewire.home.promo-banner', [
            'banners' => app(BannerService::class)->getActiveByPosition('promo'),
        ]);
    }
}
