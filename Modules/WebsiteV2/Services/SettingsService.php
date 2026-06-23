<?php

namespace Modules\WebsiteV2\Services;

use Illuminate\Support\Facades\Cache;
use Modules\WebsiteV2\Models\Setting;

class SettingsService
{
    public function getHomeSettings(): array
    {
        return [
            'show_hero' => $this->get('home_show_hero', 'all'),
            'show_categories' => $this->get('home_show_categories', 'all'),
            'show_flash_sale' => $this->get('home_show_flash_sale', 'all'),
            'show_promo_banner' => $this->get('home_show_promo_banner', 'all'),
            'show_featured' => $this->get('home_show_featured', 'all'),
            'show_new_arrivals' => $this->get('home_show_new_arrivals', 'all'),
            'show_best_sellers' => $this->get('home_show_best_sellers', 'all'),
            'show_trust_badges' => $this->get('home_show_trust_badges', 'all'),
            'show_blog_highlight' => $this->get('home_show_blog_highlight', 'all'),
            'show_newsletter' => $this->get('home_show_newsletter', 'all'),
            'category_ids' => $this->json('home_category_ids'),
            'featured_ids' => $this->json('home_featured_ids'),
            'trust_badges' => $this->json('home_trust_badges'),
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember($this->cacheKey($key), 3600, function () use ($key, $default) {
            return Setting::query()->where('key', $key)->value('value') ?? $default;
        });
    }

    public function set(string $key, mixed $value, string $group = 'general', string $type = 'text', ?string $label = null): Setting
    {
        $setting = Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group_name' => $group, 'type' => $type, 'label' => $label]
        );

        Cache::forget($this->cacheKey($key));

        return $setting;
    }

    public function updateMany(array $settings, string $group = 'general'): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value, $group);
        }
    }

    private function cacheKey(string $key): string
    {
        return config('website-v2.cache_prefix', 'website_v2') . '.settings.' . $key;
    }

    private function json(string $key, array $default = []): array
    {
        $value = $this->get($key);

        if (! is_string($value) || $value === '') {
            return $default;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : $default;
    }
}
