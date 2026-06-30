@props([
    'brand' => [],
    'partners' => [],
])

@if (! empty($partners))
    <section class="mx-auto w-full max-w-[1440px] px-3 py-8 sm:px-4 lg:px-6">
        <h2 class="text-[22px] font-semibold leading-7 text-[#00533F] md:text-[26px] md:leading-[26px]">Doi tac cua {{ $brand['name'] }}</h2>
        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
            @foreach ($partners as $partner)
                <a href="{{ $partner['target_url'] ?: '#' }}" class="flex h-28 items-center justify-center rounded-2xl bg-white p-5 transition hover:shadow-[rgba(34,34,34,0.1)_0px_2px_10px_-3px]">
                    @if ($partner['logo_url'])
                        <img src="{{ $partner['logo_url'] }}" alt="{{ $partner['name'] }}" loading="lazy" class="max-h-16 max-w-full object-contain">
                    @else
                        <span class="text-center text-[14px] font-bold leading-[21px] text-[#00533F]">{{ $partner['name'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
