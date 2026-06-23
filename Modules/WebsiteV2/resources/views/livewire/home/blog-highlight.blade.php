<section>
    <div class="flex items-end justify-between gap-4 mb-6">
        <div>
            <p class="text-sm font-bold text-blue-600 uppercase tracking-wider">Knowledge</p>
            <h2 class="text-2xl md:text-3xl font-black text-gray-900">Latest Articles</h2>
        </div>
        <a href="{{ route('website-v2.blog') }}" class="text-sm font-bold text-blue-600 hover:text-blue-800">View all</a>
    </div>

    <div class="grid md:grid-cols-3 gap-4">
        @forelse ($posts as $post)
            <article class="rounded-xl bg-white border border-gray-100 p-5 shadow-sm">
                <h3 class="font-bold text-gray-900">{{ $post->title ?? 'Article' }}</h3>
                @if (! empty($post->slug))
                    <a href="{{ route('website-v2.blog.detail', $post->slug) }}" class="inline-block mt-3 text-sm font-bold text-blue-600">Read</a>
                @endif
            </article>
        @empty
            <p class="text-gray-500">No articles available.</p>
        @endforelse
    </div>
</section>
