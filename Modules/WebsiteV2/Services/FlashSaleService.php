<?php

namespace Modules\WebsiteV2\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\WebsiteV2\Models\FlashSale;

class FlashSaleService
{
    public function getAll(): Collection
    {
        return FlashSale::query()->with('items')->latest()->get();
    }

    public function active(): Collection
    {
        return FlashSale::query()
            ->active()
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now())
            ->with('items')
            ->get();
    }
}
