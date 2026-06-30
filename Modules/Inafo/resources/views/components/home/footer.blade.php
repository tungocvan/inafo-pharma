@props([
    'brand' => [],
    'footer' => ['columns' => []],
])

<footer class="mt-12 bg-white">
    <div class="mx-auto grid w-full max-w-[1440px] gap-8 px-3 py-12 sm:px-4 md:grid-cols-2 lg:grid-cols-4 lg:px-6">
        <div>
            <h2 class="text-[19px] font-bold leading-[23px] text-[#00533F]">{{ $brand['name'] }}</h2>
            <p class="mt-4 text-[15px] font-normal leading-[27px] text-[#4A5568]">Kenh storefront B2B cho nha thuoc, ho tro dat hang nhanh va quan ly gia theo trang thai xac minh ho so kinh doanh.</p>
        </div>

        @foreach ($footer['columns'] as $column)
            <div>
                <h3 class="text-[14px] font-bold uppercase leading-[17px] text-[#222222]">{{ $column['title'] }}</h3>
                <ul class="mt-4 space-y-3">
                    @foreach ($column['links'] as $link)
                        <li>
                            <a href="{{ $link['url'] }}" class="text-[14px] font-normal leading-[21px] text-[#4A5568] transition hover:text-[#00533F]">{{ $link['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
    <div class="border-t border-[#E9ECEF] py-5 text-center text-[12px] font-medium leading-[18px] text-[#6C757D]">Version 1.0.0</div>
</footer>
