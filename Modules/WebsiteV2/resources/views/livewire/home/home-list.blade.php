<div>
    {{-- 1. Hero Banner --}}
    @php $heroClass = $this->getVisibilityClass('show_hero'); @endphp
    @if ($heroClass !== 'hidden')
        <div class="container mx-auto px-4 mt-4 {{ $heroClass }}">
            @livewire('website-v2.home.hero-banner')
        </div>
    @endif

    {{-- Main container --}}
    <div class="container mx-auto px-4 py-8 space-y-12">

        {{-- 2. Featured Categories --}}
        @php $catClass = $this->getVisibilityClass('show_categories'); @endphp
        @if ($catClass !== 'hidden')
            <div class="{{ $catClass }}">
                @livewire('website-v2.home.category-highlight', [
                    'categoryIds' => $settings['category_ids'] ?? []
                ])
            </div>
        @endif

        {{-- 3. Flash Sale --}}
        @php $flashClass = $this->getVisibilityClass('show_flash_sale'); @endphp
        @if ($flashClass !== 'hidden')
            <div class="{{ $flashClass }}">
                @livewire('website-v2.home.flash-sale', ['lazy' => true])
            </div>
        @endif

        {{-- 4. Promo Banner --}}
        @php $promoClass = $this->getVisibilityClass('show_promo_banner'); @endphp
        @if ($promoClass !== 'hidden')
            <div class="{{ $promoClass }}">
                @livewire('website-v2.home.promo-banner', ['lazy' => true])
            </div>
        @endif

        {{-- 5. Featured Products --}}
        @php $featuredClass = $this->getVisibilityClass('show_featured'); @endphp
        @if ($featuredClass !== 'hidden')
            <div class="{{ $featuredClass }}">
                @livewire('website-v2.home.featured-products', [
                    'lazy' => true,
                    'productIds' => $settings['featured_ids'] ?? []
                ])
            </div>
        @endif

        {{-- 6. New Arrivals --}}
        @php $newClass = $this->getVisibilityClass('show_new_arrivals'); @endphp
        @if ($newClass !== 'hidden')
            <div class="{{ $newClass }}">
                @livewire('website-v2.home.new-arrivals', ['lazy' => true])
            </div>
        @endif

        {{-- 7. Best Sellers --}}
        @php $showClass = $this->getVisibilityClass('show_best_sellers'); @endphp
        @if ($showClass !== 'hidden')
            <div class="{{ $showClass }}">
                @livewire('website-v2.home.best-sellers', ['lazy' => true])
            </div>
        @endif

        {{-- 8. Trust Badges --}}
        @php $trustClass = $this->getVisibilityClass('show_trust_badges'); @endphp
        @if ($trustClass !== 'hidden')
            <div class="hidden md:block {{ $trustClass }}">
                @livewire('website-v2.home.trust-badges', [
                    'lazy' => true,
                    'badges' => $settings['trust_badges'] ?? []
                ])
            </div>
        @endif

        {{-- 9. Blog --}}
        @php $blogClass = $this->getVisibilityClass('show_blog_highlight'); @endphp
        @if ($blogClass !== 'hidden')
            <div class="{{ $blogClass }}">
                @livewire('website-v2.home.blog-highlight', ['lazy' => true])
            </div>
        @endif

        {{-- 10. Newsletter --}}
        @php $newsletterClass = $this->getVisibilityClass('show_newsletter'); @endphp
        @if ($newsletterClass !== 'hidden')
            <div class="{{ $newsletterClass }}">
                @livewire('website-v2.home.newsletter-signup', ['lazy' => true])
            </div>
        @endif

    </div>
</div>
