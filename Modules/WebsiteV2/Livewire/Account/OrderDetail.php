<?php

namespace Modules\WebsiteV2\Livewire\Account;

use Livewire\Component;
use Modules\WebsiteV2\Models\Order;

class OrderDetail extends Component
{
    public string $orderCode;

    public function mount(string $code): void
    {
        $this->orderCode = $code;
    }

    public function render()
    {
        $order = Order::query()
            ->with('items')
            ->where('order_code', $this->orderCode)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('website-v2::livewire.account.order-detail', [
            'order' => $order,
        ]);
    }
}
