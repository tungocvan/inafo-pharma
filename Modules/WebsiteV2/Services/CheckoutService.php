<?php

namespace Modules\WebsiteV2\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\WebsiteV2\Models\Cart;
use Modules\WebsiteV2\Models\Order;
use Modules\WebsiteV2\Models\OrderHistory;
use Modules\WebsiteV2\Models\OrderItem;

class CheckoutService
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function getCheckoutCart(): Cart
    {
        return $this->cartService->getCart()->load(['items.product', 'coupon']);
    }

    public function createOrder(array $data): Order
    {
        $cartSummary = $this->cartService->getCartSummary();
        $cart = $cartSummary['cart'];
        $items = $cartSummary['items'];

        if ($items->isEmpty()) {
            throw new \RuntimeException('Your cart is empty. Please choose products before checkout.');
        }

        foreach ($items as $item) {
            if (! $item->product || ! $item->product->is_active) {
                throw new \RuntimeException("Product '{$item->product?->title}' is no longer available.");
            }

            if ((int) $item->product->quantity < (int) $item->quantity) {
                throw new \RuntimeException("Product '{$item->product->title}' does not have enough stock.");
            }
        }

        return DB::transaction(function () use ($data, $cartSummary, $cart, $items) {
            $order = Order::query()->create([
                'user_id' => Auth::id(),
                'order_code' => $this->generateOrderCode(),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_address' => $data['customer_address'],
                'note' => $data['note'] ?? null,
                'subtotal' => $cartSummary['subtotal'],
                'shipping_fee' => 0,
                'discount' => $cartSummary['discount'],
                'total' => $cartSummary['total'],
                'coupon_code' => $cartSummary['coupon_code'] ?? null,
                'payment_method' => $data['payment_method'],
                'status' => in_array($data['payment_method'], ['momo', 'vnpay', 'bank_transfer'], true) ? 'pending_payment' : 'pending',
            ]);

            foreach ($items as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->title,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'total' => $item->total,
                    'options' => null,
                ]);

                $item->product->decrement('quantity', $item->quantity);
                $item->product->increment('sold_count', $item->quantity);
            }

            if ($cart->coupon_id) {
                $cart->coupon?->increment('usage_count');
            }

            OrderHistory::query()->create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'action' => 'created',
                'description' => 'Order created through WebsiteV2.',
            ]);

            $cart->items()->delete();
            $cart->delete();

            return $order;
        });
    }

    private function generateOrderCode(): string
    {
        do {
            $code = 'V2-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (Order::query()->where('order_code', $code)->exists());

        return $code;
    }
}
