@props([
    'name',
    'placeholder' => 'Chon',
    'options' => [],
])

<div>
    <select
        wire:model.live="{{ $name }}"
        class="h-10 w-full rounded-md border border-[#CED4DA] bg-white px-[14px] py-2 text-[16px] font-normal leading-6 text-[#212529] transition hover:border-[#BCC0C4] focus:border-[#0D6EFD] focus:outline-none focus:ring-[3px] focus:ring-[#0D6EFD]/15 @error($name) border-[#DC3545] focus:border-[#DC3545] focus:ring-[#DC3545]/15 @enderror"
    >
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $option)
            <option value="{{ $option }}">{{ $option }}</option>
        @endforeach
    </select>

    @error($name)
        <p class="mt-1 text-[14px] leading-[21px] text-[#DC3545]">{{ $message }}</p>
    @enderror
</div>
