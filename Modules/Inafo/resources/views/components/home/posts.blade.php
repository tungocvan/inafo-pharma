@props([
    'brand' => [],
    'posts' => [],
])

@if (! empty($posts))
    <section class="mx-auto w-full max-w-[1440px] px-3 py-8 sm:px-4 lg:px-6">
        <div class="mb-4 flex items-center justify-between gap-4">
            <h2 class="text-[22px] font-semibold leading-7 text-[#222222] md:text-[26px] md:leading-[26px]">Tin tuc va kien thuc</h2>
            <a href="{{ $brand['home_url'] }}/blog" class="text-[16px] font-medium leading-6 text-[#00533F] hover:underline">Xem them</a>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach ($posts as $post)
                <a href="{{ $post['url'] }}" class="rounded-2xl bg-white p-5 transition hover:shadow-[rgba(34,34,34,0.1)_0px_2px_10px_-3px]">
                    <h3 class="text-[19px] font-bold leading-[23px] text-[#222222]">{{ $post['title'] }}</h3>
                    @if ($post['summary'])
                        <p class="mt-3 text-[14px] font-normal leading-[21px] text-[#4A5568] line-clamp-3">{{ $post['summary'] }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif
