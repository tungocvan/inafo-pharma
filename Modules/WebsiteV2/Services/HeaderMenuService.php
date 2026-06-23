<?php

namespace Modules\WebsiteV2\Services;

class HeaderMenuService
{
    public function getMenuTreeByLocation(string $location): array
    {
        return [
            ['label' => 'Home', 'url' => route('website-v2.home', absolute: false)],
            ['label' => 'Products', 'url' => route('website-v2.product.list', absolute: false)],
            ['label' => 'Blog', 'url' => route('website-v2.blog', absolute: false)],
        ];
    }
}
