<?php

namespace Modules\WebsiteV2\Livewire\Products;

use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Modules\Product\Models\Product;
use Modules\WebsiteV2\Models\Review;
use Modules\WebsiteV2\Services\CartService;

class ProductDetail extends Component
{
    public $product;

    public $reviews;

    public int $quantity = 1;

    public string $affiliateLink = '';

    public function mount(string $slug): void
    {
        $this->product = Product::query()
            ->with(['categories', 'user'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        if (request()->has('ref')) {
            Session::put('affiliate_ref', request()->get('ref'));
        }

        $this->affiliateLink = auth()->check()
            ? route('website-v2.product.detail', ['slug' => $slug, 'ref' => auth()->id()])
            : route('website-v2.product.detail', ['slug' => $slug]);

        $this->reviews = Review::query()
            ->where('product_id', $this->product->id)
            ->where('is_approved', true)
            ->latest()
            ->get();
    }

    public function increment(): void
    {
        if ($this->quantity < (int) $this->product->quantity) {
            $this->quantity++;
        }
    }

    public function decrement(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart(): void
    {
        try {
            if ((int) $this->product->quantity <= 0) {
                throw new \RuntimeException('Product is out of stock.');
            }

            if ($this->quantity > (int) $this->product->quantity) {
                throw new \RuntimeException("Only {$this->product->quantity} products left in stock.");
            }

            app(CartService::class)->addItem(
                (int) $this->product->id,
                (float) $this->finalPrice(),
                $this->quantity
            );

            $this->dispatch('cart-updated');
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Product added to cart.',
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function getRelatedProductsProperty()
    {
        return Product::query()
            ->where('id', '!=', $this->product->id)
            ->where('is_active', true)
            ->whereHas('categories', function ($query) {
                $query->whereIn('categories.id', $this->product->categories->pluck('id'));
            })
            ->take(4)
            ->get();
    }

    public function finalPrice(): float
    {
        return (float) ($this->product->sale_price ?: $this->product->regular_price ?: 0);
    }

    public function render()
    {
        return view('website-v2::livewire.products.product-detail');
    }
}
