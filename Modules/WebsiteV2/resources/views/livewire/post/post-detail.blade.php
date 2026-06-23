<div class="bg-white min-h-screen" x-data="{ width: '0%' }" x-init="window.addEventListener('scroll', () => { width = (window.scrollY / (document.body.scrollHeight - window.innerHeight) * 100) + '%' })">
    <div class="fixed top-0 left-0 w-full h-1.5 bg-gray-100 z-50">
        <div class="h-full bg-blue-600 transition-all duration-150 ease-out" :style="'width: ' + width"></div>
    </div>

    <div class="relative w-full h-[60vh] flex items-end justify-center pb-16">
        <div class="absolute inset-0">
            <img src="{{ $post->thumbnail ?: 'https://placehold.co/1600x900?text=INAFO+Pharma' }}" class="w-full h-full object-cover filter brightness-[0.4]" alt="{{ $post->name }}">
        </div>

        <div class="relative z-10 container mx-auto px-4 max-w-4xl text-center text-white">
            @if ($post->categories->isNotEmpty())
                <a href="{{ route('website-v2.blog', ['category' => $post->categories->first()->slug]) }}" class="inline-block px-4 py-1 mb-6 text-sm font-bold tracking-widest uppercase border border-white/30 rounded-full hover:bg-white hover:text-black transition-colors">
                    {{ $post->categories->first()->name }}
                </a>
            @endif
            <h1 class="text-4xl md:text-6xl font-black leading-tight mb-6">{{ $post->name }}</h1>

            <div class="flex items-center justify-center gap-6 text-sm md:text-base font-medium text-white/80">
                <span class="flex items-center gap-2">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($post->user->name ?? 'A') }}&background=fff&color=000" class="w-8 h-8 rounded-full" alt="Author">
                    {{ $post->user->name ?? 'Admin' }}
                </span>
                <span>&bull;</span>
                <span>{{ $post->published_at ? $post->published_at->format('d M, Y') : '' }}</span>
                <span>&bull;</span>
                <span>{{ $readingTime }} phut doc</span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 -mt-10 relative z-20">
        <div class="bg-white rounded-t-3xl shadow-lg border border-gray-100 max-w-4xl mx-auto p-8 md:p-16">
            @if ($post->summary)
                <div class="text-xl md:text-2xl text-gray-600 italic font-serif leading-relaxed mb-10 pl-6 border-l-4 border-blue-600">
                    {{ $post->summary }}
                </div>
            @endif

            <article class="prose prose-lg prose-blue max-w-none prose-img:rounded-xl prose-headings:font-bold prose-a:text-blue-600">
                {!! $post->content !!}
            </article>

            @if ($post->tags && $post->tags->isNotEmpty())
                <div class="mt-12 pt-8 border-t border-gray-100">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($post->tags as $tag)
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-sm rounded hover:bg-gray-200 transition cursor-pointer">#{{ $tag->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($relatedPosts->isNotEmpty())
        <div class="bg-gray-50 py-20 mt-20 border-t border-gray-200">
            <div class="container mx-auto px-4 max-w-6xl">
                <h3 class="text-2xl font-bold text-gray-900 mb-8">Bai viet lien quan</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach ($relatedPosts as $related)
                        <a href="{{ route('website-v2.blog.detail', $related->slug) }}" class="group block">
                            <div class="aspect-video rounded-xl overflow-hidden mb-4">
                                <img src="{{ $related->thumbnail ?: 'https://placehold.co/800x450?text=INAFO+Pharma' }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" alt="{{ $related->name }}">
                            </div>
                            <h4 class="font-bold text-lg text-gray-900 group-hover:text-blue-600 transition line-clamp-2">
                                {{ $related->name }}
                            </h4>
                            <p class="text-sm text-gray-500 mt-2">{{ $related->published_at ? $related->published_at->format('d M, Y') : '' }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
