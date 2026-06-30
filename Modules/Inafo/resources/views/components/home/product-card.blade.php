@props([
    'product' => [],
])

<article class="group relative rounded-2xl bg-white p-5 text-[#222222] transition hover:-translate-y-0.5 hover:shadow-[rgba(34,34,34,0.15)_0px_4px_16px_-2px]">
    <a href="{{ $product['url'] }}" class="block">
        <div class="aspect-square rounded-md bg-[#F8F9FA] p-3">
            <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}" loading="lazy" class="h-full w-full object-contain">
        </div>
        <h3 class="mt-4 min-h-[46px] text-[14px] font-semibold leading-[21px] text-[#222222] line-clamp-2">{{ $product['name'] }}</h3>
        <p class="mt-3 rounded bg-[#F8D7DA] px-2 py-2 text-center text-[12px] font-semibold leading-[18px] text-[#721C24]">{{ $product['locked_price_label'] }}</p>
    </a>
    <button type="button" class="absolute right-5 top-5 flex h-11 w-11 items-center justify-center rounded-full bg-white text-[14px] font-bold text-[#6C757D] shadow-[rgba(34,34,34,0.1)_0px_2px_10px_-3px] transition hover:text-[#DC3545] focus:outline-none focus:ring-[3px] focus:ring-[#0D6EFD]/25" aria-label="Yeu thich">
        H
    </button>
</article>
