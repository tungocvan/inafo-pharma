@props([
    'href' => '#',
    'label' => '',
    'badge' => 0,
])

<a href="{{ $href }}" class="relative inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#FFEED1] text-[14px] font-bold text-[#222222] transition hover:bg-[#FFE5A3]" aria-label="{{ $label }}">
    {{ $slot }}
    <span class="absolute -right-1 -top-1 min-w-5 rounded bg-[#DC3545] px-1 text-center text-[12px] font-semibold leading-5 text-white">{{ $badge }}</span>
</a>
