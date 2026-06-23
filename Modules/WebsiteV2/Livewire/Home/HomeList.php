<?php

namespace Modules\WebsiteV2\Livewire\Home;

use Livewire\Component;
use Modules\WebsiteV2\Services\SettingsService;

class HomeList extends Component
{
    public array $settings = [];

    public function mount(SettingsService $settings): void
    {
        $this->settings = $settings->getHomeSettings();
    }

    public function getVisibilityClass(string $key): string
    {
        return match ($this->settings[$key] ?? 'all') {
            'desktop' => 'hidden md:block',
            'mobile' => 'block md:hidden',
            'none', 'hidden' => 'hidden',
            default => 'block',
        };
    }

    public function render()
    {
        $banners = app(\Modules\WebsiteV2\Services\BannerService::class);
        $categories = app(\Modules\WebsiteV2\Services\CategoryService::class);
        $flashSales = app(\Modules\WebsiteV2\Services\FlashSaleService::class);
        $products = app(\Modules\WebsiteV2\Services\ProductService::class);
        $content = app(\Modules\WebsiteV2\Services\ContentService::class);

        return view('website-v2::livewire.home.home-list', [
            'heroSlides' => $banners->getActiveByPosition('hero'),
            'promoBanners' => $banners->getActiveByPosition('promo'),
            'homeCategories' => $categories->getHomeCategories(),
            'activeFlashSales' => $flashSales->active(),
            'featuredProducts' => $products->featured($this->settings['featured_ids'] ?? []),
            'newArrivals' => $products->newArrivals(),
            'bestSellers' => $products->bestSellers(),
            'latestPosts' => $content->latestPosts(),
            'trustBadges' => $this->settings['trust_badges'] ?? [],
        ]);
    }
}
