<?php

namespace Modules\WebsiteV2\Livewire\Checkout;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\WebsiteV2\Services\CheckoutService;

class CheckoutForm extends Component
{
    public ?string $customer_name = null;

    public ?string $customer_phone = null;

    public ?string $customer_email = null;

    public ?string $customer_address = null;

    public ?string $note = null;

    public string $payment_method = 'cod';

    protected function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'required|string|max:1000',
            'note' => 'nullable|string|max:2000',
            'payment_method' => 'required|in:cod,momo',
        ];
    }

    public function mount(): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            $this->customer_name = $user->name;
            $this->customer_email = $user->email;
            $this->customer_phone = $user->phone ?? '';
            $this->customer_address = $user->address ?? '';
        }
    }

    public function placeOrder(CheckoutService $checkoutService)
    {
        $data = $this->validate();

        try {
            $order = $checkoutService->createOrder($data);
            session()->regenerate();
            session()->flash('success_message', 'Order placed successfully.');
            session()->flash('order_code', $order->order_code);

            return redirect()->route('website-v2.checkout.success', ['code' => $order->order_code]);
        } catch (\Throwable $e) {
            $this->addError('system', $e->getMessage());
        }
    }

    public function render()
    {
        return view('website-v2::livewire.checkout.checkout-form');
    }
}
