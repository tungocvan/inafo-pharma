@props([
    'brand' => [],
    'header' => [],
    'navigation' => [],
])

<header class="sticky top-0 z-40 bg-white">
    <div class="bg-[#00533F] text-white">
        <div class="mx-auto flex min-h-[70px] w-full max-w-[1440px] flex-col gap-3 px-3 py-3 sm:px-4 lg:flex-row lg:items-center lg:px-6">
            <div class="flex items-center justify-between gap-3">
                <a href="{{ $brand['home_url'] }}" class="flex shrink-0 items-center gap-2" aria-label="{{ $brand['name'] }}">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-white text-[15px] font-bold text-[#00533F]">IN</span>
                    <span class="text-[28px] font-bold leading-[42px] tracking-normal md:text-[32px] md:leading-[49px]">{{ $brand['name'] }}</span>
                </a>

                <details class="group lg:hidden">
                    <summary class="flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-full bg-white/10 text-[20px] font-light text-white hover:bg-white/15" aria-label="Mo menu">
                        =
                    </summary>
                    <div class="absolute left-0 right-0 top-[70px] border-t border-white/10 bg-[#00533F] p-3 shadow-[rgba(34,34,34,0.07)_-1px_0px_10px_0px,rgba(34,34,34,0.04)_5px_20px_40px_0px]">
                        <nav class="mx-auto grid max-w-[1440px] gap-2">
                            @foreach ($navigation as $item)
                                <a href="{{ $item['url'] }}" class="rounded-md px-4 py-3 text-[16px] font-medium leading-6 text-white hover:bg-white/10">
                                    {{ $item['label'] }}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                </details>
            </div>

            <form wire:submit="submitSearch" class="relative flex min-w-0 flex-1 items-center rounded-[1440px] bg-white">
                <input
                    wire:model.live="search"
                    type="search"
                    class="h-10 min-w-0 flex-1 rounded-[1440px] border-0 bg-white py-2 pl-4 pr-[60px] text-[16px] font-normal leading-6 text-[#222222] placeholder:text-[#6C757D] focus:outline-none focus:ring-[3px] focus:ring-[#0D6EFD]/25"
                    placeholder="Tim san pham, hoat chat, thuong hieu"
                >
                <button
                    type="submit"
                    class="absolute right-1 top-1/2 flex h-[35px] w-[38px] -translate-y-1/2 items-center justify-center rounded-full bg-[#FFEED1] text-[17px] font-medium leading-[25px] text-[#222222] transition hover:bg-[#FFE5A3] focus:outline-none focus:ring-[3px] focus:ring-[#0D6EFD]/25"
                    aria-label="Tim kiem"
                >
                    S
                </button>
            </form>

            <div class="flex items-center gap-2">
                <x-inafo::home.header-action href="{{ $brand['home_url'] }}/wishlist" label="Yeu thich" badge="{{ $header['wishlist_count'] }}">H</x-inafo::home.header-action>
                <x-inafo::home.header-action href="{{ $brand['home_url'] }}/notifications" label="Thong bao" badge="{{ $header['notification_count'] }}">N</x-inafo::home.header-action>
                <x-inafo::home.header-action href="{{ $brand['home_url'] }}/cart" label="Gio hang" badge="{{ $header['cart_count'] }}">C</x-inafo::home.header-action>

                @if ($header['is_authenticated'])
                    <a href="{{ $brand['home_url'] }}/account" class="hidden h-[37px] items-center gap-2 rounded-[1440px] bg-white px-4 py-2 text-[14px] font-bold leading-[21px] text-[#00533F] transition hover:bg-[#F8F9FA] sm:inline-flex">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#FFC107] text-[12px] font-bold text-black">U</span>
                        <span class="max-w-36 truncate">{{ $header['display_name'] }}</span>
                    </a>
                @else
                    <a href="{{ $brand['home_url'] }}/login" class="hidden h-[37px] items-center rounded-[1440px] bg-white px-4 py-2 text-[14px] font-bold leading-[21px] text-[#00533F] transition hover:bg-[#F8F9FA] sm:inline-flex">Dang nhap</a>
                    <a href="{{ $brand['home_url'] }}/register" class="hidden h-[37px] items-center rounded-[1440px] px-4 py-2 text-[14px] font-bold leading-[21px] text-white transition hover:bg-white/10 sm:inline-flex">Dang ky</a>
                @endif
            </div>
        </div>
    </div>

    <nav class="hidden border-b border-[#E9ECEF] bg-white lg:block">
        <div class="mx-auto flex h-[46px] w-full max-w-[1440px] items-center gap-2 px-6">
            @foreach ($navigation as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="inline-flex h-[37px] shrink-0 items-center rounded-[1440px] px-4 py-2 text-[14px] font-bold leading-[21px] text-[#00533F] transition hover:bg-[#F8F9FA] {{ $item['active'] ? 'border-b-[3px] border-[#00533F]' : '' }}"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </nav>
</header>
