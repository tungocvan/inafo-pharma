<?php

namespace Modules\Inafo\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Category\Models\Category;
use Modules\Inafo\Models\FooterColumn;
use Modules\Inafo\Models\HomeBanner;
use Modules\Inafo\Models\HomeBenefit;
use Modules\Inafo\Models\HomeShelf;
use Modules\Inafo\Models\PartnerLogo;
use Modules\Post\Models\Post;
use Modules\Product\Models\Product;

class HomePageService
{
    public function getHomePayload(): array
    {
        $cacheKey = $this->cacheKey('home_payload_' . (Auth::id() ?: 'guest'));
        $ttl = (int) config('inafo.inafo.home_cache_ttl', 300);

        return Cache::remember($cacheKey, $ttl, function () {
            return [
                'brand' => [
                    'name' => config('inafo.inafo.brand_name', 'INAFO Pharma'),
                    'home_url' => $this->url('/'),
                    'search_url' => $this->url('/search'),
                ],
                'header' => $this->getHeaderState(),
                'navigation' => $this->getNavigation(),
                'hero' => $this->getHero(),
                'benefits' => $this->getBenefits(),
                'shelves' => $this->getShelves(),
                'categories' => $this->getCategories(),
                'posts' => $this->getPosts(),
                'partners' => $this->getPartners(),
                'footer' => $this->getFooter(),
            ];
        });
    }

    private function getHeaderState(): array
    {
        $user = Auth::user();

        return [
            'is_authenticated' => $user !== null,
            'display_name' => $user?->phone ?? $user?->email ?? $user?->name,
            'business_profile_status' => $user ? 'missing' : 'guest',
            'wishlist_count' => 0,
            'notification_count' => 0,
            'cart_count' => 0,
        ];
    }

    private function getNavigation(): array
    {
        return [
            ['label' => 'Trang Chu', 'url' => $this->url('/'), 'active' => true],
            ['label' => 'San Pham', 'url' => $this->url('/products'), 'active' => false],
            ['label' => 'Dat Nhanh', 'url' => $this->url('/quick-order'), 'active' => false],
            ['label' => 'Khuyen Mai Dac Biet', 'url' => $this->url('/promotions'), 'active' => false],
            ['label' => 'San Pham Ban Chay', 'url' => $this->url('/best-sellers'), 'active' => false],
        ];
    }

    private function getHero(): array
    {
        if (! Schema::hasTable('inafo_home_banners')) {
            return ['main' => [], 'side' => []];
        }

        $banners = HomeBanner::query()
            ->active()
            ->orderBy('position')
            ->get();

        return [
            'primary' => $this->mapBanner($banners->where('placement', 'hero_main')->first()),
            'side' => $this->mapBanners($banners->where('placement', 'hero_side')),
        ];
    }

    private function getBenefits(): array
    {
        if (! Schema::hasTable('inafo_home_benefits')) {
            return [];
        }

        return HomeBenefit::query()
            ->active()
            ->orderBy('position')
            ->limit(5)
            ->get()
            ->map(fn(HomeBenefit $benefit) => [
                'title' => $benefit->title,
                'description' => $benefit->description,
                'icon' => $benefit->icon ?: 'check',
            ])
            ->values()
            ->all();
    }

    private function getShelves(): array
    {
        if (! Schema::hasTable('inafo_home_shelves')) {
            return [];
        }

        return HomeShelf::query()
            ->active()
            ->with(['banner', 'shelfProducts'])
            ->orderBy('position')
            ->get()
            ->map(function (HomeShelf $shelf) {
                return [
                    'title' => $shelf->title,
                    'slug' => $shelf->slug,
                    'view_more_url' => $shelf->view_more_url ?: $this->url('/products?shelf=' . $shelf->slug),
                    'banner' => $shelf->banner ? $this->mapBanner($shelf->banner) : null,
                    'products' => $this->getShelfProducts($shelf),
                ];
            })
            ->values()
            ->all();
    }

    private function getShelfProducts(HomeShelf $shelf): array
    {
        if (! Schema::hasTable('wp_products')) {
            return [];
        }

        $limit = max(1, min((int) $shelf->product_limit, 24));
        $query = Product::query()->active();

        if ($shelf->type === 'manual' && $shelf->shelfProducts->isNotEmpty()) {
            $ids = $shelf->shelfProducts->pluck('product_id')->values()->all();

            $products = $query
                ->whereIn('id', $ids)
                ->get()
                ->sortBy(fn(Product $product) => array_search($product->id, $ids, true))
                ->take($limit);

            return $this->mapProducts($products);
        }

        if ($shelf->type === 'featured') {
            $query->where('is_featured', true)->latest();
        } elseif ($shelf->type === 'best_seller') {
            $query->orderByDesc('sold_count')->latest();
        } else {
            $query->latest();
        }

        return $this->mapProducts($query->limit($limit)->get());
    }

    private function getCategories(): array
    {
        if (! Schema::hasTable('categories')) {
            return [];
        }

        return Category::query()
            ->active()
            ->roots()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(12)
            ->get()
            ->map(fn(Category $category) => [
                'name' => $category->name,
                'url' => $category->url ?: $this->url('/products?category=' . $category->slug),
                'icon' => $category->icon,
            ])
            ->values()
            ->all();
    }

    private function getPosts(): array
    {
        if (! Schema::hasTable('wp_posts')) {
            return [];
        }

        return Post::query()
            ->where('status', 'published')
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map(fn(Post $post) => [
                'title' => $post->name,
                'summary' => $post->summary,
                'url' => $this->url('/blog/' . $post->slug),
                'thumbnail' => $post->thumbnail,
            ])
            ->values()
            ->all();
    }

    private function getPartners(): array
    {
        if (! Schema::hasTable('inafo_partner_logos')) {
            return [];
        }

        return PartnerLogo::query()
            ->active()
            ->orderBy('position')
            ->limit(12)
            ->get()
            ->map(fn(PartnerLogo $partner) => [
                'name' => $partner->name,
                'logo_url' => $partner->logo_url,
                'target_url' => $partner->target_url,
            ])
            ->values()
            ->all();
    }

    private function getFooter(): array
    {
        if (! Schema::hasTable('inafo_footer_columns')) {
            return ['columns' => []];
        }

        return [
            'columns' => FooterColumn::query()
                ->active()
                ->with(['links' => fn($query) => $query->active()->orderBy('position')])
                ->orderBy('position')
                ->get()
                ->map(fn(FooterColumn $column) => [
                    'title' => $column->title,
                    'links' => $column->links->map(fn($link) => [
                        'label' => $link->label,
                        'url' => $link->url,
                    ])->values()->all(),
                ])
                ->values()
                ->all(),
        ];
    }

    private function mapBanners(Collection $banners): array
    {
        return $banners->map(fn(HomeBanner $banner) => $this->mapBanner($banner))->values()->all();
    }

    private function mapBanner(?HomeBanner $banner): ?array
    {
        if (! $banner) {
            return null;
        }

        return [
            'title' => $banner->title,
            'subtitle' => $banner->subtitle,
            'image_desktop_url' => $banner->image_desktop_url,
            'image_mobile_url' => $banner->image_mobile_url,
            'target_url' => $banner->target_url ?: '#',
            'button_label' => $banner->button_label,
        ];
    }

    private function mapProducts(Collection $products): array
    {
        return $products->map(fn(Product $product) => [
            'id' => $product->id,
            'name' => $product->title,
            'slug' => $product->slug,
            'image_url' => $this->productImageUrl($product),
            'url' => $this->url('/product/' . $product->slug),
            'price_visibility_state' => $this->priceVisibilityState(),
            'locked_price_label' => $this->lockedPriceLabel(),
            'stock_status' => ((int) $product->quantity > 0) ? 'in_stock' : 'out_of_stock',
            'is_wishlisted' => false,
        ])->values()->all();
    }

    private function productImageUrl(Product $product): string
    {
        $image = (string) ($product->image ?? '');

        if ($image !== '' && Str::startsWith($image, ['http://', 'https://'])) {
            return $image;
        }

        if ($image !== '') {
            return asset('storage/' . ltrim($image, '/'));
        }

        return asset('images/placeholder.jpg');
    }

    private function priceVisibilityState(): string
    {
        if (! Auth::check()) {
            return 'guest';
        }

        return 'missing_profile';
    }

    private function lockedPriceLabel(): string
    {
        return Auth::check()
            ? 'Xac minh ho so KD de xem gia'
            : 'Dang nhap de xem gia';
    }

    private function url(string $path): string
    {
        $prefix = trim((string) config('inafo.inafo.route_prefix', 'inafo'), '/');
        $path = '/' . ltrim($path, '/');

        if ($prefix === '') {
            return $path;
        }

        return '/' . $prefix . ($path === '/' ? '' : $path);
    }

    private function cacheKey(string $key): string
    {
        return config('inafo.inafo.cache_prefix', 'inafo') . ':' . $key;
    }
}
