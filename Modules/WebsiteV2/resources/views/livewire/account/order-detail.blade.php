<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900">Chi tiet don hang #{{ $order->order_code }}</h1>
        <a href="{{ route('website-v2.account.orders') }}" class="text-blue-600 hover:underline flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Quay lai
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Thong tin giao hang</h3>
            <div class="space-y-2 text-sm">
                <p><span class="font-medium text-gray-600">Nguoi nhan:</span> {{ $order->customer_name }}</p>
                <p><span class="font-medium text-gray-600">Dien thoai:</span> {{ $order->customer_phone }}</p>
                <p><span class="font-medium text-gray-600">Email:</span> {{ $order->customer_email ?? 'Khong co' }}</p>
                <p><span class="font-medium text-gray-600">Dia chi:</span> {{ $order->customer_address }}</p>
                <p><span class="font-medium text-gray-600">Ngay dat:</span> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                @if ($order->note)
                    <p class="text-gray-500 italic mt-2">"{{ $order->note }}"</p>
                @endif
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Thanh toan</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Trang thai:</span>
                    <span class="font-bold uppercase">{{ $order->status }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Phuong thuc:</span>
                    <span>{{ strtoupper($order->payment_method) }}</span>
                </div>
                <div class="border-t my-2"></div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Tam tinh:</span>
                    <span>{{ number_format($order->subtotal) }}d</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Giam gia:</span>
                    <span>-{{ number_format($order->discount) }}d</span>
                </div>
                <div class="flex justify-between text-lg font-bold text-blue-600 mt-2">
                    <span>Tong cong:</span>
                    <span>{{ number_format($order->total) }}d</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="font-bold text-gray-900">San pham da mua</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">San pham</th>
                        <th class="px-6 py-3 text-center">Gia</th>
                        <th class="px-6 py-3 text-center">So luong</th>
                        <th class="px-6 py-3 text-right">Thanh tien</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $item->product_name }}</td>
                            <td class="px-6 py-4 text-center">{{ number_format($item->price) }}d</td>
                            <td class="px-6 py-4 text-center">x{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900">{{ number_format($item->total) }}d</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
