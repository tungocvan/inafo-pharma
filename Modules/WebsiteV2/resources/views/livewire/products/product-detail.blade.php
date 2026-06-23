<div class="bg-white" x-data="{
    activeImg: '{{ $product->image_url }}',
    activeTab: 'description',
    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => alert('Link copied.'));
    }
}">
    <nav class="container mx-auto px-4 py-4 text-sm text-gray-500 flex items-center gap-2">
        <a href="{{ route('website-v2.home') }}" class="hover:text-blue-600">Home</a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('website-v2.product.list') }}" class="hover:text-blue-600">Products</a>
        <span class="text-gray-300">/</span>
        @if ($product->categories->isNotEmpty())
            <span class="hover:text-blue-600">{{ $product->categories->first()->name }}</span>
            <span class="text-gray-300">/</span>
        @endif
        <span class="text-gray-900 font-medium truncate max-w-[200px]">{{ $product->title }}</span>
    </nav>

    <div class="container mx-auto px-4 pb-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
            <div class="space-y-4">
                <div class="aspect-square bg-gray-50 rounded-2xl overflow-hidden border border-gray-100 relative group">
                    <img :src="activeImg" class="w-full h-full object-contain transition-transform duration-500 group-hover:scale-105" alt="{{ $product->title }}">

                    @if ($product->sale_price && $product->regular_price && $product->sale_price < $product->regular_price)
                        <span class="absolute top-4 left-4 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                            -{{ round((($product->regular_price - $product->sale_price) / $product->regular_price) * 100) }}%
                        </span>
                    @endif
                </div>

                @if (count($product->gallery_urls ?? []) > 0)
                    <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
                        <button @click="activeImg = '{{ $product->image_url }}'"
                            :class="activeImg === '{{ $product->image_url }}' ? 'ring-2 ring-blue-600 border-transparent' : 'border-gray-200 hover:border-gray-300'"
                            class="flex-shrink-0 w-20 h-20 rounded-xl border bg-gray-50 overflow-hidden transition-all" type="button">
                            <img src="{{ $product->image_url }}" class="w-full h-full object-cover" alt="{{ $product->title }}">
                        </button>
                        @foreach ($product->gallery_urls as $url)
                            <button @click="activeImg = '{{ $url }}'"
                                :class="activeImg === '{{ $url }}' ? 'ring-2 ring-blue-600 border-transparent' : 'border-gray-200 hover:border-gray-300'"
                                class="flex-shrink-0 w-20 h-20 rounded-xl border bg-gray-50 overflow-hidden transition-all" type="button">
                                <img src="{{ $url }}" class="w-full h-full object-cover" alt="{{ $product->title }}">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-blue-600 font-bold text-sm tracking-wider uppercase">
                        {{ $product->categories->first()->name ?? 'Shop' }}
                    </span>
                    <div class="flex items-center gap-1 text-yellow-400 text-sm">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        @endfor
                        <span class="text-gray-400 ml-1">({{ $reviews->count() }} reviews)</span>
                    </div>
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-gray-900 mb-4 leading-tight">
                    {{ $product->title }}
                </h1>

                <div class="flex items-end gap-4 mb-6 pb-6 border-b border-gray-100">
                    @if ($product->sale_price)
                        <span class="text-4xl font-bold text-gray-900">{{ number_format((float) $product->sale_price) }}<span class="text-xl align-top">d</span></span>
                        <span class="text-xl text-gray-400 line-through mb-1">{{ number_format((float) $product->regular_price) }}d</span>
                    @else
                        <span class="text-4xl font-bold text-gray-900">{{ number_format((float) $product->regular_price) }}<span class="text-xl align-top">d</span></span>
                    @endif
                </div>

                <div class="prose prose-sm text-gray-600 mb-8">
                    {!! $product->short_description !!}
                </div>

                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-100 mb-8">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex items-center bg-white border border-gray-200 rounded-xl">
                            <button wire:click="decrement" class="w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-100 rounded-l-xl transition" type="button">-</button>
                            <input type="text" value="{{ $quantity }}" readonly class="w-12 text-center border-none focus:ring-0 font-bold text-gray-900 bg-transparent p-0">
                            <button wire:click="increment" class="w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-100 rounded-r-xl transition" type="button">+</button>
                        </div>
                        <span class="text-sm text-gray-500">{{ $product->quantity > 0 ? 'In stock' : 'Out of stock' }}</span>
                    </div>

                    <div class="flex gap-3">
                        <button wire:click="addToCart" wire:loading.attr="disabled" @disabled($product->quantity <= 0)
                            class="flex-1 bg-gray-900 text-white py-4 rounded-xl font-bold hover:bg-blue-600 transition-all shadow-lg hover:shadow-blue-500/30 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed" type="button">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            Add to cart
                        </button>
                        <button class="w-14 bg-white border border-gray-200 rounded-xl flex items-center justify-center text-gray-400 hover:text-red-500 hover:border-red-200 transition" type="button">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-3">
                    <p class="text-sm font-bold text-gray-900 uppercase tracking-wider">Share & affiliate</p>

                    <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg p-2">
                        <input type="text" readonly value="{{ $affiliateLink }}" class="flex-1 text-xs text-gray-500 bg-transparent border-none focus:ring-0 truncate">
                        <button @click="copyToClipboard('{{ $affiliateLink }}')" class="text-xs font-bold text-blue-600 hover:text-blue-700 whitespace-nowrap px-2" type="button">
                            Copy
                        </button>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($affiliateLink) }}" target="_blank" class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-600 hover:text-white transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="https://zalo.me/share/?url={{ urlencode($affiliateLink) }}" target="_blank" class="w-10 h-10 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center hover:bg-blue-500 hover:text-white transition font-bold text-xs">
                            Zalo
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode($affiliateLink) }}&text={{ urlencode($product->title) }}" target="_blank" class="w-10 h-10 rounded-full bg-sky-100 text-sky-500 flex items-center justify-center hover:bg-sky-500 hover:text-white transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                    </div>
                    @guest
                        <p class="text-xs text-gray-500 mt-2">
                            <a href="{{ route('website-v2.login') }}" class="text-blue-600 hover:underline">Login</a> to earn affiliate commission when sharing this link.
                        </p>
                    @endguest
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-50 py-16 border-t border-gray-100">
        <div class="container mx-auto px-4 max-w-4xl">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden min-h-[400px]">
                <div class="flex border-b border-gray-100">
                    <button @click="activeTab = 'description'"
                        :class="activeTab === 'description' ? 'text-blue-600 border-blue-600 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700 bg-gray-50'"
                        class="flex-1 py-4 font-bold text-sm uppercase tracking-wider border-b-2 transition-all" type="button">
                        Product details
                    </button>
                    <button @click="activeTab = 'reviews'"
                        :class="activeTab === 'reviews' ? 'text-blue-600 border-blue-600 bg-white' : 'text-gray-500 border-transparent hover:text-gray-700 bg-gray-50'"
                        class="flex-1 py-4 font-bold text-sm uppercase tracking-wider border-b-2 transition-all" type="button">
                        Reviews ({{ $reviews->count() }})
                    </button>
                </div>

                <div class="p-8 md:p-12">
                    <div x-show="activeTab === 'description'" x-transition.opacity class="prose prose-blue max-w-none text-gray-600">
                        {!! $product->description !!}
                    </div>
                    <div x-show="activeTab === 'reviews'" x-transition.opacity class="space-y-4">
                        @forelse ($reviews as $review)
                            <div class="border border-gray-100 rounded-2xl p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="text-yellow-400 text-sm">
                                        @for ($i = 1; $i <= (int) $review->rating; $i++)
                                            <span>*</span>
                                        @endfor
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $review->created_at?->format('d/m/Y') }}</span>
                                </div>
                                <p class="text-sm text-gray-600">{{ $review->comment }}</p>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <div class="inline-block p-4 rounded-full bg-gray-50 text-gray-400 mb-4">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500">No reviews yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($this->relatedProducts->isNotEmpty())
        <div class="container mx-auto px-4 py-16">
            <h3 class="text-2xl font-bold text-gray-900 mb-8">You may also like</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach ($this->relatedProducts as $related)
                    <a href="{{ route('website-v2.product.detail', $related->slug) }}" class="group block">
                        <div class="aspect-[3/4] bg-gray-100 rounded-xl overflow-hidden mb-4 relative">
                            <img src="{{ $related->image_url }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" alt="{{ $related->title }}">
                            @if ($related->sale_price && $related->sale_price < $related->regular_price)
                                <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded">Sale</span>
                            @endif
                        </div>
                        <h4 class="font-bold text-gray-900 group-hover:text-blue-600 transition truncate">{{ $related->title }}</h4>
                        <div class="flex gap-2 items-center text-sm mt-1">
                            <span class="font-bold text-gray-900">{{ number_format((float) ($related->sale_price ?: $related->regular_price)) }}d</span>
                            @if ($related->sale_price)
                                <span class="text-gray-400 line-through text-xs">{{ number_format((float) $related->regular_price) }}d</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
