<div class="min-h-screen bg-[#F8F9FA] text-[#222222]">
    <x-inafo::home.header
        :brand="$home['brand']"
        :header="$home['header']"
        :navigation="$home['navigation']"
    />

    <main>
        <x-inafo::home.hero :hero="$home['hero']" />

        <x-inafo::home.benefits
            :brand="$home['brand']"
            :benefits="$home['benefits']"
        />

        <div class="mx-auto w-full max-w-[1440px] space-y-12 px-3 py-8 sm:px-4 lg:px-6">
            @forelse ($home['shelves'] as $shelf)
                <x-inafo::home.product-shelf :shelf="$shelf" />
            @empty
                <x-inafo::home.empty-card
                    title="Chua co ke san pham"
                    message="Hay tao ban ghi trong `inafo_home_shelves` hoac chay seeder demo cua module Inafo."
                    align="center"
                />
            @endforelse
        </div>

        <x-inafo::home.categories :categories="$home['categories']" />

        <x-inafo::home.posts
            :brand="$home['brand']"
            :posts="$home['posts']"
        />

        <x-inafo::home.partners
            :brand="$home['brand']"
            :partners="$home['partners']"
        />
    </main>

    <x-inafo::home.footer
        :brand="$home['brand']"
        :footer="$home['footer']"
    />
</div>
