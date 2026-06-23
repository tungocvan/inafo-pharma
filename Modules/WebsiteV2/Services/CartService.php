<?php

namespace Modules\WebsiteV2\Services;

use Illuminate\Support\Facades\DB;
use Modules\WebsiteV2\Models\Cart;
use Modules\WebsiteV2\Models\CartItem;
use Modules\WebsiteV2\Models\Coupon;

class CartService
{
    public function getCart(): Cart
    {
        return Cart::query()->with(['items.product', 'coupon'])->firstOrCreate([
            'session_id' => session()->getId(),
            'user_id' => auth()->id(),
        ]);
    }

    public function addItem(int $productId, float $price, int $quantity = 1): CartItem
    {
        return DB::transaction(function () use ($productId, $price, $quantity) {
            $cart = $this->getCart();
            $item = $cart->items()->firstOrNew(['product_id' => $productId]);
            $item->quantity = (int) $item->quantity + $quantity;
            $item->price = $price;
            $item->total = $item->price * $item->quantity;
            $item->save();

            return $item;
        });
    }

    public function updateQuantity(int $itemId, int $quantity): CartItem
    {
        return DB::transaction(function () use ($itemId, $quantity) {
            $item = $this->ownedItem($itemId);
            $item->quantity = max(1, $quantity);
            $item->total = $item->price * $item->quantity;
            $item->save();

            return $item;
        });
    }

    public function incrementItem(int $itemId): CartItem
    {
        $item = $this->ownedItem($itemId);

        return $this->updateQuantity($itemId, $item->quantity + 1);
    }

    public function decrementItem(int $itemId): CartItem
    {
        $item = $this->ownedItem($itemId);

        return $this->updateQuantity($itemId, max(1, $item->quantity - 1));
    }

    public function removeItem(int $itemId): void
    {
        $this->ownedItem($itemId)->delete();
    }

    public function applyCoupon(string $code): Cart
    {
        $coupon = Coupon::query()->active()->where('code', $code)->firstOrFail();
        $cart = $this->getCart();
        $cart->update(['coupon_id' => $coupon->id]);

        return $cart->refresh();
    }

    public function removeCoupon(): Cart
    {
        $cart = $this->getCart();
        $cart->update(['coupon_id' => null]);

        return $cart->refresh();
    }

    public function getCartSummary(): array
    {
        $cart = $this->getCart()->load('items.product', 'coupon');
        $subtotal = (float) $cart->items->sum('total');
        $discount = $this->discountFor($cart, $subtotal);

        return [
            'cart' => $cart,
            'items' => $cart->items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => max(0, $subtotal - $discount),
            'coupon_code' => $cart->coupon?->code,
        ];
    }

    private function ownedItem(int $itemId): CartItem
    {
        return $this->getCart()->items()->whereKey($itemId)->firstOrFail();
    }

    private function discountFor(Cart $cart, float $subtotal): float
    {
        if (! $cart->coupon || $subtotal < (float) $cart->coupon->min_order_value) {
            return 0;
        }

        return $cart->coupon->type === 'percent'
            ? $subtotal * ((float) $cart->coupon->value / 100)
            : (float) $cart->coupon->value;
    }
}
