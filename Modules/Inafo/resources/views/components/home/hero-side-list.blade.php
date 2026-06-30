@props([
    'banners' => [],
])

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-1">
    @forelse ($banners as $sideBanner)
        <x-inafo::home.hero-side-card :banner="$sideBanner" />
    @empty
        <x-inafo::home.hero-side-card
            :banner="[
                'target_url' => '#',
                'image_desktop_url' => null,
                'title' => 'Cau hinh banner trong Inafo',
                'subtitle' => 'Them banner placement hero_side de hien thi tai day.',
            ]"
            eyebrow="Banner phu"
        />
        <x-inafo::home.hero-side-card
            :banner="[
                'target_url' => '#',
                'image_desktop_url' => null,
                'title' => 'San sang cho luong B2B',
                'subtitle' => 'Gia san pham duoc khoa theo trang thai xac minh ho so KD.',
            ]"
            eyebrow="Dat nhanh"
        />
    @endforelse
</div>
