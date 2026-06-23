<section>
    <div class="flex items-end justify-between gap-4 mb-6">
        <div>
            <p class="text-sm font-bold text-blue-600 uppercase tracking-wider">Selected for you</p>
            <h2 class="text-2xl md:text-3xl font-black text-gray-900">Featured Products</h2>
        </div>
        <a href="{{ route('website-v2.product.list') }}" class="text-sm font-bold text-blue-600 hover:text-blue-800">View all</a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @forelse ($products as $product)
            <article class="rounded-xl bg-white border border-gray-100 p-4 shadow-sm hover:shadow-md transition">
                <h3 class="font-bold text-gray-900 line-clamp-2">{{ $product->title ?? $product->name ?? 'Product' }}</h3>
                @if (! empty($product->slug))
                    <a href="{{ route('website-v2.product.detail', $product->slug) }}" class="inline-block mt-3 text-sm font-bold text-blue-600">View</a>
                @endif
            </article>
        @empty
            <p class="col-span-full text-gray-500">No featured products configured.</p>
        @endforelse
    </div>
</section>
