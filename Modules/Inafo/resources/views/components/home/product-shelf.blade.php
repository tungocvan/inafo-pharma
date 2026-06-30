@props([
    'shelf' => [],
])

<section>
    <div class="mb-4 flex items-center justify-between gap-4">
        <h2 class="text-[22px] font-semibold leading-7 text-[#222222] md:text-[26px] md:leading-[26px]">{{ $shelf['title'] }}</h2>
        <a href="{{ $shelf['view_more_url'] }}" class="inline-flex h-[37px] shrink-0 items-center rounded-[1440px] border-2 border-[#00533F] bg-white px-4 py-2 text-[14px] font-bold leading-[21px] text-[#00533F] transition hover:bg-[#F8F9FA]">Xem them</a>
    </div>

    @if ($shelf['banner'])
        <a href="{{ $shelf['banner']['target_url'] }}" class="mb-4 block overflow-hidden rounded-2xl bg-[#DC3545] px-5 py-4 text-center text-[19px] font-bold leading-[23px] text-white transition hover:shadow-[rgba(34,34,34,0.1)_0px_2px_10px_-3px]">
            {{ $shelf['banner']['title'] }}
        </a>
    @endif

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
        @forelse ($shelf['products'] as $product)
            <x-inafo::home.product-card :product="$product" />
        @empty
            <div class="rounded-2xl border border-dashed border-[#CED4DA] bg-white p-6 text-[14px] leading-[21px] text-[#6C757D] md:col-span-3 lg:col-span-4 xl:col-span-6">
                Ke nay chua co san pham hoac Product module chua co du lieu phu hop.
            </div>
        @endforelse
    </div>
</section>
