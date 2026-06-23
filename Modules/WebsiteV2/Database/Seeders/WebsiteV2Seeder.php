<?php

namespace Modules\WebsiteV2\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Modules\WebsiteV2\Models\AffiliateLevel;
use Modules\WebsiteV2\Models\Banner;
use Modules\WebsiteV2\Models\Coupon;
use Modules\WebsiteV2\Models\FlashSale;
use Modules\WebsiteV2\Models\FooterColumn;
use Modules\WebsiteV2\Models\Newsletter;
use Modules\WebsiteV2\Models\Setting;
use Modules\WebsiteV2\Models\SocialLink;

class WebsiteV2Seeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();
        $this->seedRequiredSettings();
        $this->seedDemoContent();
    }

    private function seedPermissions(): void
    {
        foreach (['website-v2.view', 'website-v2.create', 'website-v2.edit', 'website-v2.delete'] as $name) {
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
        }
    }

    private function seedRequiredSettings(): void
    {
        $settings = [
            ['site_name', 'INAFO Pharma V2', 'general', 'text', 'Site name'],
            ['header.brand_name', 'INAFO Pharma V2', 'header', 'text', 'Brand name'],
            ['header.topbar.hotline', '0903 971 949', 'header', 'text', 'Hotline'],
            ['header.topbar.email', 'contact@inafo.vn', 'header', 'email', 'Email'],
            ['home_show_hero', 'all', 'homepage', 'text', 'Show hero'],
            ['home_show_categories', 'all', 'homepage', 'text', 'Show categories'],
            ['home_show_flash_sale', 'all', 'homepage', 'text', 'Show flash sale'],
            ['home_show_promo_banner', 'all', 'homepage', 'text', 'Show promo banner'],
            ['home_show_featured', 'all', 'homepage', 'text', 'Show featured products'],
            ['home_show_new_arrivals', 'all', 'homepage', 'text', 'Show new arrivals'],
            ['home_show_best_sellers', 'all', 'homepage', 'text', 'Show best sellers'],
            ['home_show_blog_highlight', 'all', 'homepage', 'text', 'Show blog highlight'],
            ['home_show_trust_badges', 'all', 'homepage', 'text', 'Show trust badges'],
            ['home_show_newsletter', 'all', 'homepage', 'text', 'Show newsletter'],
            ['home_category_ids', '[]', 'homepage', 'json', 'Home category IDs'],
            ['home_featured_ids', '[]', 'homepage', 'json', 'Featured product IDs'],
            ['home_trust_badges', '[{"title":"Certified products","description":"Products curated for health and wellness."},{"title":"Fast support","description":"Customer care for every order."}]', 'homepage', 'json', 'Trust badges'],
            ['footer.brand_description', 'Demo storefront for WebsiteV2.', 'footer', 'textarea', 'Footer description'],
            ['footer.email', 'contact@inafo.vn', 'footer', 'email', 'Footer email'],
            ['footer.phone', '0903 971 949', 'footer', 'text', 'Footer phone'],
            ['footer.copyright', 'Copyright INAFO Pharma V2', 'footer', 'text', 'Copyright'],
        ];

        foreach ($settings as [$key, $value, $group, $type, $label]) {
            Setting::query()->updateOrCreate(['key' => $key], compact('value') + [
                'group_name' => $group,
                'type' => $type,
                'label' => $label,
            ]);
        }
    }

    private function seedDemoContent(): void
    {
        Banner::query()->updateOrCreate(
            ['position' => 'hero', 'order' => 1],
            [
                'title' => 'INAFO Pharma V2',
                'sub_title' => 'Health products demo banner',
                'image_desktop' => '/images/demo/website-v2/hero.jpg',
                'image_mobile' => '/images/demo/website-v2/hero-mobile.jpg',
                'link' => '/website-v2/product',
                'btn_text' => 'Shop now',
                'is_active' => true,
            ]
        );

        Coupon::query()->updateOrCreate(
            ['code' => 'WEBSITEV2-DEMO'],
            [
                'description' => 'Safe demo coupon for WebsiteV2 sample data',
                'type' => 'percent',
                'value' => 10,
                'min_order_value' => 100000,
                'usage_limit' => 100,
                'is_active' => true,
            ]
        );

        $company = FooterColumn::query()->updateOrCreate(
            ['slug' => 'company'],
            ['title' => 'Company', 'sort_order' => 1, 'is_active' => true]
        );
        $company->links()->updateOrCreate(
            ['label' => 'Help'],
            ['url' => '/website-v2/help', 'sort_order' => 1, 'is_active' => true]
        );

        SocialLink::query()->updateOrCreate(
            ['platform' => 'facebook'],
            ['name' => 'Facebook', 'url' => 'https://facebook.com', 'sort_order' => 1, 'is_active' => true]
        );

        AffiliateLevel::query()->updateOrCreate(
            ['slug' => 'standard'],
            ['name' => 'Standard', 'min_revenue_required' => 0, 'is_default' => true]
        );

        FlashSale::query()->updateOrCreate(
            ['title' => 'WebsiteV2 Demo Sale'],
            ['start_time' => now()->subDay(), 'end_time' => now()->addWeek(), 'is_active' => true]
        );

        Newsletter::query()->updateOrCreate(
            ['email' => 'demo.website-v2@example.com'],
            ['is_subscribed' => true]
        );
    }
}
