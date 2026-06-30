@props([
    'categories' => [],
])

@if (! empty($categories))
    <section class="mx-auto w-full max-w-[1440px] px-3 py-8 sm:px-4 lg:px-6">
        <h2 class="text-[22px] font-semibold leading-7 text-[#222222] md:text-[26px] md:leading-[26px]">Danh muc noi bat</h2>
        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
            @foreach ($categories as $category)
                <a href="{{ $category['url'] }}" class="rounded-2xl bg-white p-5 text-[14px] font-bold leading-[21px] text-[#222222] transition hover:text-[#00533F] hover:shadow-[rgba(34,34,34,0.1)_0px_2px_10px_-3px]">
                    {{ $category['name'] }}
                </a>
            @endforeach
        </div>
    </section>
@endif
