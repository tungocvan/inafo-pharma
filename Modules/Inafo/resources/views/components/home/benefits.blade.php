@props([
    'brand' => [],
    'benefits' => [],
])

<section class="mx-auto w-full max-w-[1440px] px-3 py-8 sm:px-4 lg:px-6">
    <h2 class="text-[22px] font-semibold leading-7 text-[#222222] md:text-[26px] md:leading-[26px]">Tai sao chon {{ $brand['name'] }}?</h2>
    <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @forelse ($benefits as $benefit)
            <x-inafo::home.benefit-card :benefit="$benefit" />
        @empty
            <div class="rounded-2xl border border-dashed border-[#CED4DA] bg-white p-6 text-[14px] leading-[21px] text-[#6C757D] sm:col-span-2 lg:col-span-5">
                Chua co benefit. Hay seed hoac tao ban ghi trong `inafo_home_benefits`.
            </div>
        @endforelse
    </div>
</section>
