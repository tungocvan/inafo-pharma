<?php

namespace Modules\WebsiteV2\Services;

class MarketingService
{
    public function __construct(private readonly BannerService $banners)
    {
    }

    public function getHeroSlides()
    {
        return $this->banners->getActiveByPosition('hero');
    }
}
