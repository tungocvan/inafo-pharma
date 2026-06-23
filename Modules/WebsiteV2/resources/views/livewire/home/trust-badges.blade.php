<section class="rounded-2xl bg-white border border-gray-100 p-6 shadow-sm">
    <div class="grid md:grid-cols-3 gap-6">
        @forelse ($badges as $badge)
            <div>
                <h3 class="font-black text-gray-900">{{ $badge['title'] ?? 'Trusted service' }}</h3>
                <p class="text-sm text-gray-500 mt-2">{{ $badge['description'] ?? '' }}</p>
            </div>
        @empty
            <div>
                <h3 class="font-black text-gray-900">Certified products</h3>
                <p class="text-sm text-gray-500 mt-2">Products curated for health and wellness.</p>
            </div>
            <div>
                <h3 class="font-black text-gray-900">Fast support</h3>
                <p class="text-sm text-gray-500 mt-2">Customer care for every order.</p>
            </div>
            <div>
                <h3 class="font-black text-gray-900">Reliable delivery</h3>
                <p class="text-sm text-gray-500 mt-2">Convenient fulfillment for WebsiteV2 customers.</p>
            </div>
        @endforelse
    </div>
</section>
