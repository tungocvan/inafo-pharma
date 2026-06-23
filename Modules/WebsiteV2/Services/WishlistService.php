<?php

namespace Modules\WebsiteV2\Services;

use Modules\WebsiteV2\Models\Wishlist;

class WishlistService
{
    public function count(?int $userId = null): int
    {
        return Wishlist::query()->where('user_id', $userId ?? auth()->id())->count();
    }
}
