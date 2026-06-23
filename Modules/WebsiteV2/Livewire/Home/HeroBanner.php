<?php

namespace Modules\WebsiteV2\Livewire\Home;

use Livewire\Component;
use Modules\WebsiteV2\Services\BannerService;

class HeroBanner extends Component
{
    public function render()
    {
        return view('website-v2::livewire.home.hero-banner', [
            'slides' => app(BannerService::class)->getActiveByPosition('hero'),
        ]);
    }
}
