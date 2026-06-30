@props([
    'banner' => null,
])

<div class="overflow-hidden rounded-2xl bg-[#00533F]">
    @if ($banner)
        <a href="{{ $banner['target_url'] }}" class="relative block min-h-[300px] md:min-h-[420px] lg:min-h-[460px]">
            @if ($banner['image_desktop_url'])
                <img src="{{ $banner['image_desktop_url'] }}" alt="{{ $banner['title'] }}" class="absolute inset-0 h-full w-full object-cover">
            @endif
            <div class="relative flex min-h-[300px] flex-col justify-center bg-[#00533F]/80 p-6 text-white md:min-h-[420px] md:p-12 lg:min-h-[460px] lg:p-[116px] lg:py-16">
                <p class="text-[14px] font-bold uppercase leading-[21px] text-[#FFC107]">San pham noi bat</p>
                <h1 class="mt-3 max-w-3xl text-[28px] font-bold leading-10 tracking-normal md:text-[32px] md:leading-[49px]">{{ $banner['title'] }}</h1>
                @if ($banner['subtitle'])
                    <p class="mt-4 max-w-2xl text-[15px] font-normal leading-[27px] text-white">{{ $banner['subtitle'] }}</p>
                @endif
                @if ($banner['button_label'])
                    <span class="mt-6 inline-flex h-[37px] w-fit items-center rounded-[1440px] bg-white px-4 py-2 text-[14px] font-bold leading-[21px] text-[#00533F]">{{ $banner['button_label'] }}</span>
                @endif
            </div>
        </a>
    @else
        <div class="flex min-h-[300px] flex-col justify-center p-6 text-white md:min-h-[420px] md:p-12 lg:min-h-[460px] lg:p-[116px] lg:py-16">
            <p class="text-[14px] font-bold uppercase leading-[21px] text-[#FFC107]">INAFO Pharma</p>
            <h1 class="mt-3 max-w-3xl text-[28px] font-bold leading-10 tracking-normal md:text-[32px] md:leading-[49px]">Kenh dat hang duoc pham B2B cho nha thuoc</h1>
            <p class="mt-4 max-w-2xl text-[15px] font-normal leading-[27px] text-white">Quan ly san pham, khuyen mai va dat hang nhanh trong mot storefront rieng cho Inafo.</p>
        </div>
    @endif
</div>
