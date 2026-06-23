<?php

namespace Modules\WebsiteV2\Livewire\Home;

use Livewire\Component;
use Modules\WebsiteV2\Services\CategoryService;

class CategoryHighlight extends Component
{
    public array $categoryIds = [];

    public function render()
    {
        return view('website-v2::livewire.home.category-highlight', [
            'categories' => app(CategoryService::class)->getHomeCategories(),
        ]);
    }
}
