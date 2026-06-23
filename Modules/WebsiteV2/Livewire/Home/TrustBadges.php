<?php

namespace Modules\WebsiteV2\Livewire\Home;

use Livewire\Component;

class TrustBadges extends Component
{
    public bool $lazy = false;

    public array $badges = [];

    public function render()
    {
        return view('website-v2::livewire.home.trust-badges');
    }
}
