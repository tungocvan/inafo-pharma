<section class="relative overflow-hidden rounded-2xl bg-gray-900 text-white">
    @forelse ($slides as $slide)
        <div class="min-h-[320px] px-6 py-12 md:px-12 flex items-center bg-cover bg-center"
             @if ($slide->image_desktop) style="background-image: linear-gradient(90deg, rgba(17,24,39,.86), rgba(17,24,39,.28)), url('{{ asset($slide->image_desktop) }}')" @endif>
            <div class="max-w-xl">
                <p class="text-sm font-bold uppercase tracking-widest text-blue-200">WebsiteV2</p>
                <h1 class="mt-3 text-3xl md:text-5xl font-black leading-tight">{{ $slide->title ?? 'INAFO Pharma V2' }}</h1>
                @if ($slide->sub_title)
                    <p class="mt-4 text-base md:text-lg text-gray-100">{{ $slide->sub_title }}</p>
                @endif
                @if ($slide->link)
                    <a href="{{ $slide->link }}" class="inline-flex mt-6 px-6 py-3 rounded-full bg-white text-gray-900 font-bold hover:bg-blue-100 transition">
                        {{ $slide->btn_text ?: 'View more' }}
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="min-h-[320px] px-6 py-12 md:px-12 flex items-center">
            <div class="max-w-xl">
                <p class="text-sm font-bold uppercase tracking-widest text-blue-200">WebsiteV2</p>
                <h1 class="mt-3 text-3xl md:text-5xl font-black leading-tight">INAFO Pharma V2</h1>
                <p class="mt-4 text-base md:text-lg text-gray-100">Independent storefront powered by WebsiteV2.</p>
            </div>
        </div>
    @endforelse
</section>
