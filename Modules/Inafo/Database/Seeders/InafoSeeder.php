<?php

namespace Modules\Inafo\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Modules\Inafo\Models\FooterColumn;
use Modules\Inafo\Models\HomeBanner;
use Modules\Inafo\Models\HomeBenefit;
use Modules\Inafo\Models\HomeShelf;
use Modules\Inafo\Models\HomeShelfProduct;
use Modules\Inafo\Models\PartnerLogo;
use Modules\Product\Models\Product;

class InafoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBanners();
        $this->seedBenefits();
        $this->seedShelves();
        $this->seedPartners();
        $this->seedFooter();
    }

    private function seedBanners(): void
    {
        HomeBanner::query()->updateOrCreate(
            ['placement' => 'hero_main', 'position' => 1],
            [
                'title' => 'Top cac san pham hot cho nha thuoc',
                'subtitle' => 'Dat hang nhanh, gia khoa theo trang thai xac minh ho so kinh doanh.',
                'button_label' => 'Kham pha ngay',
                'target_url' => '/inafo/products',
                'is_active' => true,
            ]
        );

        HomeBanner::query()->updateOrCreate(
            ['placement' => 'hero_side', 'position' => 1],
            [
                'title' => 'San ngay qua hot',
                'subtitle' => 'Chuong trinh uu dai theo chien dich.',
                'target_url' => '/inafo/promotions',
                'is_active' => true,
            ]
        );

        HomeBanner::query()->updateOrCreate(
            ['placement' => 'hero_side', 'position' => 2],
            [
                'title' => 'Mua san pham tang ngay',
                'subtitle' => 'Cap nhat cac combo noi bat cho nha thuoc.',
                'target_url' => '/inafo/promotions',
                'is_active' => true,
            ]
        );

        HomeBanner::query()->updateOrCreate(
            ['placement' => 'shelf_strip', 'position' => 1],
            [
                'title' => 'San pham noi bat - mua 500K, tang 50K',
                'target_url' => '/inafo/promotions',
                'is_active' => true,
            ]
        );
    }

    private function seedBenefits(): void
    {
        $benefits = [
            ['title' => 'Hang chinh hang', 'description' => '100% hoa don VAT', 'icon' => 'check'],
            ['title' => 'Gia tot nhat thi truong', 'description' => 'Tot hon moi gia tot', 'icon' => 'award'],
            ['title' => 'Bao hanh bat chap', 'description' => 'Doi tra khong can ly do', 'icon' => 'shield'],
            ['title' => 'Chiet khau vo dich', 'description' => 'Cao hon moi chiet khau', 'icon' => 'percent'],
            ['title' => 'Dich vu vuot troi', 'description' => 'Nhanh chong - An toan - Hieu qua', 'icon' => 'star'],
        ];

        foreach ($benefits as $index => $benefit) {
            HomeBenefit::query()->updateOrCreate(
                ['title' => $benefit['title']],
                $benefit + [
                    'position' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedShelves(): void
    {
        $stripBanner = HomeBanner::query()
            ->where('placement', 'shelf_strip')
            ->orderBy('position')
            ->first();

        $shelves = [
            ['title' => 'San pham moi', 'slug' => 'san-pham-moi', 'type' => 'newest', 'banner_id' => null],
            ['title' => 'San pham noi bat', 'slug' => 'san-pham-noi-bat', 'type' => 'featured', 'banner_id' => $stripBanner?->id],
            ['title' => 'San pham ban chay', 'slug' => 'san-pham-ban-chay', 'type' => 'best_seller', 'banner_id' => null],
        ];

        foreach ($shelves as $index => $shelf) {
            $model = HomeShelf::query()->updateOrCreate(
                ['slug' => $shelf['slug']],
                $shelf + [
                    'view_more_url' => '/inafo/products?shelf=' . $shelf['slug'],
                    'product_limit' => 12,
                    'position' => $index + 1,
                    'is_active' => true,
                ]
            );

            if ($model->type === 'manual') {
                $this->syncShelfProducts($model);
            }
        }
    }

    private function syncShelfProducts(HomeShelf $shelf): void
    {
        if (! Schema::hasTable('wp_products')) {
            return;
        }

        $ids = Product::query()
            ->where('is_active', true)
            ->latest()
            ->limit(12)
            ->pluck('id')
            ->values();

        foreach ($ids as $index => $id) {
            HomeShelfProduct::query()->updateOrCreate(
                ['shelf_id' => $shelf->id, 'product_id' => $id],
                ['position' => $index + 1]
            );
        }
    }

    private function seedPartners(): void
    {
        foreach (['A Dong', 'AZ Pharmacy', 'Tam Phuc', 'Nha Khong Pharmacy', 'Minh Tam', 'Thu Phuong'] as $index => $name) {
            PartnerLogo::query()->updateOrCreate(
                ['name' => $name],
                [
                    'position' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedFooter(): void
    {
        $columns = [
            'Ve Inafo' => [
                ['Gioi thieu', '/inafo/about'],
                ['Tin tuc', '/inafo/blog'],
                ['Ket noi voi chung toi', '/inafo/contact'],
            ],
            'Dieu khoan va Chinh sach' => [
                ['Dieu khoan su dung', '/inafo/policy/terms'],
                ['Chinh sach bao mat', '/inafo/policy/privacy'],
                ['Chinh sach van chuyen', '/inafo/policy/shipping'],
            ],
            'Ho tro khach hang' => [
                ['Huong dan dat hang', '/inafo/help/order'],
                ['Cau hoi thuong gap', '/inafo/help/faq'],
                ['Lien he', '/inafo/contact'],
            ],
        ];

        $columnPosition = 1;

        foreach ($columns as $title => $links) {
            $column = FooterColumn::query()->updateOrCreate(
                ['title' => $title],
                [
                    'position' => $columnPosition,
                    'is_active' => true,
                ]
            );

            foreach ($links as $index => [$label, $url]) {
                $column->links()->updateOrCreate(
                    ['label' => $label],
                    [
                        'url' => $url,
                        'position' => $index + 1,
                        'is_active' => true,
                    ]
                );
            }

            $columnPosition++;
        }
    }
}
