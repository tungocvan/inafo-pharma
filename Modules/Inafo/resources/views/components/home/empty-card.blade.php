@props([
    'title' => 'Chua co du lieu',
    'message' => '',
    'align' => 'left',
])

<section class="rounded-2xl border border-dashed border-[#CED4DA] bg-white p-8 {{ $align === 'center' ? 'text-center' : '' }}">
    <h2 class="text-[19px] font-bold leading-[23px] text-[#222222]">{{ $title }}</h2>
    @if ($message)
        <p class="mt-2 text-[14px] font-normal leading-[21px] text-[#6C757D]">{{ $message }}</p>
    @endif
</section>
