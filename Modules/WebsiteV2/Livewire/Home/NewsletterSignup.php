<?php

namespace Modules\WebsiteV2\Livewire\Home;

use Livewire\Component;

class NewsletterSignup extends Component
{
    public bool $lazy = false;

    public function render()
    {
        return view('website-v2::livewire.home.newsletter-signup');
    }
}
