<div class="bg-gray-50 min-h-screen font-sans pb-20">
    <div class="bg-white border-b border-gray-100 pt-12 pb-16">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-black text-gray-900 tracking-tight mb-4">
                {{ $currentCategory ? $currentCategory->name : 'Tap Chi & Tin Tuc' }}
            </h1>
            <p class="text-gray-500 text-lg max-w-2xl mx-auto">
                {{ $currentCategory ? 'Cac bai viet thuoc chu de ' . $currentCategory->name : 'Cap nhat nhung xu huong, kien thuc va cau chuyen moi nhat tu INAFO Pharma.' }}
            </p>
        </div>
    </div>

    <div class="container mx-auto px-4 mt-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-12 items-start">
            <aside class="hidden lg:block lg:col-span-1 sticky top-24">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-900 text-lg mb-6 uppercase tracking-wider border-b border-gray-100 pb-2">
                        Chu de
                    </h3>

                    <nav class="space-y-2">
                        <a href="{{ route('website-v2.blog') }}"
                            class="flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 group {{ ! $categorySlug ? 'bg-black text-white shadow-lg' : 'hover:bg-gray-50 text-gray-600' }}">
                            <span class="font-bold">Tat ca bai viet</span>
                            @if (! $categorySlug)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            @endif
                        </a>

                        @foreach ($categories as $cat)
                            <a href="{{ route('website-v2.blog', ['category' => $cat->slug]) }}"
                                class="flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-300 group {{ $categorySlug === $cat->slug ? 'bg-blue-600 text-white shadow-lg' : 'hover:bg-gray-50 text-gray-600 hover:text-blue-600' }}">
                                <span class="font-medium">{{ $cat->name }}</span>
                                <span class="{{ $categorySlug === $cat->slug ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-400 group-hover:bg-white group-hover:text-blue-600' }} text-xs font-bold px-2 py-1 rounded-full transition-colors">
                                    {{ $cat->posts_count }}
                                </span>
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="mt-8 rounded-2xl overflow-hidden relative aspect-[3/4] group cursor-pointer">
                    <img src="https://images.unsplash.com/photo-1587854692152-cbe660dbde88?auto=format&fit=crop&w=400&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" alt="INAFO Pharma">
                    <div class="absolute inset-0 bg-black/40 flex flex-col justify-end p-6">
                        <span class="text-white text-xs font-bold uppercase tracking-widest mb-2">INAFO Pharma</span>
                        <h4 class="text-white font-bold text-xl leading-tight">Cham soc suc khoe moi ngay</h4>
                    </div>
                </div>
            </aside>

            <div class="lg:col-span-3 space-y-12">
                <div class="lg:hidden mb-8" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full bg-white border border-gray-200 px-4 py-3 rounded-xl flex justify-between items-center font-bold text-gray-700">
                        <span>{{ $currentCategory ? $currentCategory->name : 'Chon chu de' }}</span>
                        <svg class="w-5 h-5 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" class="mt-2 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden">
                        <a href="{{ route('website-v2.blog') }}" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-50">Tat ca</a>
                        @foreach ($categories as $cat)
                            <a href="{{ route('website-v2.blog', ['category' => $cat->slug]) }}" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-50 flex justify-between">
                                {{ $cat->name }} <span class="text-gray-400 text-xs">({{ $cat->posts_count }})</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                @if ($heroPost)
                    <div class="group relative rounded-3xl overflow-hidden shadow-xl border border-gray-100">
                        <a href="{{ route('website-v2.blog.detail', $heroPost->slug) }}" class="block relative aspect-video md:aspect-[21/9]">
                            <img src="{{ $heroPost->thumbnail ?: 'https://placehold.co/1200x675?text=INAFO+Pharma' }}" alt="{{ $heroPost->name }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>

                            <div class="absolute bottom-0 left-0 p-6 md:p-10 w-full md:w-3/4">
                                @if ($heroPost->categories->isNotEmpty())
                                    <span class="inline-block px-3 py-1 mb-3 text-xs font-bold tracking-wider text-white uppercase bg-blue-600 rounded-full">
                                        {{ $heroPost->categories->first()->name }}
                                    </span>
                                @endif
                                <h2 class="text-2xl md:text-4xl font-bold text-white leading-tight mb-3 group-hover:text-blue-300 transition-colors">
                                    {{ $heroPost->name }}
                                </h2>
                                <p class="text-gray-300 text-sm md:text-base line-clamp-2">{{ $heroPost->summary }}</p>
                            </div>
                        </a>
                    </div>
                @endif

                @if ($posts->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @foreach ($posts as $post)
                            <article class="flex flex-col group bg-white rounded-2xl overflow-hidden border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 h-full">
                                <a href="{{ route('website-v2.blog.detail', $post->slug) }}" class="relative aspect-[16/10] overflow-hidden bg-gray-100">
                                    <img src="{{ $post->thumbnail ?: 'https://placehold.co/800x500?text=INAFO+Pharma' }}" alt="{{ $post->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                </a>

                                <div class="p-6 flex flex-col flex-1">
                                    <div class="flex items-center justify-between mb-3 text-xs">
                                        @if ($post->categories->isNotEmpty())
                                            <span class="font-bold text-blue-600 uppercase tracking-wide">
                                                {{ $post->categories->first()->name }}
                                            </span>
                                        @endif
                                        <span class="text-gray-400 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $post->published_at ? $post->published_at->format('d/m/Y') : '' }}
                                        </span>
                                    </div>

                                    <h3 class="text-lg font-bold text-gray-900 mb-3 leading-snug group-hover:text-blue-600 transition-colors line-clamp-2">
                                        <a href="{{ route('website-v2.blog.detail', $post->slug) }}">{{ $post->name }}</a>
                                    </h3>

                                    <p class="text-gray-500 text-sm line-clamp-3 mb-4 flex-1">{{ $post->summary }}</p>

                                    <div class="pt-4 border-t border-gray-100 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-200 overflow-hidden">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($post->user->name ?? 'Admin') }}" alt="Author">
                                        </div>
                                        <span class="text-xs font-bold text-gray-900">{{ $post->user->name ?? 'Admin' }}</span>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-12">
                        {{ $posts->links('website-v2::livewire.partials.pagination') }}
                    </div>
                @else
                    <div class="text-center py-20 bg-white rounded-2xl border border-gray-100 border-dashed">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                        </svg>
                        <p class="text-gray-500">Chua co bai viet nao trong danh muc nay.</p>
                        <a href="{{ route('website-v2.blog') }}" class="text-blue-600 font-bold hover:underline mt-2 inline-block">Xem tat ca bai viet</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
