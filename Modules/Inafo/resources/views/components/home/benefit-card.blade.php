@props([
    'benefit' => [],
])

<div class="rounded-2xl bg-white p-5 text-[#222222] transition hover:shadow-[rgba(34,34,34,0.1)_0px_2px_10px_-3px]">
    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-[#00533F] text-[14px] font-bold text-white">{{ strtoupper(substr($benefit['icon'], 0, 1)) }}</div>
    <h3 class="mt-4 text-[14px] font-bold uppercase leading-[17px] text-[#00533F]">{{ $benefit['title'] }}</h3>
    <p class="mt-2 text-[14px] font-medium leading-[21px] text-[#222222]">{{ $benefit['description'] }}</p>
</div>
