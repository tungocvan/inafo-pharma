<?php

namespace Modules\WebsiteV2\Livewire\Checkout;

use Illuminate\Support\Facades\App;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Modules\WebsiteV2\Services\CartService;

class OrderSummary extends Component
{
    public string $couponCode = '';

    protected function getCartService(): CartService
    {
        return App::make(CartService::class);
    }

    #[Computed]
    public function summary(): array
    {
        return $this->getCartService()->getCartSummary();
    }

    public function applyCoupon(): void
    {
        if ($this->couponCode === '') {
            return;
        }

        try {
            $this->getCartService()->applyCoupon($this->couponCode);
            unset($this->summary);
            $this->couponCode = '';
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Coupon applied.']);
            $this->dispatch('cart-updated');
        } catch (\Throwable $e) {
            $this->addError('coupon', $e->getMessage());
        }
    }

    public function removeCoupon(): void
    {
        try {
            $this->getCartService()->removeCoupon();
            unset($this->summary);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Coupon removed.']);
            $this->dispatch('cart-updated');
        } catch (\Throwable $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('website-v2::livewire.checkout.order-summary');
    }
}
