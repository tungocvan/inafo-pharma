@props([
    'name',
    'type' => 'text',
    'placeholder' => '',
    'autocomplete' => null,
    'trailing' => null,
])

<div>
    <div class="relative">
        <input
            wire:model.live="{{ $name }}"
            type="{{ $type }}"
            placeholder="{{ $placeholder }}"
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            class="h-10 w-full rounded-md border border-[#CED4DA] bg-white px-[14px] py-3 {{ $trailing ? 'pr-11' : '' }} text-[16px] font-normal leading-6 text-[#212529] placeholder:text-[#6C757D] transition hover:border-[#BCC0C4] focus:border-[#0D6EFD] focus:outline-none focus:ring-[3px] focus:ring-[#0D6EFD]/15 @error($name) border-[#DC3545] focus:border-[#DC3545] focus:ring-[#DC3545]/15 @enderror"
        >

        @if ($trailing === 'eye')
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[17px] font-bold text-[#222222]">o</span>
        @endif
    </div>

    @error($name)
        <p class="mt-1 text-[14px] leading-[21px] text-[#DC3545]">{{ $message }}</p>
    @enderror
</div>
