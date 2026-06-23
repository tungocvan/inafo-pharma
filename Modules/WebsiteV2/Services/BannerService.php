<?php

namespace Modules\WebsiteV2\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\WebsiteV2\Models\Banner;

class BannerService
{
    public function getAll(): Collection
    {
        return Banner::query()->orderBy('position')->orderBy('order')->get();
    }

    public function getActiveByPosition(string $position): Collection
    {
        return Banner::query()->active()->where('position', $position)->orderBy('order')->get();
    }
}
