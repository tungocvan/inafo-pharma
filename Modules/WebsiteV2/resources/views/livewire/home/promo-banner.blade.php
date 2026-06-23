<section>
    <div class="grid md:grid-cols-2 gap-4">
        @forelse ($banners as $banner)
            <a href="{{ $banner->link ?: '#' }}" class="rounded-2xl overflow-hidden bg-gray-900 text-white min-h-[180px] p-8 flex items-end bg-cover bg-center"
               @if ($banner->image_desktop) style="background-image: linear-gradient(90deg, rgba(17,24,39,.78), rgba(17,24,39,.22)), url('{{ asset($banner->image_desktop) }}')" @endif>
                <div>
                    <h2 class="text-2xl font-black">{{ $banner->title }}</h2>
                    @if ($banner->sub_title)
                        <p class="mt-2 text-gray-100">{{ $banner->sub_title }}</p>
                    @endif
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-gray-500">
                No promotions configured.
            </div>
        @endforelse
    </div>
</section>
