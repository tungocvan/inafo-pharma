<?php

namespace Modules\WebsiteV2\Livewire\Cart;

use Illuminate\Support\Facades\App;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\WebsiteV2\Services\CartService;

class CartList extends Component
{
    public string $couponCodeInput = '';

    protected function getCartService(): CartService
    {
        return App::make(CartService::class);
    }

    #[Computed]
    public function cartData(): array
    {
        return $this->getCartService()->getCartSummary();
    }

    public function increment(int $itemId): void
    {
        try {
            $this->getCartService()->incrementItem($itemId);
            unset($this->cartData);
            $this->dispatch('cart-updated');
        } catch (\Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function decrement(int $itemId): void
    {
        try {
            $this->getCartService()->decrementItem($itemId);
            unset($this->cartData);
            $this->dispatch('cart-updated');
        } catch (\Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function remove(int $itemId): void
    {
        try {
            $this->getCartService()->removeItem($itemId);
            unset($this->cartData);
            $this->dispatch('cart-updated');
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Product removed.']);
        } catch (\Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function applyCoupon(): void
    {
        try {
            $this->validate(['couponCodeInput' => 'required|string']);
            $this->getCartService()->applyCoupon($this->couponCodeInput);
            unset($this->cartData);
            $this->couponCodeInput = '';
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Coupon applied.']);
        } catch (\Throwable $e) {
            $this->addError('couponCodeInput', $e->getMessage());
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function removeCoupon(): void
    {
        $this->getCartService()->removeCoupon();
        unset($this->cartData);
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Coupon removed.']);
    }

    public function render()
    {
        return view('website-v2::livewire.cart.cart-list');
    }
}
