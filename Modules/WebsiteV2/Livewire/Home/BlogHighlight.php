<?php

namespace Modules\WebsiteV2\Livewire\Home;

use Livewire\Component;
use Modules\WebsiteV2\Services\ContentService;

class BlogHighlight extends Component
{
    public bool $lazy = false;

    public function render()
    {
        return view('website-v2::livewire.home.blog-highlight', [
            'posts' => app(ContentService::class)->latestPosts(),
        ]);
    }
}
