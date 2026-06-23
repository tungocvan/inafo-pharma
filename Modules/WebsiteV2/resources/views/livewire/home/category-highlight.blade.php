<section>
    <div class="flex items-end justify-between gap-4 mb-6">
        <div>
            <p class="text-sm font-bold text-blue-600 uppercase tracking-wider">Categories</p>
            <h2 class="text-2xl md:text-3xl font-black text-gray-900">Featured Categories</h2>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @forelse ($categories as $category)
            <a href="#" class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm hover:shadow-md transition">
                <h3 class="font-bold text-gray-900">{{ $category->name ?? $category->title ?? 'Category' }}</h3>
            </a>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-gray-200 bg-white p-6 text-gray-500">
                No featured categories configured.
            </div>
        @endforelse
    </div>
</section>
