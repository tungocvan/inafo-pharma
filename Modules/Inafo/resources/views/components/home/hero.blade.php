@props([
    'hero' => ['primary' => null, 'side' => []],
])

<section class="mx-auto grid w-full max-w-[1440px] gap-4 px-3 py-6 sm:px-4 md:py-8 lg:grid-cols-[2fr_1fr] lg:px-6">
    <x-inafo::home.hero-primary :banner="$hero['primary'] ?? null" />
    <x-inafo::home.hero-side-list :banners="$hero['side'] ?? []" />
</section>
