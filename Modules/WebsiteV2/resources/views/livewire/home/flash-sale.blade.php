<section class="rounded-2xl bg-red-50 border border-red-100 p-6">
    <div class="flex items-end justify-between gap-4 mb-6">
        <div>
            <p class="text-sm font-bold text-red-600 uppercase tracking-wider">Limited time</p>
            <h2 class="text-2xl md:text-3xl font-black text-gray-900">Flash Sale</h2>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-4">
        @forelse ($flashSales as $sale)
            <article class="rounded-xl bg-white p-5 shadow-sm">
                <h3 class="font-bold text-gray-900">{{ $sale->title }}</h3>
                <p class="text-sm text-gray-500 mt-2">{{ optional($sale->start_time)->format('d/m/Y H:i') }} - {{ optional($sale->end_time)->format('d/m/Y H:i') }}</p>
            </article>
        @empty
            <p class="text-gray-500">No active flash sale.</p>
        @endforelse
    </div>
</section>
