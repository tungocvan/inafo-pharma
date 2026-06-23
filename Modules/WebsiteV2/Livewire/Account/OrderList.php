<?php

namespace Modules\WebsiteV2\Livewire\Account;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\WebsiteV2\Models\Order;

class OrderList extends Component
{
    use WithPagination;

    public function render()
    {
        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('website-v2::livewire.account.order-list', [
            'orders' => $orders,
        ]);
    }
}
