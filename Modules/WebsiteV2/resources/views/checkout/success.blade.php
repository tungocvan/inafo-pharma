@extends('website-v2::layouts.frontend')

@section('title', 'Checkout Success')

@section('content')
    <div class="max-w-3xl mx-auto py-16 px-4 sm:px-6 lg:px-8">
        @if (($order?->payment_method ?? null) === 'momo' && ($order?->status ?? null) === 'pending_payment')
            <div class="bg-white rounded-lg shadow-lg border border-pink-200 overflow-hidden text-center p-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-pink-100 mb-6">
                    <img src="https://developers.momo.vn/v3/img/logo.svg" class="w-10 h-10" alt="Momo">
                </div>

                <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Pay Your Order</h2>
                <p class="text-gray-600 mb-6">Scan the QR code below to complete payment.</p>

                <div class="bg-gray-50 p-6 rounded-xl inline-block border border-gray-200 mb-6">
                    <img src="https://img.vietqr.io/image/momo-0903971949-compact.png?amount={{ $order->total }}&addInfo={{ $order->order_code }}"
                        alt="Momo payment QR" class="mx-auto w-64 object-contain">
                    <div class="mt-4 text-sm font-medium text-gray-700">
                        <p>Amount: <span class="text-xl font-bold text-pink-600">{{ number_format($order->total) }}</span></p>
                        <p class="mt-1">Transfer content:</p>
                        <div class="bg-white border border-pink-300 border-dashed py-2 px-4 mt-1 rounded text-pink-700 font-bold text-lg select-all">
                            {{ $order->order_code }}
                        </div>
                    </div>
                </div>

                <div class="flex justify-center gap-4">
                    <a href="{{ route('website-v2.account.orders') }}" class="text-gray-600 hover:underline">View orders</a>
                    <a href="{{ route('website-v2.home') }}" class="bg-pink-600 text-white px-6 py-2 rounded-full font-bold hover:bg-pink-700 transition">
                        I have paid
                    </a>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden text-center p-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-6">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Order placed successfully!</h2>
                <p class="text-gray-500 mb-6">
                    Thank you for your order. Your order code is
                    <span class="font-bold text-gray-900">{{ $order->order_code ?? $code ?? 'pending' }}</span>
                </p>

                <div class="flex justify-center gap-4">
                    <a href="{{ route('website-v2.account.orders') }}" class="text-blue-600 font-medium hover:underline">View order history</a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('website-v2.product.list') }}" class="text-blue-600 font-medium hover:underline">Continue shopping</a>
                </div>
            </div>
        @endif
    </div>
@endsection
