<section>
    <div class="flex items-end justify-between gap-4 mb-6">
        <div>
            <p class="text-sm font-bold text-blue-600 uppercase tracking-wider">Customer favorites</p>
            <h2 class="text-2xl md:text-3xl font-black text-gray-900">Best Sellers</h2>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @forelse ($products as $product)
            <article class="rounded-xl bg-white border border-gray-100 p-4 shadow-sm">
                <h3 class="font-bold text-gray-900 line-clamp-2">{{ $product->title ?? $product->name ?? 'Product' }}</h3>
            </article>
        @empty
            <p class="col-span-full text-gray-500">No best sellers available.</p>
        @endforelse
    </div>
</section>
