<?php

namespace Modules\WebsiteV2\Livewire\Products;

use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Category\Models\Category;
use Modules\WebsiteV2\Services\CartService;
use Modules\WebsiteV2\Services\ProductService;

class ProductList extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public ?string $categorySlug = null;

    #[Url(history: true)]
    public string $sort = 'latest';

    #[Url(history: true)]
    public string $price_range = '';

    public array $selected_categories = [];

    public string $view_mode = 'grid';

    #[On('search-updated')]
    public function updateSearch(string $search): void
    {
        $this->search = $search;
        $this->resetPage();
    }

    #[On('sort-updated')]
    public function updateSort(string $sort): void
    {
        $this->sort = $sort;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['selected_categories', 'price_range', 'search', 'categorySlug']);
        $this->resetPage();
    }

    public function addToCart(int $productId): void
    {
        $product = \Modules\Product\Models\Product::query()->findOrFail($productId);
        $price = (float) ($product->sale_price ?: $product->regular_price ?: 0);

        app(CartService::class)->addItem($productId, $price);
        $this->dispatch('cart-updated');
    }

    public function render(ProductService $products)
    {
        return view('website-v2::livewire.products.product-list', [
            'products' => $products->listing([
                'search' => $this->search,
                'categorySlug' => $this->categorySlug,
                'sort' => $this->sort,
                'price_range' => $this->price_range,
                'selected_categories' => $this->selected_categories,
            ]),
            'categories' => Category::query()
                ->withCount('products')
                ->roots()
                ->ofType('product')
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
