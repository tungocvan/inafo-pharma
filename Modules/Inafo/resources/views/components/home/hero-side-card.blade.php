@props([
    'banner' => [],
    'eyebrow' => 'Khuyen mai',
])

<a href="{{ $banner['target_url'] }}" class="relative min-h-[196px] overflow-hidden rounded-2xl bg-white">
    @if ($banner['image_desktop_url'])
        <img src="{{ $banner['image_desktop_url'] }}" alt="{{ $banner['title'] }}" class="absolute inset-0 h-full w-full object-cover">
    @endif
    <div class="relative flex h-full min-h-[196px] flex-col justify-center bg-white/90 p-5">
        <p class="text-[12px] font-bold uppercase leading-[18px] text-[#0E947A]">{{ $eyebrow }}</p>
        <h2 class="mt-2 text-[26px] font-semibold leading-[30px] text-[#00533F]">{{ $banner['title'] }}</h2>
        @if ($banner['subtitle'])
            <p class="mt-2 text-[14px] font-normal leading-[21px] text-[#4A5568]">{{ $banner['subtitle'] }}</p>
        @endif
    </div>
</a>
